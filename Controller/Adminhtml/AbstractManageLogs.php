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
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Webhook\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mageplaza\Webhook\Model\History;
use Mageplaza\Webhook\Model\HistoryFactory;

/**
 * Class AbstractManageLogs
 * @package Mageplaza\Webhook\Controller\Adminhtml
 */
abstract class AbstractManageLogs extends Action
{
    /** Authorization level of a basic admin session */
    const ADMIN_RESOURCE = 'Mageplaza_Webhook::webhook';

    /**
     * @var HistoryFactory
     */
    public $historyFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    public $coreRegistry;

    /**
     * AbstractManageHooks constructor.
     *
     * @param HistoryFactory $historyFactory
     * @param Registry $coreRegistry
     * @param Context $context
     */
    public function __construct(
        HistoryFactory $historyFactory,
        Registry $coreRegistry,
        Context $context
    ) {
        $this->historyFactory = $historyFactory;
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    /**
     * @return bool|History
     */
    protected function initLog()
    {
        $logId = $this->getRequest()->getParam('id');

        /** @var History $log */
        $log = $this->historyFactory->create();

        if ($logId) {
            $log = $log->load($logId);
            if (!$log->getId()) {
                $this->messageManager->addErrorMessage(__('This log no longer exists.'));

                return false;
            }
        }
        $this->coreRegistry->register('mageplaza_webhook_log', $log);

        return $log;
    }
}
