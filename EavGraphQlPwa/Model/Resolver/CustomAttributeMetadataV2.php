<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQlPwa\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\Enum\DataMapperInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\EavGraphQlPwa\Model\Resolver\Query\Attributes;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\EavGraphQl\Model\Resolver\Query\Type;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * @inheritdoc
 */
class CustomAttributeMetadataV2 implements ResolverInterface
{
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
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @param Attributes $attributes
     * @param DataMapperInterface $enumDataMapper
     */
    public function __construct(
        Attributes $attributes,
        DataMapperInterface $enumDataMapper,
        Uid $uidEncoder,
        EnumLookup $enumLookup,
        Type $type,
        StoreRepositoryInterface $storeRepository
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
            $type = $this->type->getType(
                $attribute->getAttributeCode(),
                $attribute->getEntityType()->getEntityTypeCode()
            );
            if (!$attribute->getFrontendInput()) {
                continue;
            }

            $items[] = [
                'uid' => $this->uidEncoder->encode('catalog_product' . '/' . $attribute->getAttributeCode()),
                'code' => $attribute->getAttributeCode(),
                'label' => $attribute->getFrontendLabel(),
                'attribute_labels' => $attribute->getFrontendLabels(),
                'data_type' => $this->enumLookup->getEnumValueFromField('ObjectDataTypeEnum', $type),
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
                    'attribute_options' => $attribute->getOptions(),
                    'entity_type' => $attribute->getEntityType()->getEntityTypeCode(),
                    'attribute_code' => $attribute->getAttributeCode()
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
     * @param string $entityType
     * @return string|null
     */
    private function getEntityType(string $entityType): ?string
    {
        $entityTypeEnums = $this->enumDataMapper->getMappedEnums('AttributeEntityTypeEnum');

        return $entityTypeEnums[strtolower($entityType)] ?? null;
    }
}
