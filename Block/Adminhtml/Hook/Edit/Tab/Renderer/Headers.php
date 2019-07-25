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

namespace Mageplaza\Webhook\Block\Adminhtml\Hook\Edit\Tab\Renderer;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Phrase;

/**
 * Class Headers
 * @package Mageplaza\Webhook\Block\Adminhtml\Hook\Edit\Tab\Renderer
 */
class Headers extends AbstractFieldArray
{
    /**
     * @var string
     */
    protected $_template = 'Mageplaza_Webhook::hook/headers.phtml';

    /**
     * Initialise form fields
     *
     * @return void
     */
    public function _construct()
    {
        $this->addColumn('name', ['label' => __('Name')]);
        $this->addColumn('value', ['label' => __('Value')]);

        $this->_addAfter = false;

        parent::_construct();
    }

    /**
     * Get Button Label
     *
     * @return Phrase
     */
    public function getAddButtonLabel()
    {
        return __('Add');
    }
}
