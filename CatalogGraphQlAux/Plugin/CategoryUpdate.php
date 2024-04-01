<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQlAux\Plugin;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Formatter\LayerFormatter;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder\Category;
use Magento\Framework\GraphQl\Query\Uid;

class CategoryUpdate
{
    /** @var Uid */
     private Uid $uidEncoder;

    /**
     * @var LayerFormatter
     */
    private LayerFormatter $layerFormatter;

     /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @param LayerFormatter $layerFormatter
     * @param Uid $uidEncoder
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        LayerFormatter $layerFormatter,
        Uid $uidEncoder,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->layerFormatter = $layerFormatter;
        $this->uidEncoder = $uidEncoder;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Plugin to avoid disable categories from layered filter
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
        if(!empty($result)) {
            foreach ($result as $key => $option) {
                if ($option['attribute_code'] == 'category_uid') {
                    foreach ($option['options'] as $label => $value) {
                        $categoryId = $this->uidEncoder->decode($value['value']);
                        if(!$this->isActiveFilteredCategories($categoryId)){ 
                            unset($result[$key]['options'][$label]);
                        }
                    }
                }
            } 
            if(count($result[0]['options']) <= 0){
                return [];
            }
        }
        return $result;
    }

    protected function isActiveFilteredCategories($categoryId){
        $category = $this->categoryRepository->get($categoryId);
        return $category->getIsActive();
       
    }
}
