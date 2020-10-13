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

use Magento\Framework\Option\ArrayInterface;

/**
 * Class HookType
 * @package Mageplaza\Webhook\Model\Config\Source
 */
class HookType implements ArrayInterface
{
    const ORDER = 'order';
    const NEW_ORDER_COMMENT = 'new_order_comment';
    const NEW_INVOICE = 'new_invoice';
    const NEW_SHIPMENT = 'new_shipment';
    const NEW_CREDITMEMO = 'new_creditmemo';
    const NEW_CUSTOMER = 'new_customer';
    const UPDATE_CUSTOMER = 'update_customer';
    const DELETE_CUSTOMER = 'delete_customer';
    const NEW_PRODUCT = 'new_product';
    const UPDATE_PRODUCT = 'update_product';
    const DELETE_PRODUCT = 'delete_product';
    const NEW_CATEGORY = 'new_category';
    const UPDATE_CATEGORY = 'update_category';
    const DELETE_CATEGORY = 'delete_category';
    const CUSTOMER_LOGIN = 'customer_login';
    const SUBSCRIBER = 'subscriber';
    //    const UPDATE_CART = 'update_cart';
    const ABANDONED_CART = 'abandoned_cart';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->toArray() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::ORDER => 'Order',
            self::NEW_ORDER_COMMENT => 'New Order Comment',
            self::NEW_INVOICE => 'New Invoice',
            self::NEW_SHIPMENT => 'New Shipment',
            self::NEW_CREDITMEMO => 'New Credit Memo',
            self::NEW_CUSTOMER => 'New Customer',
            self::UPDATE_CUSTOMER => 'Update Customer',
            self::DELETE_CUSTOMER => 'Delete Customer',
            self::NEW_PRODUCT => 'New Product',
            self::UPDATE_PRODUCT => 'Update Product',
            self::DELETE_PRODUCT => 'Delete Product',
            self::NEW_CATEGORY => 'New Category',
            self::UPDATE_CATEGORY => 'Update Category',
            self::DELETE_CATEGORY => 'Delete Category',
            self::CUSTOMER_LOGIN => 'Customer Login',
            self::SUBSCRIBER => 'Subscriber',
            //            self::UPDATE_CART => 'Update cart',
            self::ABANDONED_CART => 'Abandoned Cart',
        ];
    }
}
