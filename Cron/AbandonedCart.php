<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Webhook
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Webhook\Cron;

use Mageplaza\Webhook\Helper\Data;
use Mageplaza\Webhook\Model\Config\Source\HookType;
use Mageplaza\Webhook\Model\HookFactory;
use Psr\Log\LoggerInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class AbandonedCart
 * @package Mageplaza\Webhook\Cron
 */
class AbandonedCart
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * AbandonedCart constructor.
     * @param LoggerInterface $logger
     * @param DateTime $date
     * @param QuoteFactory $quoteFactory
     * @param Data $helper
     */
    public function __construct(
        LoggerInterface $logger,
        DateTime $date,
        QuoteFactory $quoteFactory,
        Data $helper
    )
    {
        $this->logger = $logger;
        $this->quoteFactory = $quoteFactory;
        $this->helper = $helper;
        $this->date = $date;
    }

    /**
     * @throws \Exception
     */
    public function execute()
    {

        if (!$this->helper->isEnabled()) {
            return;
        }

        $abandonedTime = (int)$this->helper->getConfigGeneral('abandoned_time');
        $update = (new \DateTime())->sub(new \DateInterval("PT{$abandonedTime}M"));
        $updateFrom = clone $update;
        $updateTo = $update->add(new \DateInterval("PT1H"));

        $quoteCollection = $this->quoteFactory->create()->getCollection()
            ->addFieldToFilter('is_active', 0)
            ->addFieldToFilter('updated_at', ['from' => $updateFrom])
            ->addFieldToFilter('updated_at', ['to' => $updateTo]);
        $noneUpdateQuoteCollection = $this->quoteFactory->create()->getCollection()
            ->addFieldToFilter('is_active', 0)
            ->addFieldToFilter('created_at', ['from' => $updateFrom])
            ->addFieldToFilter('created_at', ['to' => $updateTo])
            ->addFieldToFilter('updated_at', ['eq' => '0000-00-00 00:00:00']);

        try {
            foreach ($quoteCollection as $quote) {
                $this->helper->sendObserver($quote, HookType::ABANDONED_CART);
            }
            foreach ($noneUpdateQuoteCollection as $quote) {
                $this->helper->sendObserver($quote, HookType::ABANDONED_CART);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getLogMessage());
        }
    }
}
