<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQlPwa\Model\TypeResolver;

namespace Magento\EavGraphQlPwa\Model\TypeResolver;
use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * {@inheritdoc}
 */
class AttributeOptionsTypeInterface implements TypeResolverInterface
{
    /**
     * {@inheritdoc}
     * @throws GraphQlInputException
     */
    public function resolveType(array $data) : string
    {
        return 'AttributeOptions';
    }
}
