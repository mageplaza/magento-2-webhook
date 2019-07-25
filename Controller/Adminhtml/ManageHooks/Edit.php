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

namespace Mageplaza\Webhook\Controller\Adminhtml\ManageHooks;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\Webhook\Controller\Adminhtml\AbstractManageHooks;
use Mageplaza\Webhook\Model\Hook;
use Mageplaza\Webhook\Model\HookFactory;

/**
 * Class Edit
 * @package Mageplaza\Webhook\Controller\Adminhtml\ManageHooks
 */
class Edit extends AbstractManageHooks
{
    /**
     * Page factory
     *
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * Edit constructor.
     *
     * @param HookFactory $hookFactory
     * @param Registry $coreRegistry
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        HookFactory $hookFactory,
        Registry $coreRegistry,
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;

        parent::__construct($hookFactory, $coreRegistry, $context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|Redirect|Page
     */
    public function execute()
    {
        /** @var Hook $hook */
        $hook = $this->initHook();
        if (!$hook) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('mpwebhook/managehooks/index');

            return $resultRedirect;
        }

        $data = $this->_session->getData('mageplaza_webhook_hook', true);
        if (!empty($data)) {
            $hook->setData($data);
        }

        $this->coreRegistry->register('mageplaza_webhook_hook', $hook);

        /** @var \Magento\Backend\Model\View\Result\Page|Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Mageplaza_Webhook::webhook');
        $resultPage->getConfig()->getTitle()->set(__('Hook'));

        $title = $hook->getId() ? __('Edit %1 hook', $hook->getName()) : __('New Hook');
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
