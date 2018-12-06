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

namespace Mageplaza\Webhook\Controller\Logs;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use Mageplaza\Webhook\Controller\Adminhtml\AbstractManageHooks;
use Mageplaza\Webhook\Model\HookFactory;
use Mageplaza\Webhook\Helper\Data;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;



/**
 * Class Log
 * @package Mageplaza\Webhook\Controller\Adminhtml\Logs
 */
class Testcron extends \Magento\Framework\App\Action\Action
{
    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;
    protected $helperData;
    protected $quoteFactory;
    protected $timezone;

    public function __construct(\Magento\Framework\App\Action\Context $context,
                                Data $helperData,
                                Collection $quoteFactory,
        TimezoneInterface $timezone
)
    {
        parent::__construct($context);

        $this->helperData = $helperData;
        $this->quoteFactory = $quoteFactory;
        $this->timezone = $timezone;
    }
    /**
     * Log constructor.
     * @param HookFactory $hookFactory
     * @param Registry $coreRegistry
     * @param Context $context
     * @param LayoutFactory $resultLayoutFactory
     */
//    public function __construct(
//        HookFactory $hookFactory,
//        Registry $coreRegistry,
//        Context $context,
//        LayoutFactory $resultLayoutFactory,
//        Data $helperData,
//        QuoteFactory $quoteFactory,
//        TimezoneInterface $timezone
//
//    )
//    {
//        parent::__construct($hookFactory, $coreRegistry, $context);
//
//        $this->resultLayoutFactory = $resultLayoutFactory;
//        $this->helperData = $helperData;
//        $this->quoteFactory = $quoteFactory;
//        $this->timezone = $timezone;
//    }

    /**
     * Hook send request log
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->helperData->isEnabled()) {
            return;
        }
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $abandonedTime = (int)$this->helperData->getConfigGeneral('abandoned_time');
        $update        = (new \DateTime())->sub(new \DateInterval("PT{$abandonedTime}M"));
        $updateFrom    = clone $update;
        $updateFrom    = $this->convertToLocaleTime($updateFrom->format('Y-m-d H:i:s'));
        $updateTo      = $update->add(new \DateInterval("PT1H"));

        $updateTo      = $this->convertToLocaleTime($updateTo->format('Y-m-d H:i:s'));

        // $quoteCollection           = $this->quoteFactory->create()->getCollection()
        //     ->addFieldToFilter('is_active', 1)
        //     ->addFieldToFilter('updated_at', ['from' => $updateFrom])
        //     ->addFieldToFilter('updated_at', ['to' => $updateTo]);
        $noneUpdateQuoteCollection = $this->quoteFactory->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('created_at',['from' => '2018-12-06 09:00:00']);

        \Zend_Debug::dump($noneUpdateQuoteCollection->getData());

//            ->addFieldToFilter('created_at',['>=' =>  '2018-12-06 17:00:44'])
//            ->addFieldToFilter('created_at', ['<=' => '2018-12-06 17:15:44']);
//            ->addFieldToFilter('updated_at', ['eq' => '0000-00-00 00:00:00']);
        ;
// $logger->info($quoteCollection->getSize());
//        $logger->info(count($noneUpdateQuoteCollection));
//        $logger->info($noneUpdateQuoteCollection->getData());
//        $logger->info($noneUpdateQuoteCollection->getSelect()->__toString());
       // \Zend_Debug::dump($noneUpdateQuoteCollection->getSelect()->__toString());
//        \Zend_Debug::dump($noneUpdateQuoteCollection);
//        foreach ($noneUpdateQuoteCollection as $item){
//            \Zend_Debug::dump(json_decode(json_encode($item)));
//        }
        die;
        try {
            // foreach ($quoteCollection as $quote) {
            //     $this->helper->sendObserver($quote, HookType::ABANDONED_CART);
            // }
            foreach ($noneUpdateQuoteCollection as $quote) {
                $this->helperData->sendObserver($quote, HookType::ABANDONED_CART);
            }
        } catch (\Exception $e) {
//            $this->logger->critical($e->getLogMessage());
        }
    }
    public function convertToLocaleTime($time)
    {
        $localTime = new \DateTime($time, new \DateTimeZone('UTC'));
        $localTime->setTimezone(new \DateTimeZone($this->timezone->getConfigTimezone()));

//        $localTime = $localTime->format('Y-m-d H:i:s');

        return $localTime;
    }
}

