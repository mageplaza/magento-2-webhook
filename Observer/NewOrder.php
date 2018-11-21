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
class NewOrder implements ObserverInterface
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
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $hookCollection = $this->hookFactory->create()->getCollection()
            ->addFieldToFilter('hook_type', HookType::NEW_ORDER)
            ->setOrder('priority', 'ASC');
        $item = $observer->getDataObject();
        foreach ($hookCollection as $hook) {
            $history = $this->historyFactory->create();
            $data = [
                'hook_id' => $hook->getId(),
                'hook_name' => $hook->getName(),
                'store_ids' => $hook->getStoreIds(),
                'hook_type' => $hook->getHookType(),
                'priority' => $hook->getPriority(),
                'payload_url' => $this->helper->generateLiquidTemplate($item, $hook->getPayloadUrl()),
                'body' => $this->helper->generateLiquidTemplate($item, $hook->getBody())
            ];
            $history->addData($data);
            try {
                $result = $this->helper->sendHttpRequestFromHook($hook, $item);
                $hook->setResponse($result['response']);
            } catch (\Exception $e) {
                $result = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
            if ($result['success'] == true) {
                $history->setStatus(1);
            } else {
                $history->setStatus(0)->setMessage($result['message']);
            }
            $history->save();
        }
    }
}
