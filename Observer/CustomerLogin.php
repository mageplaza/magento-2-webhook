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

namespace Mageplaza\Webhook\Observer;

use Mageplaza\Webhook\Model\Config\Source\HookType;

/**
 * Class CustomerLogin
 * @package Mageplaza\Webhook\Observer
 */
class CustomerLogin extends AfterSave
{
    /**
     * @var string
     */
    protected $hookType = HookType::CUSTOMER_LOGIN;

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $item = $observer->getCustomer();
        $this->send($item, $this->hookType);
    }
}
