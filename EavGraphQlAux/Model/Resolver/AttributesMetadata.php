<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQlAux\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\Enum\DataMapperInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\EavGraphQlAux\Model\Resolver\Query\Attributes;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\EavGraphQl\Model\Resolver\Query\Type;

/**
 * @inheritdoc
 */
class AttributesMetadata implements ResolverInterface
{
    const COMPLEX_DATA_TYPE = 'COMPLEX';

    /**
     * @var Attributes
     */
    private $attributes;

    /**
     * @var DataMapperInterface
     */
    private $enumDataMapper;

    /** @var Uid */
    private $uidEncoder;

    /**
     * @var EnumLookup
     */
    private $enumLookup;

    /**
     * @var Type
     */
    private $type;

    /**
     * @param Attributes $attributes
     * @param DataMapperInterface $enumDataMapper
     * @param Uid $uidEncoder
     * @param EnumLookup $enumLookup
     * @param Type $type
     */
    public function __construct(
        Attributes $attributes,
        DataMapperInterface $enumDataMapper,
        Uid $uidEncoder,
        EnumLookup $enumLookup,
        Type $type
    ) {
        $this->attributes = $attributes;
        $this->enumDataMapper = $enumDataMapper;
        $this->uidEncoder = $uidEncoder;
        $this->enumLookup = $enumLookup;
        $this->type = $type;
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

        if (empty($args['entityType'])) {
            throw new GraphQlInputException(__('Required parameter "entityType" is missing'));
        }

        $items = [];
        $entityType = $this->getEntityType($args['entityType']);

        $attributes = $this->attributes->getAttributes(
            $entityType,
            $args['attributeUids'] ?? [],
            $args['showSystemAttributes'] ?? false
        );

        foreach ($attributes as $attribute) {
            if (!$attribute->getFrontendInput()) {
                continue;
            }
            $attributeType = $this->type->getType(
                $attribute->getAttributeCode(),
                $attribute->getEntityType()->getEntityTypeCode()
            );
            $dataType = $this->enumLookup->getEnumValueFromField('ObjectDataTypeEnum', $attributeType);

            $items[] = [
                'uid' => $this->uidEncoder->encode('catalog_product' . '/' . $attribute->getAttributeCode()),
                'code' => $attribute->getAttributeCode(),
                'label' => $attribute->getFrontendLabel(),
                'attribute_labels' => $attribute->getFrontendLabels(),
                'data_type' => $dataType !== '' ? $dataType : self::COMPLEX_DATA_TYPE,
                'sort_order' => $attribute->getPosition(),
                'is_system' => !$attribute->getIsUserDefined(),
                'entity_type' => $this->enumLookup->getEnumValueFromField(
                    'AttributeEntityTypeEnum',
                    $attribute->getEntityType()->getEntityTypeCode()
                ),
                'ui_input' => [
                    'ui_input_type' => $this->enumLookup->getEnumValueFromField(
                        'UiInputTypeEnum',
                        $attribute->getFrontendInput()
                    ),
                    'is_html_allowed' => $attribute->getIsHtmlAllowedOnFront(),
                    'attribute_options' => $attribute->getOptions(),
                    'attribute' => $attribute
                ],
                'entity_type_code' => $attribute->getEntityType()->getEntityTypeCode(),
                'attribute' => $attribute
            ];
        }
        return [
            'items' => $items
        ];
    }

    /**
     * Get mapped entity type
     *
     * @param string $entityType
     * @return string|null
     */
    private function getEntityType(string $entityType): ?string
    {
        $entityTypeEnums = $this->enumDataMapper->getMappedEnums('AttributeEntityTypeEnum');

        return $entityTypeEnums[strtolower($entityType)] ?? null;
    }
}
