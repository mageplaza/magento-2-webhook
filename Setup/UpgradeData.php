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
 * @package     Mageplaza_Blog
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Webhook\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Mageplaza\Webhook\Model\CronScheduleFactory;
use Mageplaza\Webhook\Model\HistoryFactory;
use Mageplaza\Webhook\Model\HookFactory;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var HookFactory
     */
    protected $hookFactory;

    /**
     * @var CronScheduleFactory
     */
    protected $cronScheduleFactory;

    /**
     * @var HistoryFactory
     */
    protected $historyFactory;

    /**
     * UpgradeData constructor.
     *
     * @param HookFactory $hookFactory
     * @param CronScheduleFactory $cronScheduleFactory
     * @param HistoryFactory $historyFactory
     */
    public function __construct(
        HookFactory $hookFactory,
        CronScheduleFactory $cronScheduleFactory,
        HistoryFactory $historyFactory
    ) {
        $this->hookFactory = $hookFactory;
        $this->cronScheduleFactory = $cronScheduleFactory;
        $this->historyFactory = $historyFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            /**
             * Update hook type of mageplaza_webhook_hook table
             */
            $hookCollections = $this->hookFactory->create()->getCollection()
                ->addFieldToFilter('hook_type', ['eq' => 'new_order']);
            foreach ($hookCollections as $hook) {
                $hook->setHookType('order');
            }

            $hookCollections->save();

            /**
             * Update hook type of mageplaza_webhook_cron_schedule table
             */
            $cronScheduleCollections = $this->cronScheduleFactory->create()->getCollection()
                ->addFieldToFilter('hook_type', ['eq' => 'new_order']);
            foreach ($cronScheduleCollections as $cronSchedule) {
                $cronSchedule->setHookType('order');
            }

            $cronScheduleCollections->save();

            /**
             * Update hook type of mageplaza_webhook_history table
             */
            $historyCollections = $this->historyFactory->create()->getCollection()
                ->addFieldToFilter('hook_type', ['eq' => 'new_order']);
            foreach ($historyCollections as $history) {
                $history->setHookType('order');
            }

            $historyCollections->save();
        }
    }
}
