<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQlAux\Model\Resolver;

use Magento\Catalog\Helper\Output as OutputHelper;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\ObjectManager;
use Magento\EavGraphQlAux\Model\Resolver\Query\Attributes;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\EavGraphQlAux\Model\Resolver\DataProvider\AttributeMetadata;
use Magento\EavGraphQlAux\Model\Resolver\DataProvider\AttributeOptions;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * @inheritdoc
 */
class CustomAttributes implements ResolverInterface
{
    /**
     * @var OutputHelper
     */
    private $outputHelper;

    /**
     * @var string[]
     */
    private array $selectableTypes;

    /**
     * @var Uid
     */
    private $uidEncoder;

    /**
     * @var Attributes
     */
    private $attributes;

    /**
     * @var AttributeMetadata
     */
    private $metadataProvider;

    /**
     * @var AttributeOptions
     */
    private $attributeOptions;

    /**
     * @param Uid $uidEncoder
     * @param Attributes $attributes
     * @param AttributeMetadata $metadataProvider
     * @param AttributeOptions $attributeOptions
     * @param array|null $selectableTypes
     * @param OutputHelper|null $outputHelper
     */
    public function __construct(
        Uid $uidEncoder,
        Attributes $attributes,
        AttributeMetadata $metadataProvider,
        AttributeOptions $attributeOptions,
        array $selectableTypes = null,
        OutputHelper $outputHelper = null
    ) {
        $this->uidEncoder = $uidEncoder;
        $this->attributes = $attributes;
        $this->metadataProvider = $metadataProvider;
        $this->attributeOptions = $attributeOptions;
        $this->selectableTypes = $selectableTypes ?? ['SELECT'];
        $this->outputHelper = $outputHelper ?: ObjectManager::getInstance()
            ->get(outputHelper::class);
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();
        $storeId = (int)$store->getId();

        /** @var Product $product */
        $product = $value['model'];
        $attributesValuesByUid = $this->mapValuesToUid($product->getCustomAttributes());
        $attributes = $this->attributes->getAttributes(Product::ENTITY, array_keys($attributesValuesByUid));

        $items = [];
        foreach ($attributes as $attribute) {
            $attributeData['attribute_metadata'] = $this->metadataProvider->getAttributeMetadata(
                $attribute,
                $storeId,
                Product::ENTITY
            );
            $attributeValue = $attributesValuesByUid[$attributeData['attribute_metadata']['uid']];
            $attributeInputType = $attributeData['attribute_metadata']['ui_input']['ui_input_type'] ?? null;

            if ($attributeInputType && $this->isSelectable((string) $attributeInputType)) {
                $attributeData['entered_attribute_value'] = [];
                $attributeData['selected_attribute_options'] = [
                   'attribute_option' => $this->getSelectedOptions($attribute, (string) $attributeValue)
                ];
            } else {
                $attributeData['entered_attribute_value'] = [
                    'value' =>  $attribute->getIsHtmlAllowedOnFront() ?
                        $this->outputHelper->productAttribute(
                            $product,
                            $attributeValue,
                            $attribute->getAttributeCode()
                        ) :
                        $attributeValue
                ];
                $attributeData['selected_attribute_options'] = [];
            }

            $items[] = $attributeData;
        }

        usort($items, function (array $a, array $b) {
            $aPosition = $a['attribute_metadata']['sort_order'];
            $bPosition = $b['attribute_metadata']['sort_order'];

            // Sort alphabetically if same position
            if ($aPosition === $bPosition) {
                return strcmp(
                    $a['attribute_metadata']['label'],
                    $b['attribute_metadata']['label']
                );
            }

            // Sort by position
            return $aPosition <=> $bPosition;
        });

        return $items;
    }

    /**
     * Associate attributes values with UIDs
     *
     * @param array $customAttributes
     * @return array
     */
    private function mapValuesToUid(array $customAttributes): array
    {
        $customAttributesValuesByUid = [];
        foreach ($customAttributes as $customAttribute) {
            $customAttributesValuesByUid[
                $this->uidEncoder->encode(Product::ENTITY . '/' . $customAttribute->getAttributeCode())
            ] = $customAttribute->getValue();
        }
        return $customAttributesValuesByUid;
    }

    /**
     * Get selected option details
     *
     * @param AttributeInterface $attribute
     * @param string $attributeValue
     * @return array
     * @throws LocalizedException
     */
    private function getSelectedOptions(
        AttributeInterface $attribute,
        string $attributeValue
    ): array {
        $selectedOptionIds = explode(',', $attributeValue);

        return $this->attributeOptions->getAttributeOptions($attribute, $selectedOptionIds);
    }

    /**
     * Check if attribute value is selected
     *
     * @param string $uiInputType
     * @return bool
     */
    private function isSelectable(string $uiInputType): bool
    {
        return in_array($uiInputType, $this->selectableTypes);
    }
}
