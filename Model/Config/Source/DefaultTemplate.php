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

namespace Mageplaza\ProductFeed\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class SideBarLR
 * @package Mageplaza\Blog\Model\Config\Source
 */
class DefaultTemplate implements ArrayInterface
{
    private $defaultTemplateFactory;

    public function __construct(
        \Mageplaza\ProductFeed\Model\DefaultTemplateFactory $defaultTemplateFactory
    )
    {
        $this->defaultTemplateFactory = $defaultTemplateFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->toArray() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $templateCollection = $this->defaultTemplateFactory->create()->getCollection();
        $array = [];
        foreach ($templateCollection as $template) {
            $array[$template->getName()] =  $template->getTitle();
        }

        return $array;
    }
}
