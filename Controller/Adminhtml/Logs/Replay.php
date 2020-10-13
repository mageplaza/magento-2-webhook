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

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Mageplaza\Webhook\Controller\Adminhtml\AbstractManageLogs;
use Mageplaza\Webhook\Helper\Data;
use Mageplaza\Webhook\Model\Config\Source\Status;
use Mageplaza\Webhook\Model\History;
use Mageplaza\Webhook\Model\HistoryFactory;
use Mageplaza\Webhook\Model\HookFactory;

/**
 * Class Replay
 * @package Mageplaza\Webhook\Controller\Adminhtml\Logs
 */
class Replay extends AbstractManageLogs
{
    /**
     * @var HookFactory
     */
    protected $hookFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Replay constructor.
     *
     * @param HistoryFactory $historyFactory
     * @param Registry $coreRegistry
     * @param Context $context
     * @param HookFactory $hookFactory
     * @param Data $helperData
     */
    public function __construct(
        HistoryFactory $historyFactory,
        Registry $coreRegistry,
        Context $context,
        HookFactory $hookFactory,
        Data $helperData
    ) {
        $this->hookFactory = $hookFactory;
        $this->helperData = $helperData;

        parent::__construct($historyFactory, $coreRegistry, $context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $log = $this->initLog();
        $resultRedirect->setPath('mpwebhook/logs');
        if ($log->getId()) {
            try {
                $hookId = $log->getHookId();
                $hook = $this->hookFactory->create()->load($hookId);
                if (!$hook->getId()) {
                    $this->messageManager->addError('The Hook no longer exits');

                    return $resultRedirect;
                }
                /** @var History $log */
                $result = $this->helperData->sendHttpRequestFromHook($hook, false, $log);
                $log->setResponse($result['response']);
            } catch (Exception $e) {
                $result = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
            if ($result['success'] === true) {
                $log->setStatus(Status::SUCCESS)->setMessage('');
                $this->messageManager->addSuccess(__('The log has been replay successful.'));
            } else {
                $this->messageManager->addError($result['message']);
                $log->setStatus(Status::ERROR)->setMessage($result['message']);
            }
            $log->save();

            return $resultRedirect;
        }
        // display error message
        $this->messageManager->addError(__('The Log to replay was not found.'));

        return $resultRedirect;
    }
}
