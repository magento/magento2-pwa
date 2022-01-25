<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\EavGraphQlAux;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Catalog\Model\GetCategoryByName;

class SortProductsByCustomAttribute extends GraphQlAbstract
{
    /** @var ObjectManager */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Test that product custom attribute basic metadata and selected option are returned
     *
     * @magentoApiDataFixture Magento/EavGraphQlAux/_files/category_with_three_products_with_attributes.php
     * @magentoApiDataFixture Magento/Indexer/_files/reindex_all_invalid.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSortByCustomAttribute()
    {
        /** @var GetCategoryByName $getCategoryByName */
        $getCategoryByName = $this->objectManager->create(GetCategoryByName::class);
        $categoryId = $getCategoryByName->execute('Category 999')->getId();

        // Sort by dropdown_attribute ASC
        $queryDropdownASC
            = <<<QUERY
{
  products(filter: {category_id: {eq: "$categoryId"}}, sort: {dropdown_attribute: ASC}) {
    items {
      sku
      name
    }
  }
}

QUERY;
        $resultDropdownASC = $this->graphQlQuery($queryDropdownASC);
        $this->assertArrayNotHasKey('errors', $resultDropdownASC);
        $dropdownASC = array_column($resultDropdownASC['products']['items'], 'name');
        $expectedDropdownASC = [
            'Dropdown: Option 2 / Text: NULL',
            'Dropdown: Option 3 / Text: Bb',
            'Dropdown: Option NULL / Text: Aa'
        ];
        $this->assertEquals($expectedDropdownASC, $dropdownASC, 'Sort by dropdown_attribute ASC');

        // Sort by dropdown_attribute DESC
        $queryDropdownDESC
            = <<<QUERY
{
  products(filter: {category_id: {eq: "$categoryId"}}, sort: {dropdown_attribute: DESC}) {
    items {
      sku
      name
    }
  }
}

QUERY;
        $resultDropdownDESC = $this->graphQlQuery($queryDropdownDESC);
        $this->assertArrayNotHasKey('errors', $resultDropdownDESC);
        $dropdownDESC = array_column($resultDropdownDESC['products']['items'], 'name');
        $expectedDropdownDESC = [
            'Dropdown: Option 3 / Text: Bb',
            'Dropdown: Option 2 / Text: NULL',
            'Dropdown: Option NULL / Text: Aa'
        ];
        $this->assertEquals($expectedDropdownDESC, $dropdownDESC, 'Sort by dropdown_attribute DESC');

        // Sort by varchar_attribute ASC
        $queryVarcharASC
            = <<<QUERY
{
  products(filter: {category_id: {eq: "$categoryId"}}, sort: {varchar_attribute: ASC}) {
    items {
      sku
      name
    }
  }
}

QUERY;
        $resultVarcharASC = $this->graphQlQuery($queryVarcharASC);
        $this->assertArrayNotHasKey('errors', $resultVarcharASC);
        $varcharASC = array_column($resultVarcharASC['products']['items'], 'name');
        $expectedVarcharASC = [
            'Dropdown: Option NULL / Text: Aa',
            'Dropdown: Option 3 / Text: Bb',
            'Dropdown: Option 2 / Text: NULL'
        ];
        $this->assertEquals($expectedVarcharASC, $varcharASC, 'Sort by varchar_attribute ASC');

        // Sort by varchar_attribute DESC
        $queryVarcharDESC
            = <<<QUERY
{
  products(filter: {category_id: {eq: "$categoryId"}}, sort: {varchar_attribute: DESC}) {
    items {
      sku
      name
    }
  }
}

QUERY;
        $resultVarcharDESC = $this->graphQlQuery($queryVarcharDESC);
        $this->assertArrayNotHasKey('errors', $resultVarcharDESC);
        $varcharDESC = array_column($resultVarcharDESC['products']['items'], 'name');
        $expectedVarcharDESC = [
            'Dropdown: Option 3 / Text: Bb',
            'Dropdown: Option NULL / Text: Aa',
            'Dropdown: Option 2 / Text: NULL'
        ];
        $this->assertEquals($expectedVarcharDESC, $varcharDESC, 'Sort by varchar_attribute DESC');

        // Sort by price ASC
        $queryPriceASC
            = <<<QUERY
{
  products(filter: {category_id: {eq: "$categoryId"}}, sort: {price: ASC}) {
    items {
      sku
      name
    }
  }
}

QUERY;
        $resultPriceASC = $this->graphQlQuery($queryPriceASC);
        $this->assertArrayNotHasKey('errors', $resultPriceASC);
        $priceASC = array_column($resultPriceASC['products']['items'], 'name');
        $expectedPriceASC = [
            'Dropdown: Option 2 / Text: NULL',
            'Dropdown: Option 3 / Text: Bb',
            'Dropdown: Option NULL / Text: Aa',
        ];
        $this->assertEquals($expectedPriceASC, $priceASC, 'Sort by Price ASC');
    }
}
