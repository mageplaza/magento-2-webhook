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

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use Mageplaza\Webhook\Controller\Adminhtml\AbstractManageHooks;
use Mageplaza\Webhook\Model\HookFactory;

/**
 * Class Log
 * @package Mageplaza\Webhook\Controller\Adminhtml\Logs
 */
class Log extends AbstractManageHooks
{
    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * Log constructor.
     * @param HookFactory $hookFactory
     * @param Registry $coreRegistry
     * @param Context $context
     * @param LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        HookFactory $hookFactory,
        Registry $coreRegistry,
        Context $context,
        LayoutFactory $resultLayoutFactory

    )
    {
        parent::__construct($hookFactory, $coreRegistry, $context);

        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * Hook generate log
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->initHook(true);

        return $this->resultLayoutFactory->create();
    }
}

