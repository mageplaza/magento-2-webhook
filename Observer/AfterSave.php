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

namespace Mageplaza\Webhook\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Webhook\Helper\Data;
use Mageplaza\Webhook\Model\Config\Source\Schedule;
use Mageplaza\Webhook\Model\CronScheduleFactory;
use Mageplaza\Webhook\Model\HookFactory;

/**
 * Class AfterSave
 * @package Mageplaza\Webhook\Observer
 */
abstract class AfterSave implements ObserverInterface
{
    /**
     * @var HookFactory
     */
    protected $hookFactory;

    /**
     * @var CronScheduleFactory
     */
    protected $scheduleFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var string
     */
    protected $hookType = '';

    /**
     * @var string
     */
    protected $hookTypeUpdate = '';

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * AfterSave constructor.
     *
     * @param HookFactory $hookFactory
     * @param CronScheduleFactory $cronScheduleFactory
     * @param ManagerInterface $messageManager
     * @param StoreManagerInterface $storeManager
     * @param Data $helper
     */
    public function __construct(
        HookFactory $hookFactory,
        CronScheduleFactory $cronScheduleFactory,
        ManagerInterface $messageManager,
        StoreManagerInterface $storeManager,
        Data $helper
    ) {
        $this->hookFactory = $hookFactory;
        $this->helper = $helper;
        $this->scheduleFactory = $cronScheduleFactory;
        $this->messageManager = $messageManager;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Observer $observer
     *
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        $item = $observer->getDataObject();
        $schedule = $this->helper->getCronSchedule();
        if ($schedule !== Schedule::DISABLE && $schedule !== null) {
            $hookCollection = $this->hookFactory->create()->getCollection()
                ->addFieldToFilter('hook_type', $this->hookType)
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter('store_ids', [
                    ['finset' => Store::DEFAULT_STORE_ID],
                    ['finset' => $this->helper->getItemStore($item)]
                ])
                ->setOrder('priority', 'ASC');
            if ($hookCollection->getSize() > 0) {
                $schedule = $this->scheduleFactory->create();
                $data = [
                    'hook_type' => $this->hookType,
                    'event_id' => $item->getId(),
                    'status' => '0'
                ];

                try {
                    $schedule->addData($data);
                    $schedule->save();
                } catch (Exception $exception) {
                    $this->messageManager->addError($exception->getMessage());
                }
            }
        } else {
            $this->helper->send($item, $this->hookType);
        }
    }

    /**
     * @param $observer
     *
     * @throws Exception
     */
    protected function updateObserver($observer)
    {
        $item = $observer->getDataObject();
        $schedule = $this->helper->getCronSchedule();
        if ($schedule !== Schedule::DISABLE && $schedule !== null) {
            $hookCollection = $this->hookFactory->create()->getCollection()
                ->addFieldToFilter('hook_type', $this->hookType)
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter('store_ids', [
                    ['finset' => Store::DEFAULT_STORE_ID],
                    ['finset' => $this->helper->getItemStore($item)]
                ])
                ->setOrder('priority', 'ASC');
            if ($hookCollection->getSize() > 0) {
                $schedule = $this->scheduleFactory->create();
                $data = [
                    'hook_type' => $this->hookTypeUpdate,
                    'event_id' => $item->getId(),
                    'status' => '0'
                ];
                try {
                    $schedule->addData($data);
                    $schedule->save();
                } catch (Exception $exception) {
                    $this->messageManager->addError($exception->getMessage());
                }
            }
        } else {
            $this->helper->send($item, $this->hookTypeUpdate);
        }
    }
}
