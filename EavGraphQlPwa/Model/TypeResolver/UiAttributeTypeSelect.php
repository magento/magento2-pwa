<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQlPwa\Model\TypeResolver;

use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * @inheritdoc
 */
class UiAttributeTypeSelect implements TypeResolverInterface
{
    const TYPE_RESOLVER = 'UiAttributeTypeSelect';

    /**
     * @inheritdoc
     */
    public function resolveType(array $data) : string
    {
        if (isset($data['ui_input_type']) && $data['ui_input_type'] == 'SELECT') {
            return self::TYPE_RESOLVER;
        }
        return '';
    }
}
