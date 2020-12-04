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

namespace Mageplaza\Webhook\Controller\Adminhtml\ManageHooks;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Mageplaza\Webhook\Controller\Adminhtml\AbstractManageHooks;
use Mageplaza\Webhook\Helper\Data;
use Mageplaza\Webhook\Model\HookFactory;
use RuntimeException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Save
 * @package Mageplaza\Webhook\Controller\Adminhtml\ManageHooks
 */
class Save extends AbstractManageHooks
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Save constructor.
     *
     * @param HookFactory $hookFactory
     * @param Registry $coreRegistry
     * @param Context $context
     * @param Data $helperData
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        HookFactory $hookFactory,
        Registry $coreRegistry,
        Context $context,
        Data $helperData,
        StoreManagerInterface $storeManager
    ) {
        $this->helperData    = $helperData;
        $this->_storeManager = $storeManager;

        parent::__construct($hookFactory, $coreRegistry, $context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPost('hook');
        $hook = $this->initHook();

        if (is_array($data['headers'])) {
            unset($data['headers']['__empty']);
            $data['headers'] = Data::jsonEncode($data['headers']);
        }

        if (isset($data['order_status']) && $data['order_status']) {
            $data['order_status'] = implode(',', $data['order_status']);
        }

        if (isset($data['store_ids']) && $data['store_ids'] && !$this->_storeManager->isSingleStoreMode()) {
            $data['store_ids'] = implode(',', $data['store_ids']);
        }

        $hook->addData($data);

        try {
            $hook->save();

            $this->messageManager->addSuccess(__('The hook has been saved.'));
            $this->_getSession()->setData('mageplaza_webhook_hook_data', false);

            if ($this->getRequest()->getParam('back')) {
                $resultRedirect->setPath('mpwebhook/*/edit', ['hook_id' => $hook->getId(), '_current' => true]);
            } else {
                $resultRedirect->setPath('mpwebhook/*/');
            }

            return $resultRedirect;
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving the Post.'));
        }

        $this->_getSession()->setData('mageplaza_webhook_hook_data', $data);

        $resultRedirect->setPath('mpwebhook/*/edit', ['hook_id' => $hook->getId(), '_current' => true]);

        return $resultRedirect;
    }
}
