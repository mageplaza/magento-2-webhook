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

namespace Mageplaza\Webhook\Block\Adminhtml\Logs;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;
use Mageplaza\Webhook\Model\History;

/**
 * Class Edit
 * @package Mageplaza\Webhook\Block\Adminhtml\Logs
 */
class Edit extends Container
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * constructor
     *
     * @param Registry $coreRegistry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Registry $coreRegistry,
        Context $context,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context, $data);
    }

    /**
     * Initialize Hook edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Mageplaza_Webhook';
        $this->_controller = 'adminhtml_logs';

        parent::_construct();

        /** @var History $log */
        $log = $this->coreRegistry->registry('mageplaza_webhook_log');

        if ($log->getId()) {
            $this->buttonList->add('replay', [
                'label' => __('Replay'),
                'onclick' => sprintf("location.href = '%s';", $this->getReplayUrl($log)),
            ], -90);
        }
    }

    /**
     * Get replay action URL
     *
     * @param $log
     * @return string
     */
    protected function getReplayUrl($log)
    {
        return $this->getUrl('*/*/replay', ['id' => $log->getId()]);
    }

    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        /** @var History $feed */
        $log = $this->coreRegistry->registry('mageplaza_webhook_log');
        if ($id = $log->getId()) {
            return $this->getUrl('*/*/save', ['id' => $id]);
        }

        return parent::getFormActionUrl();
    }
}
