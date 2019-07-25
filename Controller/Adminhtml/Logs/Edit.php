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

namespace Mageplaza\Webhook\Controller\Adminhtml\Logs;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\Webhook\Controller\Adminhtml\AbstractManageLogs;
use Mageplaza\Webhook\Model\History;
use Mageplaza\Webhook\Model\HistoryFactory;

/**
 * Class Edit
 * @package Mageplaza\Webhook\Controller\Adminhtml\Logs
 */
class Edit extends AbstractManageLogs
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
     * @param HistoryFactory $historyFactory
     * @param Registry $coreRegistry
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        HistoryFactory $historyFactory,
        Registry $coreRegistry,
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;

        parent::__construct($historyFactory, $coreRegistry, $context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|Redirect|Page
     */
    public function execute()
    {
        /** @var History $log */
        $log = $this->initLog();
        if (!$log) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('mpwebhook/logs/index');

            return $resultRedirect;
        }

        /** @var \Magento\Backend\Model\View\Result\Page|Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Mageplaza_Webhook::webhook');
        $resultPage->getConfig()->getTitle()->set(__('Log'));

        $title = __('View log %1', $log->getId());
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
