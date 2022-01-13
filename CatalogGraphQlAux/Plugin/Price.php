<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQlAux\Plugin;

class Price
{
    /**
     * @var string
     * @see \Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder\Price::PRICE_BUCKET
     */
    private const PRICE_BUCKET = 'price_bucket';

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(\Magento\Eav\Model\Config $eavConfig)
    {
        $this->eavConfig = $eavConfig;
    }

    /**
     * Plugin to add position to price attribute
     *
     * @param \Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder\Price $subject
     * @param array $result
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterBuild(
        \Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder\Price $subject,
        array $result
    ): array {
        if (array_key_exists(self::PRICE_BUCKET, $result)) {
            $attributeCode = $result[self::PRICE_BUCKET]['attribute_code'];
            $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
            $result[self::PRICE_BUCKET]['position'] = $attribute->getPosition();
        }

        return $result;
    }
}
