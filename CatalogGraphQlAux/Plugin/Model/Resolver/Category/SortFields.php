<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQlAux\Plugin\Model\Resolver\Category;

use Magento\Eav\Model\Config;
use Magento\Framework\Exception\LocalizedException;

class SortFields
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @param Config $eavConfig
     */
    public function __construct(Config $eavConfig)
    {
        $this->eavConfig = $eavConfig;
    }

    /**
     * Replace sort option label with store label
     *
     * @param \Magento\CatalogGraphQl\Model\Resolver\Category\SortFields $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws LocalizedException
     */
    public function afterResolve(
        \Magento\CatalogGraphQl\Model\Resolver\Category\SortFields $subject,
        array $result
    ): array {
        if (isset($result['options']) && count($result['options']) > 0) {
            foreach ($result['options'] as &$option) {
                /** @var $attribute \Magento\Eav\Model\Entity\Attribute */
                $attribute = $this->eavConfig->getAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $option['value']
                );

                if ($attribute->getStoreLabel()) {
                    $option['label'] = $attribute->getStoreLabel();
                }
            }
        }

        return $result;
    }
}
