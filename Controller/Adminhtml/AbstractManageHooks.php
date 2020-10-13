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
use Mageplaza\Webhook\Model\Hook;
use Mageplaza\Webhook\Model\HookFactory;

/**
 * Class AbstractManageHooks
 * @package Mageplaza\Webhook\Controller\Adminhtml
 */
abstract class AbstractManageHooks extends Action
{
    /** Authorization level of a basic admin session */
    const ADMIN_RESOURCE = 'Mageplaza_Webhook::webhook';

    /**
     * @var HookFactory
     */
    public $hookFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    public $coreRegistry;

    /**
     * AbstractManageHooks constructor.
     *
     * @param HookFactory $hookFactory
     * @param Registry $coreRegistry
     * @param Context $context
     */
    public function __construct(
        HookFactory $hookFactory,
        Registry $coreRegistry,
        Context $context
    ) {
        $this->hookFactory = $hookFactory;
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    /**
     * @param bool $register
     *
     * @return bool|Hook
     */
    protected function initHook($register = false)
    {
        $hookId = $this->getRequest()->getParam('hook_id');

        /** @var Hook $hook */
        $hook = $this->hookFactory->create();

        if ($hookId) {
            $hook = $hook->load($hookId);
            if (!$hook->getId()) {
                $this->messageManager->addErrorMessage(__('This hook no longer exists.'));

                return false;
            }
        }
        if ($register) {
            $this->coreRegistry->register('mageplaza_webhook_hook', $hook);
        }

        return $hook;
    }
}
