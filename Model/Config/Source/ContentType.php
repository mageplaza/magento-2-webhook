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
 * Class ContentType
 * @package Mageplaza\Webhook\Model\Config\Source
 */
class ContentType extends AbstractSource
{
    const APPLICATION_JSON = 'application/json';
    const APPLICATION_X_WWW_FORM_URLENCODE = 'application/x-www-form-urlencoded';
    const APPLICATION_XML = 'application/xml';
    const APPLICATION_JSON_CHARSET_UTF_8 = 'application/json; charset=UTF-8';

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            '' => __('--Please Select--'),
            self::APPLICATION_JSON => 'application/json',
            self::APPLICATION_X_WWW_FORM_URLENCODE => 'application/x-www-form-urlencoded',
            self::APPLICATION_XML => 'application/xml',
            self::APPLICATION_JSON_CHARSET_UTF_8 => 'application/json; charset=UTF-8',
        ];
    }
}
