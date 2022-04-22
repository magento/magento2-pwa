<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQlAux\Model\Resolver\DataProvider;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\EavGraphQl\Model\Resolver\Query\Type;
use Magento\Eav\Api\Data\AttributeInterface;

/**
 * @inheritdoc
 */
class AttributeMetadata
{
    /**
     * Complex Data Type
     */
    private const COMPLEX_DATA_TYPE = 'COMPLEX';

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
     * @param Uid $uidEncoder
     * @param EnumLookup $enumLookup
     * @param Type $type
     */
    public function __construct(
        Uid $uidEncoder,
        EnumLookup $enumLookup,
        Type $type
    ) {
        $this->uidEncoder = $uidEncoder;
        $this->enumLookup = $enumLookup;
        $this->type = $type;
    }

    /**
     * Get attribute metadata details
     *
     * @param AttributeInterface $attribute
     * @param int $storeId
     * @param string $entityType
     * @return array|void
     * @throws GraphQlInputException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function getAttributeMetadata(AttributeInterface $attribute, int $storeId, string $entityType)
    {
        if (!$attribute->getFrontendInput()) {
            return;
        }

        $attributeType = $this->type->getType(
            $attribute->getAttributeCode(),
            $attribute->getEntityType()->getEntityTypeCode()
        );
        $dataType = $this->enumLookup->getEnumValueFromField('ObjectDataTypeEnum', $attributeType);
        $uiInputType = $attribute->getFrontendInput() === "textarea" && $attribute->getIsWysiwygEnabled() ?
            'texteditor' :
            $attribute->getFrontendInput();

        return [
            'uid' => $this->uidEncoder->encode($entityType . '/' . $attribute->getAttributeCode()),
            'code' => $attribute->getAttributeCode(),
            'label' => $attribute->getStoreLabel($storeId),
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
                    $uiInputType
                ),
                'is_html_allowed' => $attribute->getIsHtmlAllowedOnFront(),
                'attribute' => $attribute
            ],
            'entity_type_code' => $attribute->getEntityType()->getEntityTypeCode(),
            'attribute' => $attribute
        ];
    }
}
