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

namespace Mageplaza\Webhook\Block\Adminhtml\Hook\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Mageplaza\Webhook\Model\Config\Source\HookType;

/**
 * Class General
 * @package Mageplaza\Webhook\Block\Adminhtml\Hook\Edit\Tab
 */
class General extends Generic implements TabInterface
{
    /**
     * @var Enabledisable
     */
    protected $enabledisable;

    /**
     * @var Store
     */
    protected $systemStore;

    /**
     * General constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Enabledisable $enableDisable
     * @param Store $systemStore
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Enabledisable $enableDisable,
        Store $systemStore,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->enabledisable = $enableDisable;
        $this->systemStore   = $systemStore;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        /** @var \Mageplaza\Webhook\Model\Hook $hook */
        $hook = $this->_coreRegistry->registry('mageplaza_webhook_hook');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('hook_');
        $form->setFieldNameSuffix('hook');

        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('General Information'),
            'class'  => 'fieldset-wide'
        ]);

        $fieldset->addField('name', 'text', [
            'name'     => 'name',
            'label'    => __('Name'),
            'title'    => __('Name'),
            'required' => true
        ]);
        $fieldset->addField('hook_type', 'hidden', [
            'name'  => 'hook_type',
            'value' => $this->_request->getParam('type')
                ? $this->_request->getParam('type')
                : HookType::NEW_ORDER
        ]);
        $fieldset->addField('status', 'select', [
            'name'   => 'status',
            'label'  => __('Status'),
            'title'  => __('Status'),
            'values' => $this->enabledisable->toOptionArray()
        ]);

        if (!$this->_storeManager->isSingleStoreMode()) {
            /** @var \Magento\Framework\Data\Form\Element\Renderer\RendererInterface $rendererBlock */
            $rendererBlock = $this->getLayout()->createBlock('Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element');
            $fieldset->addField('store_ids', 'multiselect', [
                'name'     => 'store_ids',
                'label'    => __('Store Views'),
                'title'    => __('Store Views'),
                'required' => true,
                'values'   => $this->systemStore->getStoreValuesForForm(false, true)
            ])->setRenderer($rendererBlock);
        } else {
            $fieldset->addField('store_ids', 'hidden', [
                'name'  => 'store_ids',
                'value' => $this->_storeManager->getStore()->getId()
            ]);
        }

        $fieldset->addField('priority', 'text', [
            'name'  => 'priority',
            'label' => __('Priority'),
            'title' => __('Priority'),
            'note'  => __('0 is highest')
        ]);

        $form->addValues($hook->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
