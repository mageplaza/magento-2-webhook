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
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Mageplaza\Webhook\Block\Adminhtml\Hook\Edit\Tab\Renderer\Body;
use Mageplaza\Webhook\Block\Adminhtml\Hook\Edit\Tab\Renderer\Headers;
use Mageplaza\Webhook\Model\Config\Source\Authentication;
use Mageplaza\Webhook\Model\Config\Source\ContentType;
use Mageplaza\Webhook\Model\Config\Source\Method;
use Mageplaza\Webhook\Model\Hook;

/**
 * Class Actions
 * @package Mageplaza\Webhook\Block\Adminhtml\Hook\Edit\Tab
 */
class Actions extends Generic implements TabInterface
{
    /**
     * @var Method
     */
    protected $method;

    /**
     * @var ContentType
     */
    protected $contentType;

    /**
     * @var FieldFactory
     */
    protected $fieldFactory;

    /**
     * @var Authentication
     */
    protected $authentication;

    /**
     * Actions constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param FieldFactory $fieldFactory
     * @param Method $method
     * @param ContentType $contentType
     * @param Authentication $authentication
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        FieldFactory $fieldFactory,
        Method $method,
        ContentType $contentType,
        Authentication $authentication,
        array $data = []
    ) {
        $this->fieldFactory = $fieldFactory;
        $this->method = $method;
        $this->contentType = $contentType;
        $this->authentication = $authentication;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var Hook $rule */
        $hook = $this->_coreRegistry->registry('mageplaza_webhook_hook');

        /** @var Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('hook_');
        $form->setFieldNameSuffix('hook');

        $fieldset = $form->addFieldset('actions_fieldset', [
            'legend' => __('Actions'),
            'class' => 'fieldset-wide'
        ]);
        $fieldset->addField('payload_url', 'text', [
            'name' => 'payload_url',
            'label' => __('Payload URL'),
            'title' => __('Payload URL'),
            'required' => true,
            'note' => __('You can insert a variable'),
            'after_element_html' => '<a id="insert-variable-upload" class="btn">' . __('Insert Variable') . '</a>',
        ]);
        $fieldset->addField('method', 'select', [
            'name' => 'method',
            'label' => __('Method'),
            'title' => __('Method'),
            'values' => $this->method->toOptionArray(),
        ]);

        $authentication = $fieldset->addField('authentication', 'select', [
            'name' => 'authentication',
            'label' => __('Authentication'),
            'title' => __('Authentication'),
            'values' => $this->authentication->toOptionArray(),

        ]);
        $username = $fieldset->addField('username', 'text', [
            'name' => 'username',
            'label' => __('Username'),
            'title' => __('Username'),
        ]);
        $realm = $fieldset->addField('realm', 'text', [
            'name' => 'realm',
            'label' => __('Realm'),
            'title' => __('Realm'),
        ]);
        $password = $fieldset->addField('password', 'password', [
            'name' => 'password',
            'label' => __('Password'),
            'title' => __('Password'),
        ]);
        $nonce = $fieldset->addField('nonce', 'text', [
            'name' => 'nonce',
            'label' => __('Nonce'),
            'title' => __('Nonce'),
        ]);
        $algorithm = $fieldset->addField('algorithm', 'text', [
            'name' => 'algorithm',
            'label' => __('Algorithm'),
            'title' => __('Algorithm'),
        ]);
        $qop = $fieldset->addField('qop', 'text', [
            'name' => 'qop',
            'label' => __('qop'),
            'title' => __('qop'),
        ]);
        $nonceCount = $fieldset->addField('nonce_count', 'text', [
            'name' => 'nonce_count',
            'label' => __('Nonce Count'),
            'title' => __('Nonce Count'),
        ]);
        $clientNonce = $fieldset->addField('client_nonce', 'text', [
            'name' => 'client_nonce',
            'label' => __('Client Nonce'),
            'title' => __('Client Nonce'),
        ]);
        $opaque = $fieldset->addField('opaque', 'text', [
            'name' => 'opaque',
            'label' => __('Opaque'),
            'title' => __('Opaque'),
        ]);
        /** @var RendererInterface $rendererBlock */
        $rendererBlock = $this->getLayout()
            ->createBlock(Headers::class);
        $fieldset->addField('headers', 'text', [
            'name' => 'headers',
            'label' => __('Header'),
            'title' => __('Header'),
        ])->setRenderer($rendererBlock);
        $fieldset->addField('content_type', 'select', [
            'name' => 'content_type',
            'label' => __('Content Type'),
            'title' => __('Content Type'),
            'values' => $this->contentType->toOptionArray(),

        ]);
        /** @var RendererInterface $rendererBlock */
        $rendererBlock = $this->getLayout()->createBlock(Body::class);
        $fieldset->addField('body', 'textarea', [
            'name' => 'body',
            'label' => __('Body'),
            'title' => __('Body'),
            'note' => __(
                'Supports <a href="%1" target="_blank">Liquid template</a>',
                'https://shopify.github.io/liquid/'
            )
        ])->setRenderer($rendererBlock);

        $refField = $this->fieldFactory->create([
            'fieldData' => ['value' => 'basic,digest', 'separator' => ','],
            'fieldPrefix' => ''
        ]);

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(Dependence::class)
                ->addFieldMap($authentication->getHtmlId(), $authentication->getName())
                ->addFieldMap($username->getHtmlId(), $username->getName())
                ->addFieldMap($realm->getHtmlId(), $realm->getName())
                ->addFieldMap($password->getHtmlId(), $password->getName())
                ->addFieldMap($nonce->getHtmlId(), $nonce->getName())
                ->addFieldMap($algorithm->getHtmlId(), $algorithm->getName())
                ->addFieldMap($qop->getHtmlId(), $qop->getName())
                ->addFieldMap($nonceCount->getHtmlId(), $nonceCount->getName())
                ->addFieldMap($clientNonce->getHtmlId(), $clientNonce->getName())
                ->addFieldMap($opaque->getHtmlId(), $opaque->getName())
                ->addFieldDependence($username->getName(), $authentication->getName(), $refField)
                ->addFieldDependence($password->getName(), $authentication->getName(), $refField)
                ->addFieldDependence($realm->getName(), $authentication->getName(), 'digest')
                ->addFieldDependence($nonce->getName(), $authentication->getName(), 'digest')
                ->addFieldDependence($algorithm->getName(), $authentication->getName(), 'digest')
                ->addFieldDependence($qop->getName(), $authentication->getName(), 'digest')
                ->addFieldDependence($nonceCount->getName(), $authentication->getName(), 'digest')
                ->addFieldDependence($clientNonce->getName(), $authentication->getName(), 'digest')
                ->addFieldDependence($opaque->getName(), $authentication->getName(), 'digest')
        );

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
        return __('Actions');
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

    /**
     * Get form HTML
     *
     * @return string
     */
    public function getFormHtml()
    {
        $formHtml = parent::getFormHtml();
        $childHtml = $this->getChildHtml();

        return $formHtml . $childHtml;
    }
}
