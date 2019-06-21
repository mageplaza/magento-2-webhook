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

use Mageplaza\Webhook\Model\HookFactory;
use Mageplaza\Webhook\Model\ResourceModel\CronSchedule\CollectionFactory;
use Mageplaza\Webhook\Model\Config\Source\HookType;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\Sales\Model\Order\InvoiceFactory;
use Magento\Customer\Model\CustomerFactory;
use Mageplaza\Webhook\Helper\Data;

/**
 * Class ApplyRule
 * @package Mageplaza\Webhook\Cron
 */
class CronSchedule
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var HookFactory
     */
    protected $hookFactory;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    protected $orderHistory;

    protected $invoice;

    protected $customer;

    protected $helper;

    /**
     * CronSchedule constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param HookFactory $hookFactory
     * @param OrderFactory $orderFactory
     * @param HistoryFactory $historyFactory
     * @param InvoiceFactory $invoiceFactory
     * @param CreditmemoFactory $creditmemoFactory
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        HookFactory $hookFactory,
        OrderFactory $orderFactory,
        HistoryFactory $historyFactory,
        InvoiceFactory $invoiceFactory,
        CustomerFactory $customerFactory,
        Data $data
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->hookFactory       = $hookFactory;
        $this->orderFactory      = $orderFactory;
        $this->orderHistory      = $historyFactory;
        $this->invoice           = $invoiceFactory;
        $this->customer          = $customerFactory;
        $this->helper            = $data;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var  $collection */
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('status', '0');
        foreach ($collection->getItems() as $cronTab) {
            $hookType = $cronTab->getHookType();
            $eventID  = $cronTab->getEventId();

            switch ($hookType) {
                case HookType::NEW_ORDER:
                    $item = $this->orderFactory->create()->load($eventID);
                    break;
                case HookType::NEW_ORDER_COMMENT:
                    $item = $this->orderHistory->create()->load($eventID);
                    break;
                case HookType::NEW_INVOICE:
                    $item = $this->invoice->create()->load($eventID);
                    break;
                case HookType::NEW_SHIPMENT:
                    $shipment = $this->helper->getObjectClass('\Magento\Sales\Model\Order\Shipment');
                    $item = $shipment->load($eventID);
                    break;
                case HookType::NEW_CREDITMEMO:
                    $creditmemo = $this->helper->getObjectClass('\Magento\Sales\Model\Order\Creditmemo');
                    $item = $creditmemo->load($eventID);
                    break;
                case HookType::NEW_CUSTOMER:
                case HookType::UPDATE_CUSTOMER:
                case HookType::DELETE_CUSTOMER:
                case HookType::CUSTOMER_LOGIN:
                    $item = $this->customer->create()->load($eventID);
                    break;
            }

            /** @var  $hookCollection */
            $hookCollection = $this->hookFactory->create()->getCollection()
                ->addFieldToFilter('hook_type', $hookType)
                ->addFieldToFilter('status', 1)
                ->setOrder('priority', 'ASC');
        }
    }
}
