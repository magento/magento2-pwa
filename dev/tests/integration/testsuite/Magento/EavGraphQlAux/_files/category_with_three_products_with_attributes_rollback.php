<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$productRepository = $objectManager->get(ProductRepositoryInterface::class);
try {
    $productRepository->deleteById('simple1002');
} catch (NoSuchEntityException $e) {
    //Already deleted.
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/category_with_three_products_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/dropdown_attribute_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_varchar_attribute_rollback.php');
