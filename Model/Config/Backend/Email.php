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

namespace Mageplaza\Webhook\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\ValidatorException;

/**
 * Class Email
 * @package Mageplaza\Webhook\Model\Config\Backend
 */
class Email extends Value
{
    /**
     * @return Value|void
     * @throws ValidatorException
     */
    public function beforeSave()
    {
        if (!empty($this->getValue())) {
            $valueArray = explode(',', $this->getValue());
            foreach ($valueArray as $value) {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new ValidatorException(__('Invalid email format.'));
                }
            }
        }
        parent::beforeSave();
    }
}
