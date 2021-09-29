<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQlPwa\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote\Item;

/**
 * @inheritdoc
 */
class CartItemError implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var Item $cartItem */
        $cartItem = $value['model'];
        $hasError = (bool) $cartItem->getData('has_error');

        return [
            'has_error' => $hasError,
            'message' => $hasError ? $this->getItemErrors($cartItem) : null
        ];
    }

    private function getItemErrors(Item $cartItem): string
    {
        $errors = [];
        foreach ($cartItem->getErrorInfos() as $error) {
            $errors[] = $error['message'];
        }

        return implode(' ', $errors);
    }
}
