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

use DateInterval;
use DateTime;
use Exception;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Mageplaza\Webhook\Helper\Data;
use Mageplaza\Webhook\Model\Config\Source\HookType;
use Psr\Log\LoggerInterface;

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
     * AbandonedCart constructor.
     *
     * @param LoggerInterface $logger
     * @param QuoteFactory $quoteFactory
     * @param Data $helper
     */
    public function __construct(
        LoggerInterface $logger,
        QuoteFactory $quoteFactory,
        Data $helper
    ) {
        $this->logger = $logger;
        $this->quoteFactory = $quoteFactory;
        $this->helper = $helper;
    }

    /**
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->helper->isEnabled()) {
            return;
        }

        $abandonedTime = (int)$this->helper->getConfigGeneral('abandoned_time');
        $update = (new DateTime())->sub(new DateInterval("PT{$abandonedTime}H"));
        $updateTo = clone $update;
        $updateFrom = $update->sub(new DateInterval("PT1H"));

        /** @var Collection $quoteCollection */
        $quoteCollection = $this->quoteFactory->create()->getCollection()
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('updated_at', ['from' => $updateFrom])
            ->addFieldToFilter('updated_at', ['to' => $updateTo]);

        /** @var Collection $noneUpdateQuoteCollection */
        $noneUpdateQuoteCollection = $this->quoteFactory->create()->getCollection()
            ->addFieldToFilter('is_active', 1)
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
        } catch (Exception $e) {
            $this->logger->critical($e->getLogMessage());
        }
    }
}
