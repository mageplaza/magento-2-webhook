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
 * @package     Mageplaza_Blog
 * @copyright   Copyright (c) 2018 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Webhook\Controller\Adminhtml\ManageHooks;

use function DeepCopy\deep_copy;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Mageplaza\Webhook\Controller\Adminhtml\AbstractManageHooks;
use Mageplaza\Webhook\Model\HookFactory;
use Mageplaza\Webhook\Helper\Data;

/**
 * Class Save
 * @package Mageplaza\Blog\Controller\Adminhtml\Post
 */
class Save extends AbstractManageHooks
{
    /**
     * JS helper
     *
     * @var \Magento\Backend\Helper\Js
     */
    public $jsHelper;

    /**
     * @var \Mageplaza\Blog\Helper\Image
     */
    protected $helperData;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Mageplaza\Blog\Model\PostFactory $postFactory
     * @param \Magento\Backend\Helper\Js $jsHelper
     * @param \Mageplaza\Blog\Helper\Image $imageHelper
     * //     */
//    public function __construct(
//        Context $context,
//        Registry $registry,
//        HookFactory $postFactory,
//        Js $jsHelper,
//        Image $imageHelper
//    )
//    {
//        $this->jsHelper = $jsHelper;
//        $this->imageHelper = $imageHelper;
//
//        parent::__construct($postFactory, $registry, $context);
//    }
    public function __construct(
        \Mageplaza\Webhook\Model\HookFactory $hookFactory,
        Registry $coreRegistry,
        Context $context,
        Data $helperData
    )
    {
        parent::__construct($hookFactory, $coreRegistry, $context);
        $this->helperData = $helperData;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\FileSystemException
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

        if(isset($data['store_ids']) && $data['store_ids']){
            $data['store_ids'] = implode(',',$data['store_ids']);
        }

        $hook->addData($data);

//        \Zend_Debug::dump($hook->getData());die;
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
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving the Post.'));
        }

        $this->_getSession()->setData('mageplaza_webhook_hook_data', $data);

        $resultRedirect->setPath('mpwebhook/*/edit', ['hook_id' => $hook->getId(), '_current' => true]);

        return $resultRedirect;
    }

}
