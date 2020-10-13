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

use Exception;
use Mageplaza\Webhook\Helper\Data;
use Mageplaza\Webhook\Model\HistoryFactory;
use Mageplaza\Webhook\Model\HookFactory;
use Psr\Log\LoggerInterface;

/**
 * Class ClearLogs
 * @package Mageplaza\Webhook\Cron
 */
class ClearLogs
{
    /**
     * @var HookFactory
     */
    protected $hookFactory;

    /**
     * @var HistoryFactory
     */
    protected $historyFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * ClearLogs constructor.
     *
     * @param HookFactory $hookFactory
     * @param HistoryFactory $historyFactory
     * @param LoggerInterface $logger
     * @param Data $helper
     */
    public function __construct(
        HookFactory $hookFactory,
        HistoryFactory $historyFactory,
        LoggerInterface $logger,
        Data $helper
    ) {
        $this->hookFactory = $hookFactory;
        $this->historyFactory = $historyFactory;
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**
     * @throw \Exception
     */
    public function execute()
    {
        $limit = (int)$this->helper->getConfigGeneral('keep_log');
        if ($limit <= 0 || !$this->helper->isEnabled()) {
            return;
        }
        try {
            $hookCollection = $this->hookFactory->create()->getCollection();
            foreach ($hookCollection as $hook) {
                $historyCollection = $this->historyFactory->create()->getCollection()
                    ->addFieldToFilter('hook_id', $hook->getId());
                if ($historyCollection->count() > $limit) {
                    $count = $historyCollection->count() - $limit;
                    $historyCollection->setOrder('id');

                    foreach ($historyCollection->getItems() as $history) {
                        if ($count <= 0) {
                            break;
                        }
                        $history->delete();
                        $count--;
                    }
                }
            }
        } catch (Exception $e) {
            $this->logger->critical($e->getLogMessage());
        }
    }
}
