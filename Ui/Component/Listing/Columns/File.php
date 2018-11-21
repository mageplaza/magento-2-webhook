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
 * @package     Mageplaza_GiftCard
 * @copyright   Copyright (c) 2018 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ProductFeed\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class CommentContent
 * @package Mageplaza\Blog\Ui\Component\Listing\Columns
 */
class File extends Column
{
	protected $helperData;

	public function __construct(
		ContextInterface $context,
		UiComponentFactory $uiComponentFactory,
		\Mageplaza\ProductFeed\Helper\Data $helperData,
		array $components = [],
		array $data = [])
	{
		parent::__construct($context, $uiComponentFactory, $components, $data);

		$this->helperData = $helperData;
	}

	/**
	 * Prepare Data Source
	 *
	 * @param array $dataSource
	 * @return array
	 */
	public function prepareDataSource(array $dataSource)
	{
		if (isset($dataSource['data']['items'])) {
			foreach ($dataSource['data']['items'] as &$item) {
				if (isset($item[$this->getData('name')])) {
					if ($item['last_generated']) {
						$fileType = $item['file_type'];
						$file_name = $item[$this->getData('name')] . '.' . $fileType;

						$item[$this->getData('name')] = '<a href="' . $this->helperData->getFileUrl($file_name) . '" target="_blank">' . $file_name . '</a>';
					} else {
						$item[$this->getData('name')] = 'none';
					}
				}
			}
		}
		return $dataSource;
	}
}
