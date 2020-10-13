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

namespace Mageplaza\Webhook\Block\Adminhtml\Hook\Edit\Tab\Renderer;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as CatalogEavAttr;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Newsletter\Model\ResourceModel\Subscriber;
use Magento\Quote\Model\ResourceModel\Quote;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo as CreditmemoResource;
use Magento\Sales\Model\ResourceModel\Order\Invoice as InvoiceResource;
use Magento\Sales\Model\ResourceModel\Order\Shipment as ShipmentResource;
use Magento\Sales\Model\ResourceModel\Order\Status\History as OrderStatusResource;
use Mageplaza\Webhook\Block\Adminhtml\LiquidFilters;
use Mageplaza\Webhook\Model\Config\Source\HookType;
use Mageplaza\Webhook\Model\HookFactory;

/**
 * Class Body
 * @package Mageplaza\Webhook\Block\Adminhtml\Hook\Edit\Tab\Renderer
 */
class Body extends Element
{
    /**
     * @var string $_template
     */
    protected $_template = 'Mageplaza_Webhook::hook/body.phtml';

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var LiquidFilters
     */
    protected $liquidFilters;

    /**
     * @var InvoiceResource
     */
    protected $invoiceResource;

    /**
     * @var ShipmentResource
     */
    protected $shipmentResource;

    /**
     * @var CreditmemoResource
     */
    protected $creditmemoResource;

    /**
     * @var HookFactory
     */
    protected $hookFactory;

    /**
     * @var OrderStatusResource
     */
    protected $orderStatusResource;

    /**
     * @var CustomerResource
     */
    protected $customerResource;

    /**
     * @var CatalogEavAttr
     */
    protected $catalogEavAttribute;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var Quote
     */
    protected $quoteResource;

    /**
     * @var Subscriber
     */
    protected $subscriber;

    /**
     * Body constructor.
     *
     * @param Context $context
     * @param OrderFactory $orderFactory
     * @param InvoiceResource $invoiceResource
     * @param ShipmentResource $shipmentResource
     * @param CreditmemoResource $creditmemoResource
     * @param OrderStatusResource $orderStatusResource
     * @param CustomerResource $customerResource
     * @param Quote $quoteResource
     * @param CatalogEavAttr $catalogEavAttribute
     * @param CategoryFactory $categoryFactory
     * @param LiquidFilters $liquidFilters
     * @param HookFactory $hookFactory
     * @param Subscriber $subscriber
     * @param array $data
     */
    public function __construct(
        Context $context,
        OrderFactory $orderFactory,
        InvoiceResource $invoiceResource,
        ShipmentResource $shipmentResource,
        CreditmemoResource $creditmemoResource,
        OrderStatusResource $orderStatusResource,
        CustomerResource $customerResource,
        Quote $quoteResource,
        CatalogEavAttr $catalogEavAttribute,
        CategoryFactory $categoryFactory,
        LiquidFilters $liquidFilters,
        HookFactory $hookFactory,
        Subscriber $subscriber,
        array $data = []
    ) {
        $this->liquidFilters = $liquidFilters;
        $this->orderFactory = $orderFactory;
        $this->invoiceResource = $invoiceResource;
        $this->shipmentResource = $shipmentResource;
        $this->creditmemoResource = $creditmemoResource;
        $this->hookFactory = $hookFactory;
        $this->orderStatusResource = $orderStatusResource;
        $this->customerResource = $customerResource;
        $this->catalogEavAttribute = $catalogEavAttribute;
        $this->categoryFactory = $categoryFactory;
        $this->quoteResource = $quoteResource;
        $this->subscriber = $subscriber;

        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->_element = $element;

        return $this->toHtml();
    }

    /**
     * @return array
     */

    public function getHookType()
    {
        $type = $this->_request->getParam('type');
        if (!$type) {
            $hookId = $this->getRequest()->getParam('hook_id');
            $hook = $this->hookFactory->create()->load($hookId);
            $type = $hook->getHookType();
        }
        if (!$type) {
            $type = 'order';
        }

        return $type;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getHookAttrCollection()
    {
        $hookType = $this->getHookType();

        switch ($hookType) {
            case HookType::NEW_ORDER_COMMENT:
                $collectionData = $this->orderStatusResource->getConnection()
                    ->describeTable($this->orderStatusResource->getMainTable());
                $attrCollection = $this->getAttrCollectionFromDb($collectionData);
                break;
            case HookType::NEW_INVOICE:
                $collectionData = $this->invoiceResource->getConnection()
                    ->describeTable($this->invoiceResource->getMainTable());
                $attrCollection = $this->getAttrCollectionFromDb($collectionData);
                break;
            case HookType::NEW_SHIPMENT:
                $collectionData = $this->shipmentResource->getConnection()
                    ->describeTable($this->shipmentResource->getMainTable());
                $attrCollection = $this->getAttrCollectionFromDb($collectionData);
                break;
            case HookType::NEW_CREDITMEMO:
                $collectionData = $this->creditmemoResource->getConnection()
                    ->describeTable($this->creditmemoResource->getMainTable());
                $attrCollection = $this->getAttrCollectionFromDb($collectionData);
                break;
            case HookType::NEW_CUSTOMER:
            case HookType::UPDATE_CUSTOMER:
            case HookType::DELETE_CUSTOMER:
            case HookType::CUSTOMER_LOGIN:
                $collectionData = $this->customerResource->loadAllAttributes()->getSortedAttributes();
                $attrCollection = $this->getAttrCollectionFromEav($collectionData);
                break;
            case HookType::NEW_PRODUCT:
            case HookType::UPDATE_PRODUCT:
            case HookType::DELETE_PRODUCT:
                $collectionData = $this->catalogEavAttribute->getCollection()
                    ->addFieldToFilter(AttributeSet::KEY_ENTITY_TYPE_ID, 4);
                $attrCollection = $this->getAttrCollectionFromEav($collectionData);
                break;
            case HookType::NEW_CATEGORY:
            case HookType::UPDATE_CATEGORY:
            case HookType::DELETE_CATEGORY:
                $collectionData = $this->categoryFactory->create()->getAttributes();
                $attrCollection = $this->getAttrCollectionFromEav($collectionData);
                break;
            case HookType::ABANDONED_CART:
                $collectionData = $this->quoteResource->getConnection()
                    ->describeTable($this->quoteResource->getMainTable());
                $attrCollection = $this->getAttrCollectionFromDb($collectionData);
                break;
            case HookType::SUBSCRIBER:
                $collectionData = $this->subscriber->getConnection()
                    ->describeTable($this->subscriber->getMainTable());
                $attrCollection = $this->getAttrCollectionFromDb($collectionData);
                break;
            default:
                $collectionData = $this->orderFactory->create()->getResource()->getConnection()
                    ->describeTable($this->orderFactory->create()->getResource()->getMainTable());
                $attrCollection = $this->getAttrCollectionFromDb($collectionData);
                break;
        }

        return $attrCollection;
    }

    /**
     * @param $collection
     *
     * @return array
     */
    protected function getAttrCollectionFromDb($collection)
    {
        $attrCollection = [];
        foreach ($collection as $item) {
            $attrCollection[] = new DataObject([
                'name' => $item['COLUMN_NAME'],
                'title' => ucwords(str_replace('_', ' ', $item['COLUMN_NAME']))
            ]);
        }

        return $attrCollection;
    }

    /**
     * @param $collection
     *
     * @return array
     */
    protected function getAttrCollectionFromEav($collection)
    {
        $attrCollection = [];
        foreach ($collection as $item) {
            $attrCollection[] = new DataObject([
                'name' => $item->getAttributeCode(),
                'title' => $item->getDefaultFrontendLabel()
            ]);
        }

        return $attrCollection;
    }

    /**
     * @return array
     */
    public function getModifier()
    {
        return $this->liquidFilters->getFilters();
    }
}
