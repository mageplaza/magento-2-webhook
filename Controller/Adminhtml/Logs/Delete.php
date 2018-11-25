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

use Mageplaza\Webhook\Controller\Adminhtml\AbstractManageLogs;

/**
 * Class Delete
 * @package Mageplaza\Affiliate\Controller\Adminhtml\Campaign
 */
class Delete extends AbstractManageLogs
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
	public function execute()
	{
        $resultRedirect = $this->resultRedirectFactory->create();
		$log = $this->initLog();
		if ($log->getId()) {
			try {
				/** @var \Mageplaza\Affiliate\Model\Campaign $campaign */
                $log->delete();

				$this->messageManager->addSuccess(__('The log has been deleted.'));
				$resultRedirect->setPath('mpwebhook/*/');

				return $resultRedirect;
			} catch (\Exception $e) {
				$this->messageManager->addError($e->getMessage());

				// go back to edit form
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
