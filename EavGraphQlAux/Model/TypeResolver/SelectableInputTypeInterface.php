<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQlAux\Model\TypeResolver;

use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * @inheritdoc
 */
class SelectableInputTypeInterface implements TypeResolverInterface
{
    /**
     * Item Type
     */
    private const TYPE = 'SelectableInputType';

    /**
     * @inheritdoc
     */
    public function resolveType(array $data) : string
    {
        return self::TYPE;
    }
}
