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

namespace Mageplaza\Webhook\Model\Config\Source;

use Mageplaza\Webhook\Model\Config\AbstractSource;

/**
 * Class Status
 * @package Mageplaza\Webhook\Model\Config\Source
 */
class Status extends AbstractSource
{
    const SUCCESS = 1;
    const ERROR = 0;

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::SUCCESS => 'Success',
            self::ERROR => 'Error',
        ];
    }
}
