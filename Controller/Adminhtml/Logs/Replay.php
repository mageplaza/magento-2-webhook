<?php
/**
 * Mageplaza_Affiliate extension
 *                     NOTICE OF LICENSE
 *
 *                     This source file is subject to the Mageplaza License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     https://www.mageplaza.com/LICENSE.txt
 *
 * @category  Mageplaza
 * @package   Mageplaza_Affiliate
 * @copyright Copyright (c) 2016
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Webhook\Controller\Adminhtml\Logs;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mageplaza\Webhook\Controller\Adminhtml\AbstractManageLogs;
use Mageplaza\Webhook\Model\HistoryFactory;
use Mageplaza\Webhook\Model\HookFactory;
use Mageplaza\Webhook\Helper\Data;

/**
 * Class Replay
 * @package Mageplaza\Webhook\Controller\Adminhtml\Logs
 */
class Replay extends AbstractManageLogs
{
    protected $helperData;
    protected $hookFactory;

    public function __construct(
        HistoryFactory $historyFactory,
        Registry $coreRegistry,
        Context $context,
        Data $helperData,
        HookFactory $hookFactory
    )
    {
        parent::__construct($historyFactory, $coreRegistry, $context);
        $this->helperData = $helperData;
        $this->hookFactory = $hookFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
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
                /** @var \Mageplaza\Webhook\Model\History $log */
                $result = $this->helperData->sendHttpRequestFromHook($hook, false, $log);
                $log->setResponse($result['response']);
            } catch (\Exception $e) {
                $result = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
            if ($result['success'] == true) {
                $log->setStatus(1);
                $this->messageManager->addSuccess(__('The log has been replay successful.'));

            } else {
                $this->messageManager->addError($result['message']);
                $log->setStatus(0)->setMessage($result['message']);
            }
            $log->save();
            return $resultRedirect;
        }
        // display error message
        $this->messageManager->addError(__('The Log to replay was not found.'));

        return $resultRedirect;
    }
}
