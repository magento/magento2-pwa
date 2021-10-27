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
class UiInputComposite implements TypeResolverInterface
{
    /**
     * @var TypeResolverInterface[]
     */
    private $uiInputTypeResolvers = [];

    /**
     * Not Implemented Item Type
     */
    private const DEFAULT_TYPE = 'UiAttributeTypeAny';

    /**
     * @param TypeResolverInterface[] $uiInputTypeResolvers
     */
    public function __construct(array $uiInputTypeResolvers = [])
    {
        $this->uiInputTypeResolvers = $uiInputTypeResolvers;
    }

    /**
     * @inheritdoc
     */
    public function resolveType(array $data) : string
    {
        $resolvedType = null;

        foreach ($this->uiInputTypeResolvers as $uiInputTypeResolver) {
            if (!isset($data['ui_input_type'])) {
                throw new GraphQlInputException(
                    __('Missing key %1 in attribute data', ['ui_input_type'])
                );
            }
            $resolvedType = $uiInputTypeResolver->resolveType($data);
            if (!empty($resolvedType)) {
                return $resolvedType;
            }
        }

        return self::DEFAULT_TYPE;
    }
}
