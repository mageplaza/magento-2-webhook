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

namespace Mageplaza\Webhook\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;


class History extends AbstractModel
{
	/**
	 * Cache tag
	 *
	 * @var string
	 */
	const CACHE_TAG = 'mageplaza_webhook_history';

	/**
	 * Cache tag
	 *
	 * @var string
	 */
	protected $_cacheTag = 'mageplaza_webhook_history';

	/**
	 * Event prefix
	 *
	 * @var string
	 */
	protected $_eventPrefix = 'mageplaza_webhook_history';


	public function __construct(
		Context $context,
		Registry $registry,
		AbstractResource $resource = null,
		AbstractDb $resourceCollection = null,
		array $data = []
	)
	{


		parent::__construct($context, $registry, $resource, $resourceCollection, $data);
	}

	/**
	 * Initialize resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Mageplaza\Webhook\Model\ResourceModel\History');
	}

}
