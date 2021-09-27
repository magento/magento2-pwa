<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQlPwa\Test\GraphQl;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\FrontendLabel;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class ProductAttributeMetadataTest extends GraphQlAbstract
{
    /**
     * Verify the schema returns correct list of attributes metadata for given entityType
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAttributesMetadataQuery()
    {
        $query
            = <<<QUERY
{
  customAttributeMetadataV2(
    entityType: PRODUCT
  )
  {
    items
    {
      __typename
      uid
      code
      label
      attribute_labels {
        store_code
        label
      }
      data_type
      sort_order
      entity_type
      is_system
      ui_input {
        __typename
        ui_input_type
      }
      ... on ProductAttributeMetadata {
        lists_to_use_in
      }
    }
  }
 }
QUERY;
        $response = $this->graphQlQuery($query);
        $expectedAttributeUids = [
            base64_encode('catalog_product/cost'),
            base64_encode('catalog_product/manufacturer'),
            base64_encode('catalog_product/color')
        ];
        $expectedAttributeCodes = [
            'cost',
            'manufacturer',
            'color'
        ];
        $expectedLabels = [
            "Cost",
            "Manufacturer",
            "Color"
        ];
        $expectedDataTypes = ['FLOAT', 'INT', 'INT'];
        $expectedInputTypes = ['PRICE', 'SELECT', 'SELECT'];
        $expectedInputTypesTypenames = ['UiAttributeTypeAny', 'UiAttributeTypeSelect', 'UiAttributeTypeSelect'];
        $expectedLists = [
            [],
            ['PRODUCTS_COMPARE', 'PRODUCT_FILTER', 'ADVANCED_CATALOG_SEARCH'],
            ['PRODUCTS_COMPARE', 'PRODUCT_FILTER', 'ADVANCED_CATALOG_SEARCH']
        ];
        $expectedIsSystem = [false, false, false];

        $this->assertAttributeType(
            $expectedAttributeUids,
            $expectedAttributeCodes,
            $expectedLabels,
            $expectedDataTypes,
            $expectedInputTypes,
            $expectedInputTypesTypenames,
            $expectedLists,
            $expectedIsSystem,
            $response
        );
    }

    /**
     * Verify the schema returns correct list of attributes metadata for given entityType and Uid
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAttributesMetadataQueryForUid()
    {
        $query
            = <<<QUERY
{
  customAttributeMetadataV2(
    entityType: PRODUCT,
    attributeUids: ["Y2F0YWxvZ19wcm9kdWN0L21hbnVmYWN0dXJlcg=="]
  )
  {
    items
    {
      __typename
      uid
      code
      label
      attribute_labels {
        store_code
        label
      }
      data_type
      sort_order
      entity_type
      is_system
      ui_input {
        __typename
        ui_input_type
      }
      ... on ProductAttributeMetadata {
        lists_to_use_in
      }
    }
  }
 }
QUERY;
        $response = $this->graphQlQuery($query);
        $expectedAttributeUids = [
            base64_encode('catalog_product/manufacturer'),
        ];
        $expectedAttributeCodes = [
            'manufacturer'
        ];
        $expectedLabels = [
            "Manufacturer",
        ];
        $expectedDataTypes = ['INT'];
        $expectedInputTypes = ['SELECT'];
        $expectedInputTypesTypenames = ['UiAttributeTypeSelect'];
        $expectedLists = [
            ['PRODUCTS_COMPARE', 'PRODUCT_FILTER', 'ADVANCED_CATALOG_SEARCH']
        ];
        $expectedIsSystem = [false];

        $this->assertAttributeType(
            $expectedAttributeUids,
            $expectedAttributeCodes,
            $expectedLabels,
            $expectedDataTypes,
            $expectedInputTypes,
            $expectedInputTypesTypenames,
            $expectedLists,
            $expectedIsSystem,
            $response
        );
    }

    /**
     * Verify the schema returns correct list of attributes metadata for given entityType and Uid for system attribute
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAttributesMetadataQueryForSystemAttribute()
    {
        $query
            = <<<QUERY
{
  customAttributeMetadataV2(
    entityType: PRODUCT,
    attributeUids: ["Y2F0YWxvZ19wcm9kdWN0L2Rlc2NyaXB0aW9u"],
    showSystemAttributes: true
  )
  {
    items
    {
      __typename
      uid
      code
      label
      attribute_labels {
        store_code
        label
      }
      data_type
      sort_order
      entity_type
      is_system
      ui_input {
        __typename
        ui_input_type
      }
      ... on ProductAttributeMetadata {
        lists_to_use_in
      }
    }
  }
 }
QUERY;
        $response = $this->graphQlQuery($query);
        $expectedAttributeUids = [
            base64_encode('catalog_product/description'),
        ];
        $expectedAttributeCodes = [
            'description'
        ];
        $expectedLabels = [
            "Description",
        ];
        $expectedDataTypes = ['STRING'];
        $expectedInputTypes = ['TEXTAREA'];
        $expectedInputTypesTypenames = ['UiAttributeTypeAny'];
        $expectedLists = [
            ['PRODUCTS_COMPARE', 'ADVANCED_CATALOG_SEARCH']
        ];
        $expectedIsSystem = [true];

        $this->assertAttributeType(
            $expectedAttributeUids,
            $expectedAttributeCodes,
            $expectedLabels,
            $expectedDataTypes,
            $expectedInputTypes,
            $expectedInputTypesTypenames,
            $expectedLists,
            $expectedIsSystem,
            $response
        );
    }

    /**
     * @param $expectedAttributeUids
     * @param $expectedAttributeCodes
     * @param $expectedLabels
     * @param $expectedDataTypes
     * @param $expectedInputTypes
     * @param $expectedInputTypesTypenames
     * @param $expectedLists
     * @param $expectedIsSystem
     * @param $actualResponse
     */
    private function assertAttributeType(
        $expectedAttributeUids,
        $expectedAttributeCodes,
        $expectedLabels,
        $expectedDataTypes,
        $expectedInputTypes,
        $expectedInputTypesTypenames,
        $expectedLists,
        $expectedIsSystem,
        $actualResponse
    ) {
        $attributeMetaDataItems = array_map(null, $actualResponse['customAttributeMetadataV2']['items'], $expectedDataTypes);

        foreach ($attributeMetaDataItems as $itemIndex => $itemArray) {
            $this->assertResponseFields(
                $attributeMetaDataItems[$itemIndex][0],
                [
                    "__typename" => 'ProductAttributeMetadata',
                    "uid" => $expectedAttributeUids[$itemIndex],
                    "code" => $expectedAttributeCodes[$itemIndex],
                    "label" => $expectedLabels[$itemIndex],
                    "data_type" => $expectedDataTypes[$itemIndex],
                    "entity_type" => "PRODUCT",
                    "is_system" => $expectedIsSystem[$itemIndex],
                    "ui_input" => [
                        "ui_input_type"=> $expectedInputTypes[$itemIndex],
                        "__typename" => $expectedInputTypesTypenames[$itemIndex]
                    ],
                    "lists_to_use_in" => $expectedLists[$itemIndex]
                ]
            );
        }
    }

    /**
     * Test that custom attribute options and labels are returned correctly
     *
     * @magentoApiDataFixture Magento/Catalog/_files/dropdown_attribute.php
     */
    public function testCustomAttributeMetadataOptions()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var AttributeRepositoryInterface $attributeRepository */
        $attributeRepository = $objectManager->create(AttributeRepositoryInterface::class);

        /** @var Config $eavConfig */
        $eavConfig = $objectManager->get(Config::class);
        $attribute = $eavConfig->getAttribute('catalog_product', 'dropdown_attribute');

        // Add frontend label to dropdown_attribute:
        $frontendLabelAttribute = $objectManager->get(FrontendLabel::class);
        $frontendLabelAttribute->setStoreId(1);
        $frontendLabelAttribute->setLabel('Default Store View label');
        $frontendLabels = $attribute->getFrontendLabels();
        $frontendLabels[] = $frontendLabelAttribute;
        $attribute->setFrontendLabels($frontendLabels);
        $attributeRepository->save($attribute);

        /** @var AttributeOptionInterface[] $options */
        $options = $attribute->getOptions();
        array_shift($options);
        $optionValues = [];
        // phpcs:ignore Generic.CodeAnalysis.ForLoopWithTestFunctionCall
        for ($i = 0; $i < count($options); $i++) {
            $optionValues[] = $options[$i]->getValue();
        }

        $query
            = <<<QUERY
{
  customAttributeMetadataV2(
    entityType: PRODUCT,
    attributeUids: ["Y2F0YWxvZ19wcm9kdWN0L2Ryb3Bkb3duX2F0dHJpYnV0ZQ=="]
  )
  {
    items
    {
      attribute_labels {
        store_code
        label
      }
      ui_input {
        ... on AttributeOptionsInterface {
          attribute_options {
            __typename
            uid
            label
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
                    'uid' => base64_encode('catalog_product/dropdown_attribute/' . $optionValues[0]),
                    'label' => 'Option 1'
                ],
                [
                    '__typename' => 'AttributeOption',
                    'uid' => base64_encode('catalog_product/dropdown_attribute/' . $optionValues[1]),
                    'label' => 'Option 2'
                ],
                [
                    '__typename' => 'AttributeOption',
                    'uid' => base64_encode('catalog_product/dropdown_attribute/' . $optionValues[2]),
                    'label' => 'Option 3'
                ]
            ]
        ];
        $expectedLabelsArray = [
            [
                [
                    'store_code' => 'default',
                    'label' => 'Default Store View label'
                ]
            ]
        ];

        $actualAttributes = $response['customAttributeMetadataV2']['items'];

        foreach ($expectedOptionArray as $index => $expectedOptions) {
            $actualOption = $actualAttributes[$index]['ui_input']['attribute_options'];
            $this->assertEquals($expectedOptions, $actualOption);
        }
        foreach ($expectedLabelsArray as $index => $expectedLabels) {
            $actualLabels = $actualAttributes[$index]['attribute_labels'];
            $this->assertEquals($expectedLabels, $actualLabels);
        }
    }
}
