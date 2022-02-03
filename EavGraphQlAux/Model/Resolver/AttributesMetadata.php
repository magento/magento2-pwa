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
use Magento\EavGraphQlAux\Model\Resolver\DataProvider\AttributeMetadata as MetadataProvider;
use Magento\Store\Api\Data\StoreInterface;

/**
 * @inheritdoc
 */
class AttributesMetadata implements ResolverInterface
{
    /**
     * @var Attributes
     */
    private $attributes;

    /**
     * @var MetadataProvider
     */
    private $metadataProvider;

    /**
     * @var DataMapperInterface
     */
    private $enumDataMapper;

    /**
     * @param Attributes $attributes
     * @param DataMapperInterface $enumDataMapper
     * @param MetadataProvider $metadataProvider
     */
    public function __construct(
        Attributes $attributes,
        DataMapperInterface $enumDataMapper,
        MetadataProvider $metadataProvider
    ) {
        $this->attributes = $attributes;
        $this->enumDataMapper = $enumDataMapper;
        $this->metadataProvider = $metadataProvider;
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

        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();
        $storeId = (int)$store->getId();

        $items = [];
        $entityType = $this->getEntityType($args['entityType']);

        $attributes = $this->attributes->getAttributes(
            $entityType,
            $args['attributeUids'] ?? [],
            $args['showSystemAttributes'] ?? false
        );

        foreach ($attributes as $attribute) {
            $items[] = $this->metadataProvider->getAttributeMetadata($attribute, $storeId, $entityType);
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
