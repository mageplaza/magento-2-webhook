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
 * @package     Mageplaza_Milestone
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Webhook\Plugin\Cron\Model;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\Webhook\Helper\Data;
use Mageplaza\Webhook\Model\Config\Source\Schedule;
use Mageplaza\Webhook\Model\ResourceModel\CronSchedule\CollectionFactory;
use Mageplaza\Webhook\Cron\CronSchedule;

/**
 * Class Config
 * @package Mageplaza\Milestone\Plugin\Cron\Model
 */
class Config
{

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * Config constructor.
     *
     * @param DateTime $date
     * @param Data $helper
     */
    public function __construct(
        DateTime $date,
        Data $helper,
        CollectionFactory $collectionFactory
    ) {
        $this->date   = $date;
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param \Magento\Cron\Model\Config $config
     * @param array $result
     *
     * @return array
     */
    public function afterGetJobs(\Magento\Cron\Model\Config $config, $result)
    {
        if (!$this->helper->isEnabled() || $this->helper->getCronSchedule() === Schedule::DISABLE) {
            return $result;
        }

        $this->addApplyWebhookCron($result);
        return $result;
    }

    /**
     * @param $result
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function addApplyWebhookCron(&$result)
    {
        $schedule   = $this->helper->getCronSchedule();
        $startTime  = $this->helper->getCronSchedule('start_time');
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('status', '0');

        if (!$collection->getSize()) {
            return;
        }

        $result['index']['mpwebhook_cron_schedule'] = [
            'name'     => 'mpwebhook_cron_schedule',
            'instance' => CronSchedule::class,
            'method'   => 'execute',
            'schedule' => $this->helper->getCronExpr($schedule, $startTime)
        ];
    }
}
