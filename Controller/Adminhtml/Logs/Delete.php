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
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\Webhook\Controller\Adminhtml\AbstractManageLogs;
use Mageplaza\Webhook\Model\History;

/**
 * Class Delete
 * @package Mageplaza\Webhook\Controller\Adminhtml\Logs
 */
class Delete extends AbstractManageLogs
{
    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $log = $this->initLog();
        if ($log->getId()) {
            try {
                /** @var History $log */
                $log->delete();

                $this->messageManager->addSuccess(__('The log has been deleted.'));
                $resultRedirect->setPath('mpwebhook/*/');

                return $resultRedirect;
            } catch (Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $resultRedirect->setPath('mpwebhook/*/edit', ['id' => $log->getId()]);

                return $resultRedirect;
            }
        }
        // display error message
        $this->messageManager->addError(__('The log to delete was not found.'));
        $resultRedirect->setPath('mpwebhook/*/');

        return $resultRedirect;
    }
}
