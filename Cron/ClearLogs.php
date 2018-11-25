<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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
 * @copyright   Copyright (c) 2018 Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Webhook\Cron;

use Mageplaza\Webhook\Helper\Data;
use Mageplaza\Webhook\Model\Config\Source\HookType;
use Mageplaza\Webhook\Model\HookFactory;
use Mageplaza\Webhook\Model\HistoryFactory;
use Psr\Log\LoggerInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class AbandonedCart
 * @package Mageplaza\Webhook\Cron
 */
class ClearLogs
{
    protected $logger;
    protected $quoteFactory;
    protected $hookFactory;
    protected $historyFactory;
    protected $helper;
    protected $date;
    protected $timezone;

    public function __construct(
        LoggerInterface $logger,
        DateTime $date,
        TimezoneInterface $timezone,
        QuoteFactory $quoteFactory,
        HookFactory $hookFactory,
        HistoryFactory $historyFactory,
        Data $helper
    )
    {
        $this->logger = $logger;
        $this->quoteFactory = $quoteFactory;
        $this->hookFactory = $hookFactory;
        $this->historyFactory = $historyFactory;
        $this->helper = $helper;
        $this->date = $date;
        $this->timezone = $timezone;
    }

    /**
     * Send Mail
     *
     * @return void
     * @throws \Exception
     */
    public function execute()
    {
        $limit = (int)$this->helper->getConfigGeneral('keep_log');

        if (!$this->helper->isEnabled() || $limit <= 0) {
            return;
        }

        $hookCollection = $this->hookFactory->create()->getCollection();
        foreach ($hookCollection as $hook){
            $historyCollection = $this->historyFactory->create()->getCollection()
                ->addFieldToFilter('hook_id',$hook->getId());
            if($historyCollection->getSize() > $limit){
                $count = $historyCollection->getSize() - $limit;
                $historyCollection->getConnection()->query("DELETE FROM {$historyCollection->getMainTable()} LIMIT {$count}");
            }
        }
    }
}
