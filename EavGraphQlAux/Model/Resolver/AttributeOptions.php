<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQlAux\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\EavGraphQlAux\Model\Resolver\DataProvider\AttributeOptions as OptionsProvider;

/**
 * @inheritdoc
 */
class AttributeOptions implements ResolverInterface
{
    /** @var OptionsProvider */
    private $optionsProvider;

    /**
     * @param OptionsProvider $optionsProvider
     */
    public function __construct(
        OptionsProvider $optionsProvider
    ) {
        $this->optionsProvider = $optionsProvider;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['attribute'])) {
            throw new LocalizedException(__('"attribute" value should be specified'));
        }

        $attribute = $value['attribute'];

        return $this->optionsProvider->getAttributeOptions($attribute);
    }
}
