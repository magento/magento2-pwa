<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/category_with_three_products.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/dropdown_attribute.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_varchar_attribute.php');

$objectManager = Bootstrap::getObjectManager();

/** @var Config $eavConfig */
$eavConfig = $objectManager->get(Config::class);
$dropdownAttribute = $eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'dropdown_attribute');
$dropdownAttribute
    ->setIsSearchable('1')
    ->setUsedForSortBy('1')
    ->save();
/** @var $options Collection */
$options = $objectManager->create(Collection::class);
$options->setAttributeFilter($dropdownAttribute->getId());
$optionIds = $options->getAllIds();

$varcharAttribute = $eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'varchar_attribute');
$varcharAttribute
    ->setIsSearchable('1')
    ->setUsedForSortBy('1')
    ->save();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
$searchCriteriaBuilder->addFilter(ProductInterface::SKU, ['simple1000','simple1001','simple1002'], 'in');
$products = $productRepository->getList($searchCriteriaBuilder->create());

foreach ($products->getItems() as $product) {
    if ($product->getSku() === 'simple1000') {
        $product->setName('Dropdown: Option 2 / Text: NULL');
        $product->setDropdownAttribute($optionIds[1]);
    }
    if ($product->getSku() === 'simple1001') {
        $product->setName('Dropdown: Option NULL / Text: Aa');
        $product->setVarcharAttribute('Aa');
    }
    if ($product->getSku() === 'simple1002') {
        $product->setName('Dropdown: Option 3 / Text: Bb');
        $product->setDropdownAttribute($optionIds[2]);
        $product->setVarcharAttribute('Bb');
    }
    $productRepository->save($product);
}
