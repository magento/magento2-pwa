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
class UiAttributeTypeTextEditor implements TypeResolverInterface
{
    private const TYPE = 'UiAttributeTypeTextEditor';

    /**
     * @inheritdoc
     */
    public function resolveType(array $data) : string
    {
        if (isset($data['ui_input_type']) && $data['ui_input_type'] == 'TEXTEDITOR') {
            return self::TYPE;
        }
        return '';
    }
}
