<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQlAux\Model\TypeResolver;

use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface as Type;

/**
 * @inheritdoc
 */
class ProductAttributeMetadata implements TypeResolverInterface
{
    private const TYPE = 'ProductAttributeMetadata';

    /**
     * @inheritdoc
     */
    public function resolveType(array $data) : string
    {
        if (isset($data['entity_type_code']) && $data['entity_type_code'] == Type::ENTITY_TYPE_CODE) {
            return self::TYPE;
        }
        return '';
    }
}
