<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQlAux\Plugin\Query;

use Magento\CatalogGraphQl\Model\Resolver\Products\Attributes\Collection;

/**
 * Plugin to add all custom attributes to product
 */
class FieldSelection
{
    /**
     * @var Collection
     */
    private $customAttributesCollection;

    /**
     * @param Collection $customAttributesCollection
     */
    public function __construct(Collection $customAttributesCollection)
    {
        $this->customAttributesCollection = $customAttributesCollection;
    }

    /**
     * Get requested fields from products query
     *
     * @param \Magento\CatalogGraphQl\Model\Resolver\Products\Query\FieldSelection $subject
     * @param string[] $result
     * @return string[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetProductsFieldSelection(
        \Magento\CatalogGraphQl\Model\Resolver\Products\Query\FieldSelection $subject,
        array $result
    ): array {
        $customAttributeIndex = array_search('custom_attributes', $result);
        if ($customAttributeIndex) {
            $attributeCodes = [];
            foreach ($this->customAttributesCollection->getAttributes() as $customAttribute) {
                $attributeCodes[] = $customAttribute->getAttributeCode();
            }
            array_splice($result, $customAttributeIndex, 1);
            array_push($result, ...$attributeCodes);
        }

        return array_unique($result);
    }
}
