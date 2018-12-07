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

use Magento\Framework\Event\ObserverInterface;
use Mageplaza\Webhook\Helper\Data;
use Mageplaza\Webhook\Model\HistoryFactory;
use Mageplaza\Webhook\Model\HookFactory;

/**
 * Class AfterSave
 * @package Mageplaza\Webhook\Observer
 */
abstract class AfterSave implements ObserverInterface
{
    /**
     * @var HookFactory
     */
    protected $hookFactory;

    /**
     * @var HistoryFactory
     */
    protected $historyFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var string
     */
    protected $hookType = '';

    /**
     * @var string
     */
    protected $hookTypeUpdate = '';

    /**
     * AfterSave constructor.
     * @param HookFactory $hookFactory
     * @param HistoryFactory $historyFactory
     * @param Data $helper
     */
    public function __construct(
        HookFactory $hookFactory,
        HistoryFactory $historyFactory,
        Data $helper
    )
    {
        $this->hookFactory    = $hookFactory;
        $this->historyFactory = $historyFactory;
        $this->helper         = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $item = $observer->getDataObject();
        $this->send($item, $this->hookType);
    }

    /**
     * @param $observer
     * @throws \Exception
     */
    protected function updateObserver($observer)
    {
        $item = $observer->getDataObject();
        $this->send($item, $this->hookTypeUpdate);
    }

    /**
     * @param $item
     * @param $hookType
     * @throws \Exception
     */
    protected function send($item, $hookType)
    {
        if (!$this->helper->isEnabled()) {
            return;
        }
        $hookCollection = $this->hookFactory->create()->getCollection()
            ->addFieldToFilter('hook_type', $hookType)
            ->addFieldToFilter('status', 1)
            ->setOrder('priority', 'ASC');
        $isSendMail     = $this->helper->getConfigGeneral('alert_enabled');
        $sendTo         = explode(',', $this->helper->getConfigGeneral('send_to'));
        foreach ($hookCollection as $hook) {
            $history = $this->historyFactory->create();
            $data    = [
                'hook_id'     => $hook->getId(),
                'hook_name'   => $hook->getName(),
                'store_ids'   => $hook->getStoreIds(),
                'hook_type'   => $hook->getHookType(),
                'priority'    => $hook->getPriority(),
                'payload_url' => $this->helper->generateLiquidTemplate($item, $hook->getPayloadUrl()),
                'body'        => $this->helper->generateLiquidTemplate($item, $hook->getBody())
            ];
            $history->addData($data);
            try {
                $result = $this->helper->sendHttpRequestFromHook($hook, $item);
                $history->setResponse(isset($result['response']) ? $result['response'] : '');
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
                if ($isSendMail) {
                    $this->helper->sendMail($sendTo,
                        __('Something went wrong while sending %1 hook', $hook->getName()),
                        $this->helper->getConfigGeneral('email_template'),
                        $this->helper->getStoreId()
                    );
                }
            }

            $history->save();
        }
    }
}
