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
 * @package     Mageplaza_OrderExport
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Webhook\Block\Adminhtml\Hook\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Mageplaza\Webhook\Model\ResourceModel\History\CollectionFactory;

/**
 * Class History
 * @package Mageplaza\Webhook\Block\Adminhtml\Hook\Edit\Tab
 */
class History extends Extended implements TabInterface
{
    /**
     * @var CollectionFactory
     */
    protected $historyCollectionFactory;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * History constructor.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Data $backendHelper
     * @param CollectionFactory $historyCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Data $backendHelper,
        CollectionFactory $historyCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);

        $this->coreRegistry             = $coreRegistry;
        $this->historyCollectionFactory = $historyCollectionFactory;
    }

    /**
     * Set grid params
     */
    public function _construct()
    {
        parent::_construct();

        $this->setId('hook_history_grid');
        $this->setDefaultSort('hook_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareCollection()
    {
        $hook    = $this->getHook();
        $collection = $this->historyCollectionFactory->create();
        $collection = $collection->addFieldToFilter('hook_id', $hook->getId());
        $this->setCollection($collection);
//        echo "<pre>";
//        var_dump($collection->getSize());die('1231');
//        echo "</pre>";

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', [
            'header'           => __('ID'),
            'sortable'         => true,
            'index'            => 'id',
            'type'             => 'number',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
        ]);
        $this->addColumn('hook_id', [
            'header' => __('Hook ID'),
            'name'   => 'hook_id',
            'index'  => 'hook_id',
            'type'             => 'number',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
        ]);
        $this->addColumn('hook_name', [
            'header' => __('Hook Name'),
            'name'   => 'hook_name',
            'index'  => 'hook_name'
        ]);
        $this->addColumn('status', [
            'header' => __('Status'),
            'name'   => 'status',
            'index'  => 'status'
        ]);
//        $this->addColumn('generate_status', [
//            'header' => __('Generation status'),
//            'name'   => 'generate_status',
//            'index'  => 'generate_status',
//        ]);
//        $this->addColumn('delivery_status', [
//            'header' => __('Delivery'),
//            'name'   => 'delivery_status',
//            'index'  => 'delivery_status',
//        ]);
//        $this->addColumn('file', [
//            'header'   => __('Download'),
//            'name'     => 'file',
//            'index'    => 'file',
//            'renderer' => \Mageplaza\OrderExport\Block\Widget\Grid\Column\Renderer\Download::class,
//        ]);
//        $this->addColumn('message', [
//            'header' => __('Message'),
//            'name'   => 'message',
//            'index'  => 'message',
//        ]);
//        $this->addColumn('created_at', [
//            'header'           => __('Generation time'),
//            'index'            => 'created_at',
//            'header_css_class' => 'col-name',
//            'column_css_class' => 'col-name'
//        ]);

        return $this;
    }

    /**
     * get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/log', ['id' => $this->getHook()->getId()]);
    }

    /**
     * @return \Mageplaza\Webhook\Model\Hook
     */
    public function getHook()
    {
        return $this->coreRegistry->registry('mageplaza_webhook_hook');
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return __('Logs');
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('mpwebhook/logs/log', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }
}
