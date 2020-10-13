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

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Store\Model\Store;
use Mageplaza\Webhook\Model\Config\Source\HookType;
use Mageplaza\Webhook\Model\Config\Source\Schedule;

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
     * @param Observer $observer
     *
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        $item = $observer->getCustomer();
        if ($this->helper->getCronSchedule() !== Schedule::DISABLE) {
            $hookCollection = $this->hookFactory->create()->getCollection()
                ->addFieldToFilter('hook_type', $this->hookType)
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter('store_ids', [
                    ['finset' => Store::DEFAULT_STORE_ID],
                    ['finset' => $this->helper->getItemStore($item)]
                ])
                ->setOrder('priority', 'ASC');
            if ($hookCollection->getSize() > 0) {
                $schedule = $this->scheduleFactory->create();
                $data = [
                    'hook_type' => $this->hookType,
                    'event_id' => $item->getId(),
                    'status' => '0'
                ];

                try {
                    $schedule->addData($data);
                    $schedule->save();
                } catch (Exception $exception) {
                    $this->messageManager->addError($exception->getMessage());
                }
            }
        } else {
            $this->helper->send($item, $this->hookType);
        }
    }
}
