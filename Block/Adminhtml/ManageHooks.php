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

namespace Mageplaza\Webhook\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;
use Magento\Backend\Block\Widget\Context;
use Mageplaza\Webhook\Model\Config\Source\HookType;

/**
 * Class ManageHooks
 * @package Mageplaza\Webhook\Block\Adminhtml
 */
class ManageHooks extends \Magento\Backend\Block\Widget\Container
{
    protected $hookType;

    public function __construct(
        Context $context,
        HookType $hookType,
        array $data = [])
    {
        parent::__construct($context, $data);

        $this->hookType = $hookType;
    }

    /**
     * constructor
     *
     * @return void
     */
//    protected function _construct()
//    {
//        $this->_controller = 'adminhtml_hook';
//        $this->_blockGroup = 'Mageplaza_Webhook';
//        $this->_headerText = __('Manage Hooks');
////        $this->_addButtonLabel = __('Add New Hook');
//
//        parent::_construct();
//    }

    /**
     * Prepare button and grid
     *
     * @return \Mageplaza\Webhook\Block\Adminhtml\ManageHooks
     */

    protected function _prepareLayout()
    {
        $addButtonProps = [
            'id' => 'add_new_hook',
            'label' => __('New Order'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->_getAddProductButtonOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        return parent::_prepareLayout();
    }

    /**
     * Retrieve options for 'Add Profile' split button
     *
     * @return array
     */
    protected function _getAddProductButtonOptions()
    {
        $splitButtonOptions = [];
//        $splitButtonOptions['new_order'] = [
//            'label' => __('New Order'),
//            'onclick' => "setLocation('" . $this->getUrl('mpwebhook/webhook/new', ['type' => 'new_order']) . "')",
//            'default' => true,
//        ];
//        $splitButtonOptions['new_invoice'] = [
//            'label' => __('New Invoice'),
//            'onclick' => "setLocation('" . $this->getUrl('mpwebhook/webhook/new', ['type' => 'new_invoice']) . "')",
//        ];
//        $splitButtonOptions['new_creditmemo'] = [
//            'label' => __('New Creditmemo'),
//            'onclick' => "setLocation('" . $this->getUrl('mpwebhook/manageprofiles/new', ['type' => 'new_creditmemo']) . "')",
//        ];
//        $splitButtonOptions['new_shipment'] = [
//            'label' => __('New Shipment'),
//            'onclick' => "setLocation('" . $this->getUrl('mpwebhook/manageprofiles/new', ['type' => 'new_shipment']) . "')",
//        ];
        foreach ($this->hookType->toOptionArray() as $hookType){
            $splitButtonOptions[$hookType['value']] = [
                'label' => $hookType['label'],
                'onclick' => "setLocation('" . $this->getUrl('mpwebhook/managehooks/new', ['type' => $hookType['value']]) . "')",
                'default' => $hookType['value'] === 'new_order' ? true : false,
            ];
        }

        return $splitButtonOptions;
    }
}
