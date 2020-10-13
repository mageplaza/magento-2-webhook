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

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\Webhook\Model\Hook;
use Mageplaza\Webhook\Model\HookFactory;
use RuntimeException;

/**
 * Class InlineEdit
 * @package Mageplaza\Webhook\Controller\Adminhtml\ManageHooks
 */
class InlineEdit extends Action
{
    /**
     * JSON Factory
     *
     * @var JsonFactory
     */
    public $jsonFactory;

    /**
     * Post Factory
     *
     * @var HookFactory
     */
    public $hookFactory;

    /**
     * InlineEdit constructor.
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param HookFactory $hookFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        HookFactory $hookFactory
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->hookFactory = $hookFactory;

        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        $hookItems = $this->getRequest()->getParam('items', []);
        if (!empty($hookItems) && !$this->getRequest()->getParam('isAjax')) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        $key = array_keys($hookItems);
        $hookId = !empty($key) ? (int)$key[0] : '';
        /** @var Hook $hook */
        $hook = $this->hookFactory->create()->load($hookId);
        try {
            $hookData = $hookItems[$hookId];
            $hook->addData($hookData);
            $hook->save();
        } catch (LocalizedException $e) {
            $messages[] = $this->getErrorWithHookId($hook, $e->getMessage());
            $error = true;
        } catch (RuntimeException $e) {
            $messages[] = $this->getErrorWithHookId($hook, $e->getMessage());
            $error = true;
        } catch (Exception $e) {
            $messages[] = $this->getErrorWithHookId(
                $hook,
                __('Something went wrong while saving the Post.')
            );
            $error = true;
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add Hook id to error message
     *
     * @param Hook $hook
     * @param string $errorText
     *
     * @return string
     */
    public function getErrorWithHookId(Hook $hook, $errorText)
    {
        return '[Hook ID: ' . $hook->getId() . '] ' . $errorText;
    }
}
