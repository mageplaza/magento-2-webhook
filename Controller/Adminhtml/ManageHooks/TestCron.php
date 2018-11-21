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
 * @package     Mageplaza_Blog
 * @copyright   Copyright (c) 2018 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Webhook\Controller\Adminhtml\ManageHooks;

use function DeepCopy\deep_copy;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Setup\Exception;
use Mageplaza\Webhook\Controller\Adminhtml\AbstractManageHooks;
use Mageplaza\Webhook\Model\HookFactory;
use Magento\Backend\Block\Template;
use phpDocumentor\Reflection\Types\This;
use Magento\Framework\Stdlib\DateTime\DateTime;

class TestCron extends AbstractManageHooks
{
	/**
	 * JS helper
	 *
	 * @var \Magento\Backend\Helper\Js
	 */
	public $jsHelper;

	/**
	 * @var \Mageplaza\Blog\Helper\Image
	 */
	protected $imageHelper;
	protected $helperData;
	protected $historyFactory;
	protected $date;
	protected $cron;

	public function __construct(
		HookFactory $hookFactory, Registry $coreRegistry, Context $context,
		DateTime $date,
		\Mageplaza\Webhook\Helper\Data $helperData,
		\Mageplaza\Webhook\Model\HistoryFactory $historyFactory,
		\Mageplaza\Webhook\Cron\Generate $cron
	)
	{
		parent::__construct($hookFactory, $coreRegistry, $context);

		$this->date = $date;
		$this->helperData = $helperData;
		$this->historyFactory = $historyFactory;
		$this->cron = $cron;
	}

	/**
	 * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
	 * @throws \Magento\Framework\Exception\FileSystemException
	 */
	public function execute()
	{
		$this->cron->execute();
//		$hookCol = $this->hookFactory->create()->getCollection();
//		foreach ($hookCol as $hook){
//			$this->cron->generate($hook);
//		}
	}

}
