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
 * @package     Mageplaza_BetterCoupon
 * @copyright   Copyright (c) 2018 Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Webhook\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Mageplaza\Webhook\Model\HookFactory;
use Mageplaza\Webhook\Model\HistoryFactory;
use Mageplaza\Webhook\Helper\Data;
use Mageplaza\Webhook\Model\Config\Source\HookType;

/**
 * Class AddNoticeNoRules
 * @package Mageplaza\BetterCoupon\Observer
 */
class BeforeSave implements ObserverInterface
{

    protected $hookFactory;
    protected $historyFactory;
    protected $helper;

    public function __construct(
        HookFactory $hookFactory,
        HistoryFactory $historyFactory,
        Data $helper
    )
    {
        $this->hookFactory = $hookFactory;
        $this->historyFactory = $historyFactory;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if(!$this->helper->isEnabled()){
            return;
        }
        $item = $observer->getDataObject();
        $item->setMpNew(1);
    }
}
