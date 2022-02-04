<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogGraphQlAux;

use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class CustomProductAttributesTest extends GraphQlAbstract
{
    /** @var ObjectManager */
    private $objectManager;

    /** @var Uid */
    private $uid;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->uid = $this->objectManager->get(Uid::class);
    }

    /**
     * Test that product custom attribute basic metadata and selected option are returned
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_dropdown_attribute.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testProductWithDropDownAttribute()
    {
        /** @var Config $eavConfig */
        $eavConfig = $this->objectManager->get(Config::class);
        $attribute = $eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'dropdown_attribute');

        /** @var AttributeOptionInterface[] $options */
        $options = $attribute->getOptions();
        array_shift($options);
        $optionValues = [];
        // phpcs:ignore Generic.CodeAnalysis.ForLoopWithTestFunctionCall
        for ($i = 0; $i < count($options); $i++) {
            $optionValues[] = $options[$i]->getValue();
        }

        $productSku = 'simple_op_1';
        $query
            = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
    {
        items {
            sku
            custom_attributes {
                attribute_metadata {
                    uid
                    ui_input {
                        ... on SelectableInputTypeInterface {
                            attribute_options {
                                __typename
                                label
                                uid
                                ... on AttributeOption {
                                    value
                                }
                            }
                        }
                    }
                }
                entered_attribute_value {
                    __typename
                    value
                }
                selected_attribute_options {
                    __typename
                    attribute_option {
                        __typename
                        label
                        uid
                        ... on AttributeOption {
                            value
                        }
                    }
                }
            }
        }
    }
}

QUERY;
        $response = $this->graphQlQuery($query);

        $expectedOptionArray = [
            [
                [
                    '__typename' => 'AttributeOption',
                    'uid' => $this->uid->encode('catalog_product/dropdown_attribute/' . $optionValues[0]),
                    'label' => 'Option 1',
                    'value' => $optionValues[0]
                ],
                [
                    '__typename' => 'AttributeOption',
                    'uid' => $this->uid->encode('catalog_product/dropdown_attribute/' . $optionValues[1]),
                    'label' => 'Option 2',
                    'value' => $optionValues[1]
                ],
                [
                    '__typename' => 'AttributeOption',
                    'uid' => $this->uid->encode('catalog_product/dropdown_attribute/' . $optionValues[2]),
                    'label' => 'Option 3',
                    'value' => $optionValues[2]
                ]
            ]
        ];

        $actualAttributes = $response['products']['items'][0]['custom_attributes'][0];

        foreach ($expectedOptionArray as $expectedOptions) {
            $actualOption = $actualAttributes['attribute_metadata']['ui_input']['attribute_options'];
            $this->assertEquals($expectedOptions, $actualOption);
        }

        $this->assertEquals(null, $actualAttributes['entered_attribute_value']['value']);
        $this->assertEquals(
            'SelectedAttributeOption',
            $actualAttributes['selected_attribute_options']['__typename']
        );
        $this->assertEquals(
            $expectedOptionArray[0][0],
            $actualAttributes['selected_attribute_options']['attribute_option'][0]
        );
    }

    /**
     * Test that product custom attribute basic metadata and entered value are returned
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_custom_attribute.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testProductWithDateAttribute()
    {
        /** @var Config $eavConfig */

        $productSku = 'simple';
        $query
            = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
    {
        items {
            sku
            custom_attributes {
                attribute_metadata {
                    uid
                    ui_input {
                        ui_input_type
                    }
                }
                entered_attribute_value {
                    __typename
                    value
                }
                selected_attribute_options {
                    __typename
                }
            }
        }
    }
}

QUERY;
        $response = $this->graphQlQuery($query);

        $actualAttributes = $response['products']['items'][0]['custom_attributes'][0];

        $this->assertEquals('TEXT', $actualAttributes['attribute_metadata']['ui_input']['ui_input_type']);
        $this->assertEquals(
            ['__typename' => 'SelectedAttributeOption'],
            $actualAttributes['selected_attribute_options']
        );
        $this->assertEquals(
            'EnteredAttributeValue',
            $actualAttributes['entered_attribute_value']['__typename']
        );
        $this->assertEquals(
            'customAttributeValue',
            $actualAttributes['entered_attribute_value']['value']
        );
    }
}
