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
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Registry;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Address as AddressResource;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo as CreditmemoResource;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item as CreditmemoItem;
use Magento\Sales\Model\ResourceModel\Order\Invoice as InvoiceResource;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\ResourceModel\Order\Item as OrderItem;
use Magento\Sales\Model\ResourceModel\Order\Shipment as ShipmentResource;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Item as ShipmentItem;
use Mageplaza\Webhook\Block\Adminhtml\LiquidFilters;
use Mageplaza\Webhook\Helper\Data;
use Mageplaza\WebHook\Model\Config\Source\HookType;
use Mageplaza\Webhook\Model\Hook;
use Mageplaza\Webhook\Model\HookFactory;

/**
 * Class TemplateContent
 * @package Mageplaza\Webhook\Block\Adminhtml\Hook\Edit\Tab\Renderer
 */
class Body extends Element implements RendererInterface
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
     * @var OrderItem
     */
    protected $orderItemResource;

    /**
     * @var InvoiceItem
     */
    protected $invoiceItemResource;

    /**
     * @var ShipmentItem
     */
    protected $shipmentItemResource;

    /**
     * @var CreditmemoItem
     */
    protected $creditmemoItemResource;

    /**
     * @var LiquidFilters
     */
    protected $liquidFilters;

    /**
     * @var Registry
     */
    protected $registry;


    /**
     * @var JsonHelper
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
     * @var AddressResource
     */
    protected $addressResource;

    /**
     * @var HookFactory
     */
    protected $hookFactory;

    /**
     * TemplateContent constructor.
     *
     * @param Context $context
     * @param AddressResource $addressResource
     * @param OrderFactory $orderFactory
     * @param OrderItem $orderItemResource
     * @param InvoiceItem $invoiceItemResource
     * @param ShipmentItem $shipmentItemResource
     * @param CreditmemoItem $creditmemoItemResource
     * @param InvoiceResource $invoiceResource
     * @param ShipmentResource $shipmentResource
     * @param CreditmemoResource $creditmemoResource
     * @param Registry $registry
     * @param LiquidFilters $liquidFilters
     * @param HookFactory $hookFactory
     * @param DefaultTemplate $defaultTemplate
     * @param array $data
     */
    public function __construct(
        Context $context,
        AddressResource $addressResource,
        OrderFactory $orderFactory,
        OrderItem $orderItemResource,
        InvoiceItem $invoiceItemResource,
        ShipmentItem $shipmentItemResource,
        CreditmemoItem $creditmemoItemResource,
        InvoiceResource $invoiceResource,
        ShipmentResource $shipmentResource,
        CreditmemoResource $creditmemoResource,
        Registry $registry,
        LiquidFilters $liquidFilters,
        HookFactory $hookFactory,
        array $data = [])
    {
        parent::__construct($context, $data);

        $this->registry = $registry;
        $this->liquidFilters = $liquidFilters;
        $this->orderFactory = $orderFactory;
        $this->orderItemResource = $orderItemResource;
        $this->invoiceItemResource = $invoiceItemResource;
        $this->shipmentItemResource = $shipmentItemResource;
        $this->creditmemoItemResource = $creditmemoItemResource;
        $this->invoiceResource = $invoiceResource;
        $this->shipmentResource = $shipmentResource;
        $this->creditmemoResource = $creditmemoResource;
        $this->addressResource = $addressResource;
        $this->hookFactory = $hookFactory;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->_element = $element;
        $html = $this->toHtml();

        return $html;
    }

    /**
     * @return array
     */

    public function getHookType()
    {
        $type = $this->_request->getParam('type');
        if (!$type) {
            $hookId = $this->getRequest()->getParam('id');
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getHookAttrCollection()
    {
        $hookType = $this->getHookType();

        switch ($hookType) {
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
            default:
                $collectionData = $this->orderFactory->create()->getResource()->getConnection()
                    ->describeTable($this->orderFactory->create()->getResource()->getMainTable());
                $attrCollection = $this->getAttrCollectionFromDb($collectionData);
                break;
        }

        return $attrCollection;
    }

    protected function getAttrCollectionFromDb($collection){
        $attrCollection = [];
        foreach ($collection as $item) {
            $attrCollection[] = new DataObject([
                'name'=>$item['COLUMN_NAME'],
                'title'=>ucwords(str_replace('_',' ',$item['COLUMN_NAME']))
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

    public function getElement()
    {
        return parent::getElement(); // TODO: Change the autogenerated stub
    }
}
