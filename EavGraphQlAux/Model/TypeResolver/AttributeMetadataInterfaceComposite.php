<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQlAux\Model\TypeResolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * @inheritdoc
 */
class AttributeMetadataInterfaceComposite implements TypeResolverInterface
{
    /**
     * @var TypeResolverInterface[]
     */
    private $attributeEntityTypeResolvers = [];

    /**
     * @param TypeResolverInterface[] $attributeEntityTypeResolvers
     */
    public function __construct(array $attributeEntityTypeResolvers = [])
    {
        $this->attributeEntityTypeResolvers = $attributeEntityTypeResolvers;
    }

    /**
     * @inheritdoc
     */
    public function resolveType(array $data) : string
    {
        $resolvedType = null;

        foreach ($this->attributeEntityTypeResolvers as $attributeEntityTypeResolver) {
            if (!isset($data['entity_type_code'])) {
                throw new GraphQlInputException(
                    __('Missing key %1 in attribute data', ['entity_type_code'])
                );
            }
            $resolvedType = $attributeEntityTypeResolver->resolveType($data);
            if (!empty($resolvedType)) {
                return $resolvedType;
            }
        }

        throw new GraphQlInputException(
            __('Concrete type for %1 not implemented', ['AttributeMetadataInterface'])
        );
    }
}
