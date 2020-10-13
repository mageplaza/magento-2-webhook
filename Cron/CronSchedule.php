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
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\InvoiceFactory;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\Sales\Model\OrderFactory;
use Mageplaza\Webhook\Helper\Data;
use Mageplaza\Webhook\Model\Config\Source\HookType;
use Mageplaza\Webhook\Model\Config\Source\Status;
use Mageplaza\Webhook\Model\HookFactory;
use Mageplaza\Webhook\Model\ResourceModel\CronSchedule\CollectionFactory;
use Psr\Log\LoggerInterface;

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

    /**
     * @var HistoryFactory
     */
    protected $orderHistory;

    /**
     * @var InvoiceFactory
     */
    protected $invoice;

    /**
     * @var CustomerFactory
     */
    protected $customer;

    /**
     * @var ProductFactory
     */
    protected $product;

    /**
     * @var CategoryFactory
     */
    protected $category;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var SubscriberFactory
     */
    protected $subscribe;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * CronSchedule constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param HookFactory $hookFactory
     * @param OrderFactory $orderFactory
     * @param HistoryFactory $historyFactory
     * @param InvoiceFactory $invoiceFactory
     * @param CustomerFactory $customerFactory
     * @param ProductFactory $productFactory
     * @param CategoryFactory $categoryFactory
     * @param SubscriberFactory $subscriberFactory
     * @param LoggerInterface $logger
     * @param Data $data
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        HookFactory $hookFactory,
        OrderFactory $orderFactory,
        HistoryFactory $historyFactory,
        InvoiceFactory $invoiceFactory,
        CustomerFactory $customerFactory,
        ProductFactory $productFactory,
        CategoryFactory $categoryFactory,
        SubscriberFactory $subscriberFactory,
        LoggerInterface $logger,
        Data $data
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->hookFactory = $hookFactory;
        $this->orderFactory = $orderFactory;
        $this->orderHistory = $historyFactory;
        $this->invoice = $invoiceFactory;
        $this->customer = $customerFactory;
        $this->product = $productFactory;
        $this->category = $categoryFactory;
        $this->subscribe = $subscriberFactory;
        $this->logger = $logger;
        $this->helper = $data;
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
            $eventID = $cronTab->getEventId();

            switch ($hookType) {
                case HookType::ORDER:
                    $item = $this->orderFactory->create()->load($eventID);
                    break;
                case HookType::NEW_ORDER_COMMENT:
                    $item = $this->orderHistory->create()->load($eventID);
                    break;
                case HookType::NEW_INVOICE:
                    $item = $this->invoice->create()->load($eventID);
                    break;
                case HookType::NEW_SHIPMENT:
                    $shipment = $this->helper->getObjectClass(Shipment::class);
                    $item = $shipment->load($eventID);
                    break;
                case HookType::NEW_CREDITMEMO:
                    $creditmemo = $this->helper->getObjectClass(Creditmemo::class);
                    $item = $creditmemo->load($eventID);
                    break;
                case HookType::NEW_CUSTOMER:
                case HookType::UPDATE_CUSTOMER:
                case HookType::DELETE_CUSTOMER:
                case HookType::CUSTOMER_LOGIN:
                    $item = $this->customer->create()->load($eventID);
                    break;
                case HookType::NEW_PRODUCT:
                case HookType::UPDATE_PRODUCT:
                case HookType::DELETE_PRODUCT:
                    $item = $this->product->create()->load($eventID);
                    break;
                case HookType::NEW_CATEGORY:
                case HookType::UPDATE_CATEGORY:
                case HookType::DELETE_CATEGORY:
                    $item = $this->category->create()->load($eventID);
                    break;
                case HookType::SUBSCRIBER:
                    $item = $this->subscribe->create()->load($eventID);
            }
            $this->helper->send($item, $hookType);
            try {
                $cronTab->setStatus(Status::SUCCESS)->save();
            } catch (Exception $exception) {
                $this->logger->critical($exception->getLogMessage());
            }
        }
    }
}
