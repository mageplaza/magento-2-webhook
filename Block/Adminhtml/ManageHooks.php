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

namespace Mageplaza\Webhook\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Class ManageHooks
 * @package Mageplaza\Webhook\Block\Adminhtml
 */
class ManageHooks extends Container
{
    /**
     * constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_hook';
        $this->_blockGroup = 'Mageplaza_Webhook';
        $this->_headerText = __('Manage Hooks');
        $this->_addButtonLabel = __('Add New Hook');

        parent::_construct();
    }
}
