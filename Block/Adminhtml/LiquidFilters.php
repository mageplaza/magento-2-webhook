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

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class LiquidFilters
 * @package Mageplaza\Webhook\Block\Adminhtml
 */
class LiquidFilters
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * LiquidFilters constructor.
     *
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @param $subject
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function price($subject)
    {
        return $subject . ' ' . $this->storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * Auto correct for csv,txt file
     *
     * @param $subject
     * @param $fieldAround
     * @param $fieldSeparate
     *
     * @return mixed
     */
    public function mpCorrect($subject, $fieldAround, $fieldSeparate)
    {
        $result = str_replace("\n", "\t", $subject);
        switch ($fieldAround) {
            case 'quotes':
                $result = str_replace('"', "'", $result);
                break;
            case 'quote':
                $result = str_replace('"', "'", $result);
                break;
            default:
                switch ($fieldSeparate) {
                    case ';':
                        $result = str_replace(';', ',', $result);
                        break;
                    case ',':
                        $result = str_replace(',', ';', $result);
                        break;
                    default:
                        $result = str_replace("\t", ' ', $result);
                }
        }

        return $result;
    }

    /**
     * @param $subject
     *
     * @return int
     */
    public function count($subject)
    {
        return count($subject);
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        $filters = [
            'abs' => ['label' => __('Abs'), 'params' => []],
            'append' => ['label' => __('Append'), 'params' => [['label' => __('Append'), 'defVal' => '']]],
            'at_least' => ['label' => __('At Least'), 'params' => [['label' => __('At Least'), 'defVal' => '']]],
            'at_most' => ['label' => __('At Most'), 'params' => [['label' => __('At Most'), 'defVal' => '']]],
            'capitalize' => ['label' => __('Capitalize'), 'params' => []],
            'ceil' => ['label' => __('Ceil'), 'params' => []],
            'date' => ['label' => __('Date'), 'params' => [['label' => __('Date Format'), 'defVal' => '']]],
            'default' => ['label' => __('Default'), 'params' => [['label' => __('Default'), 'defVal' => '']]],
            'divided_by' => [
                'label' => __('Divided By'),
                'params' => [['label' => __('Divided By'), 'defVal' => '']]
            ],
            'downcase' => ['label' => __('Lower Case'), 'params' => []],
            'escape' => ['label' => __('Escape'), 'params' => []],
            'escape_once' => ['label' => __('Escape once'), 'params' => []],
            'floor' => ['label' => __('Floor'), 'params' => []],
            'join' => ['label' => __('Join'), 'params' => [['label' => __('Join By'), 'defVal' => '']]],
            'lstrip' => ['label' => __('Left Trim'), 'params' => []],
            'minus' => ['label' => __('Minus'), 'params' => [['label' => __('Minus'), 'defVal' => '']]],
            'modulo' => ['label' => __('Modulo'), 'params' => [['label' => __('Divided By'), 'defVal' => '']]],
            'newline_to_br' => ['label' => __('Replace new line to <br'), 'params' => []],
            'plus' => ['label' => __('Plus'), 'params' => [['label' => __('Plus'), 'defVal' => '']]],
            'prepend' => ['label' => __('Prepend'), 'params' => [['label' => __('Prepend'), 'defVal' => '']]],
            'remove' => ['label' => __('Remove'), 'params' => [['label' => __('Remove'), 'defVal' => '']]],
            'remove_first' => [
                'label' => __('Remove First'),
                'params' => [['label' => __('Remove'), 'defVal' => '']]
            ],
            'replace' => [
                'label' => __('Replace'),
                'params' => [
                    ['label' => __('Search'), 'defVal' => ''],
                    ['label' => __('Replace'), 'defVal' => '']
                ]
            ],
            'replace_first' => [
                'label' => __('Replace First'),
                'params' => [
                    ['label' => __('Search'), 'defVal' => ''],
                    ['label' => __('Replace'), 'defVal' => '']
                ]
            ],
            'reverse' => ['label' => __('Reverse Array'), 'params' => []],
            'round' => ['label' => __('Round'), 'params' => [['label' => __('Count'), 'defVal' => '']]],
            'rstrip' => ['label' => __('Right Trim'), 'params' => []],
            'size' => ['label' => __('Array Size'), 'params' => []],
            'slice' => [
                'label' => __('Slice'),
                'params' => [
                    ['label' => __('From'), 'defVal' => ''],
                    ['label' => __('To'), 'defVal' => '']
                ]
            ],
            'sort' => ['label' => __('Array Sort'), 'params' => []],
            'strip' => ['label' => __('Trim Text'), 'params' => []],
            'strip_html' => ['label' => __('Strip Html Tags'), 'params' => []],
            'strip_newlines' => ['label' => __('Strip New Line'), 'params' => []],
            'times' => ['label' => __('Times'), 'params' => [['label' => __('Times'), 'defVal' => '']]],
            'truncate' => [
                'label' => __('Truncate'),
                'params' => [
                    ['label' => __('Count'), 'Chars' => ''],
                    ['label' => __('Append Last'), 'defVal' => '']
                ]
            ],
            'truncatewords' => [
                'label' => __('Truncate Words'),
                'params' => [
                    ['label' => __('Words'), 'defVal' => ''],
                    ['label' => __('Append Last'), 'defVal' => '']
                ]
            ],
            'ucwords' => ['label' => __('Uppercase first character of each word '), 'params' => []],
            'uniq' => ['label' => __('Unique Id Of Array'), 'params' => []],
            'upcase' => ['label' => __('Upper Case'), 'params' => []],
            'url_decode' => ['label' => __('Decode Url'), 'params' => []],
            'url_encode' => ['label' => __('Encode Url'), 'params' => []]
        ];

        $customFilter = [
            'count' => ['label' => __('Count'), 'params' => []],
            'price' => ['label' => __('Price'), 'params' => []],
            'ifEmpty' => ['label' => __('If Empty'), 'params' => [['label' => __('Default'), 'defVal' => '']]],
            'phone_correct' => ['label' => __('Correct phone number'), 'params' => []],
        ];

        return array_merge($filters, $customFilter);
    }

    /**
     * @param $phoneNum
     *
     * @return string|string[]|null
     */
    public function phone_correct($phoneNum)
    {
        if (strncmp($phoneNum, '0031', 4) === 0) {
            return $phoneNum;
        }

        if (strncmp($phoneNum, '+31', 3) === 0) {
            return preg_replace('/\+31/', '0031', $phoneNum, 1);
        }

        if (strncmp($phoneNum, '0', 1) === 0) {
            return preg_replace('/0/', '0031', $phoneNum, 1);
        }

        return $phoneNum;
    }

    /**
     * @return array
     */
    public function getFiltersMethods()
    {
        return array_keys($this->getFilters());
    }

    /**
     * @param $subject
     * @param $default
     *
     * @return mixed
     */
    public function ifEmpty($subject, $default)
    {
        if (!$subject) {
            $subject = $default;
        }

        return $subject;
    }
}
