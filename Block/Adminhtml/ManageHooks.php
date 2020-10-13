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

use Magento\Backend\Block\Widget\Button\SplitButton;
use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Mageplaza\Webhook\Model\Config\Source\HookType;

/**
 * Class ManageHooks
 * @package Mageplaza\Webhook\Block\Adminhtml
 */
class ManageHooks extends Container
{
    /**
     * @var HookType
     */
    protected $hookType;

    /**
     * ManageHooks constructor.
     *
     * @param Context $context
     * @param HookType $hookType
     * @param array $data
     */
    public function __construct(
        Context $context,
        HookType $hookType,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->hookType = $hookType;
    }

    /**
     * @return Container
     */

    protected function _prepareLayout()
    {
        $addButtonProps = [
            'id' => 'add_new_hook',
            'label' => __('Add New'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => SplitButton::class,
            'options' => $this->_getAddProductButtonOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        return parent::_prepareLayout();
    }

    /**
     * Retrieve options for 'Add New Trigger' split button
     *
     * @return array
     */
    protected function _getAddProductButtonOptions()
    {
        $splitButtonOptions = [];

        foreach ($this->hookType->toOptionArray() as $hookType) {
            $splitButtonOptions[$hookType['value']] = [
                'label' => $hookType['label'],
                'onclick' => "setLocation('" . $this->getUrl('mpwebhook/managehooks/new', [
                        'type' => $hookType['value']
                    ]) . "')",
                'default' => $hookType['value'] === 'order',
            ];
        }

        return $splitButtonOptions;
    }
}
