<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQlAux\Plugin;

class Aggregations
{
    /**
     * @var string
     */
    private const CATEGORY_CODE = 'category_id';

    /**
     * Plugin to reorder attributes
     *
     * @param \Magento\CatalogGraphQl\Model\Resolver\Aggregations $subject
     * @param array|null $result
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterResolve(
        \Magento\CatalogGraphQl\Model\Resolver\Aggregations $subject,
        ?array $result
    ): ?array {
        if (is_array($result)) {
            usort($result, function (array $a, array $b) {
                // Place Category filter first
                if ($a['attribute_code'] === self::CATEGORY_CODE) {
                    return -1;
                }
                if ($b['attribute_code'] === self::CATEGORY_CODE) {
                    return 1;
                }

                // Sort alphabetically if same position
                if ($a['position'] === $b['position']) {
                    return strcmp($a['label'], $b['label']);
                }

                // Sort by position
                return $a['position'] <=> $b['position'];
            });
        }

        return $result;
    }
}
