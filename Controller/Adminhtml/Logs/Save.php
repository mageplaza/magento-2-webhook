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

namespace Mageplaza\Webhook\Controller\Adminhtml\Logs;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\Webhook\Controller\Adminhtml\AbstractManageLogs;
use Mageplaza\Webhook\Model\History;
use RuntimeException;

/**
 * Class Save
 * @package Mageplaza\Webhook\Controller\Adminhtml\Logs
 */
class Save extends AbstractManageLogs
{
    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPost('log');
        /** @var History $log */
        $log = $this->initLog();

        $log->setBody($data['body']);

        try {
            $log->save();

            $this->messageManager->addSuccess(__('The log has been saved.'));
            $this->_getSession()->setData('mageplaza_webhook_log', false);
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving the Log.'));
        }

        $resultRedirect->setPath('mpwebhook/*/edit', ['id' => $log->getId(), '_current' => true]);

        return $resultRedirect;
    }
}
