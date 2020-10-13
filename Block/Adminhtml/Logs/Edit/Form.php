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

namespace Mageplaza\Webhook\Block\Adminhtml\Logs\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Mageplaza\Webhook\Model\Config\Source\HookType;
use Mageplaza\Webhook\Model\Config\Source\Status;
use Mageplaza\Webhook\Model\History;

/**
 * Class Form
 * @package Mageplaza\Webhook\Block\Adminhtml\Logs\Edit
 */
class Form extends Generic
{
    /**
     * @var HookType
     */
    protected $hookType;

    /**
     * Form constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param HookType $hookType
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        HookType $hookType,
        array $data = []
    ) {
        $this->hookType = $hookType;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
                'action' => $this->getData('action'),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            ]
        ]);
        $form->setUseContainer(true);
        /** @var History $log */
        $log = $this->_coreRegistry->registry('mageplaza_webhook_log');

        $log->setStatus((int)$log->getStatus() === Status::SUCCESS ? __('Success') : __('Error'));

        $form->setHtmlIdPrefix('log_');
        $form->setFieldNameSuffix('log');

        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('General Information'),
            'class' => 'fieldset-wide'
        ]);

        $fieldset->addField('id', 'label', [
            'name' => 'id',
            'label' => __('Log ID'),
            'title' => __('Log ID'),
        ]);
        $fieldset->addField('hook_type', 'label', [
            'name' => 'hook_type',
            'label' => __('Entity'),
            'title' => __('Entity'),
            'values' => $this->hookType->toOptionArray()
        ]);
        $fieldset->addField('status', 'label', [
            'name' => 'status',
            'label' => __('Status'),
            'title' => __('Status'),
        ]);

        $fieldset->addField('response', 'textarea', [
            'name' => 'response',
            'label' => __('Response'),
            'title' => __('Response'),
            'readonly' => true
        ]);

        $fieldset->addField('body', 'textarea', [
            'name' => 'body',
            'label' => __('Request Body'),
            'title' => __('Request Body')
        ]);

        $form->addValues($log->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
