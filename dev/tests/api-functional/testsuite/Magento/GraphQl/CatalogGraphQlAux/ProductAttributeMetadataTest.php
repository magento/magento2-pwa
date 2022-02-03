<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogGraphQlAux;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\FrontendLabel;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class ProductAttributeMetadataTest extends GraphQlAbstract
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
     * Verify the schema returns correct list of attributes metadata for given entityType
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAttributesMetadataQuery()
    {
        $query
            = <<<QUERY
{
  attributesMetadata(
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
        is_html_allowed
      }
      ... on ProductAttributeMetadata {
        used_in_components
      }
    }
  }
 }
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $response);
        $expectedAttributesMetadata = [
            'cost' => [
                "__typename" => 'ProductAttributeMetadata',
                "uid" => $this->uid->encode('catalog_product/cost'),
                "code" => 'cost',
                "label" => 'Cost',
                "data_type" => 'FLOAT',
                "entity_type" => "PRODUCT",
                "is_system" => false,
                "ui_input" => [
                    "ui_input_type"=> 'PRICE',
                    "is_html_allowed"=> false,
                    "__typename" => 'UiAttributeTypeAny'
                ],
                "used_in_components" => []
            ],
            'manufacturer' => [
                "__typename" => 'ProductAttributeMetadata',
                "uid" => $this->uid->encode('catalog_product/manufacturer'),
                "code" => 'manufacturer',
                "label" => 'Manufacturer',
                "data_type" => 'INT',
                "entity_type" => "PRODUCT",
                "is_system" => false,
                "ui_input" => [
                    "ui_input_type"=> 'SELECT',
                    "is_html_allowed"=> false,
                    "__typename" => 'UiAttributeTypeSelect'
                ],
                "used_in_components" => ['PRODUCTS_COMPARE', 'PRODUCT_FILTER', 'ADVANCED_CATALOG_SEARCH']
            ],
            'color' => [
                "__typename" => 'ProductAttributeMetadata',
                "uid" => $this->uid->encode('catalog_product/color'),
                "code" => 'color',
                "label" => 'Color',
                "data_type" => 'INT',
                "entity_type" => "PRODUCT",
                "is_system" => false,
                "ui_input" => [
                    "ui_input_type"=> 'SELECT',
                    "is_html_allowed"=> false,
                    "__typename" => 'UiAttributeTypeSelect'
                ],
                "used_in_components" => ['PRODUCTS_COMPARE', 'PRODUCT_FILTER', 'ADVANCED_CATALOG_SEARCH']
            ],
        ];
        $attributes = [];
        foreach ($response['attributesMetadata']['items'] as $item) {
            if (isset($expectedAttributesMetadata[$item['code']])) {
                $this->assertResponseFields(
                    $item,
                    $expectedAttributesMetadata[$item['code']]
                );
                $attributes[] = $item['code'];
            }
        }
        $this->assertEqualsCanonicalizing(array_keys($expectedAttributesMetadata), $attributes);
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
  attributesMetadata(
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
        is_html_allowed
      }
      ... on ProductAttributeMetadata {
        used_in_components
      }
    }
  }
 }
QUERY;
        $response = $this->graphQlQuery($query);
        $expectedAttributeUids = [
            $this->uid->encode('catalog_product/manufacturer'),
        ];
        $expectedAttributeCodes = [
            'manufacturer'
        ];
        $expectedLabels = [
            "Manufacturer",
        ];
        $expectedDataTypes = ['INT'];
        $expectedInputTypes = ['SELECT'];
        $expectedIsHtmlTypes = [false];
        $expectedInputTypesTypenames = ['UiAttributeTypeSelect'];
        $expectedUseInComponents = [
            ['PRODUCTS_COMPARE', 'PRODUCT_FILTER', 'ADVANCED_CATALOG_SEARCH']
        ];
        $expectedIsSystem = [false];

        $this->assertAttributeType(
            $expectedAttributeUids,
            $expectedAttributeCodes,
            $expectedLabels,
            $expectedDataTypes,
            $expectedInputTypes,
            $expectedIsHtmlTypes,
            $expectedInputTypesTypenames,
            $expectedUseInComponents,
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
  attributesMetadata(
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
        is_html_allowed
      }
      ... on ProductAttributeMetadata {
        used_in_components
      }
    }
  }
 }
QUERY;
        $response = $this->graphQlQuery($query);
        $expectedAttributeUids = [
            $this->uid->encode('catalog_product/description'),
        ];
        $expectedAttributeCodes = [
            'description'
        ];
        $expectedLabels = [
            "Description",
        ];
        $expectedDataTypes = ['STRING'];
        $expectedInputTypes = ['TEXTAREA'];
        $expectedIsHtmlTypes = [true];
        $expectedInputTypesTypenames = ['UiAttributeTypeAny'];
        $expectedUseInComponents = [
            ['PRODUCTS_COMPARE', 'ADVANCED_CATALOG_SEARCH']
        ];
        $expectedIsSystem = [true];

        $this->assertAttributeType(
            $expectedAttributeUids,
            $expectedAttributeCodes,
            $expectedLabels,
            $expectedDataTypes,
            $expectedInputTypes,
            $expectedIsHtmlTypes,
            $expectedInputTypesTypenames,
            $expectedUseInComponents,
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
     * @param $expectedIsHtmlTypes
     * @param $expectedInputTypesTypenames
     * @param $expectedUseInComponents
     * @param $expectedIsSystem
     * @param $actualResponse
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    private function assertAttributeType(
        $expectedAttributeUids,
        $expectedAttributeCodes,
        $expectedLabels,
        $expectedDataTypes,
        $expectedInputTypes,
        $expectedIsHtmlTypes,
        $expectedInputTypesTypenames,
        $expectedUseInComponents,
        $expectedIsSystem,
        $actualResponse
    ) {
        $attributeMetaDataItems = array_map(
            null,
            $actualResponse['attributesMetadata']['items'],
            $expectedDataTypes
        );

        foreach ($attributeMetaDataItems as $itemIndex => $itemArray) {
            $this->assertResponseFields(
                $itemArray[0],
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
                        "is_html_allowed"=> $expectedIsHtmlTypes[$itemIndex],
                        "__typename" => $expectedInputTypesTypenames[$itemIndex]
                    ],
                    "used_in_components" => $expectedUseInComponents[$itemIndex]
                ]
            );
        }
    }

    /**
     * Test that custom attribute options and labels are returned correctly
     *
     * @magentoApiDataFixture Magento/Catalog/_files/dropdown_attribute.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCustomAttributeMetadataOptions()
    {
        /** @var AttributeRepositoryInterface $attributeRepository */
        $attributeRepository = $this->objectManager->create(AttributeRepositoryInterface::class);

        /** @var Config $eavConfig */
        $eavConfig = $this->objectManager->get(Config::class);
        $attribute = $eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'dropdown_attribute');

        // Add frontend label to dropdown_attribute:
        $frontendLabelAttribute = $this->objectManager->get(FrontendLabel::class);
        $frontendLabelAttribute->setStoreId(1);
        $frontendLabelAttribute->setLabel('Default Store View label');
        $frontendLabels = $attribute->getFrontendLabels();
        $frontendLabels[] = $frontendLabelAttribute;
        $attribute->setFrontendLabels($frontendLabels);

        /** @var AttributeOptionInterface[] $options */
        $options = $attribute->getOptions();
        array_shift($options);
        $optionValues = [];
        // phpcs:ignore Generic.CodeAnalysis.ForLoopWithTestFunctionCall
        for ($i = 0; $i < count($options); $i++) {
            $optionValues[] = $options[$i]->getValue();
            if ($i === 1) {
                $attribute->setDefaultValue($options[$i]->getValue());
            }
        }
        $attributeRepository->save($attribute);

        $query
            = <<<QUERY
{
  attributesMetadata(
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
            is_default
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
                    'is_default' => false
                ],
                [
                    '__typename' => 'AttributeOption',
                    'uid' => $this->uid->encode('catalog_product/dropdown_attribute/' . $optionValues[1]),
                    'label' => 'Option 2',
                    'is_default' => true
                ],
                [
                    '__typename' => 'AttributeOption',
                    'uid' => $this->uid->encode('catalog_product/dropdown_attribute/' . $optionValues[2]),
                    'label' => 'Option 3',
                    'is_default' => false
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

        $actualAttributes = $response['attributesMetadata']['items'];

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
