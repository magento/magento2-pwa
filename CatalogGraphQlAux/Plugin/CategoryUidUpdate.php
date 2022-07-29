<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQlAux\Plugin;

use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Formatter\LayerFormatter;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder\Category;
use Magento\Framework\GraphQl\Query\Uid;

class CategoryUidUpdate
{
    /** @var Uid */
     private Uid $uidEncoder;

    /**
     * @var LayerFormatter
     */
    private LayerFormatter $layerFormatter;

    /**
     * @param LayerFormatter $layerFormatter
     * @param Uid $uidEncoder
     */
    public function __construct(
        LayerFormatter $layerFormatter,
        Uid $uidEncoder
    ) {
        $this->layerFormatter = $layerFormatter;
        $this->uidEncoder = $uidEncoder;
    }

    /**
     * Plugin to update the category_id in value into category_uid
     *
     * @param Category $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterBuild(
        Category $subject,
        array $result
    ): array {
       foreach ($result as $key => $option) {
           if ($option['attribute_code'] == 'category_uid') {
                foreach ($option['options'] as $label => $value) {
                    $result[$key]['options'][$label]['value'] = $this->uidEncoder->encode((string) $value['value']);
                }
           }
        }

        return $result;
    }
}