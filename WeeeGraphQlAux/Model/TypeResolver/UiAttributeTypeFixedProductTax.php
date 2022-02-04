<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WeeeGraphQlAux\Model\TypeResolver;

use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * @inheritdoc
 */
class UiAttributeTypeFixedProductTax implements TypeResolverInterface
{
    private const TYPE = 'UiAttributeTypeFixedProductTax';

    /**
     * @inheritdoc
     */
    public function resolveType(array $data) : string
    {
        if (isset($data['ui_input_type']) && $data['ui_input_type'] == 'FIXED_PRODUCT_TAX') {
            return self::TYPE;
        }
        return '';
    }
}
