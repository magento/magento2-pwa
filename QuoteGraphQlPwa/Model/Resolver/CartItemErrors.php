<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQlPwa\Model\Resolver;

use Magento\CatalogInventory\Helper\Data as InventoryHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote\Item;

/**
 * @inheritdoc
 */
class CartItemErrors implements ResolverInterface
{
    public const ERROR_UNDEFINED = 0;

    /** @var \Magento\Framework\GraphQl\Query\EnumLookup */
    private $enumLookup;

    /**
     * @param \Magento\Framework\GraphQl\Query\EnumLookup $enumLookup
     */
    public function __construct(
        EnumLookup $enumLookup
    ) {
        $this->enumLookup = $enumLookup;
    }

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

        return $this->getItemErrors($cartItem);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $cartItem
     *
     * @return string[]|null
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    private function getItemErrors(Item $cartItem): ?array
    {
        $hasError = (bool) $cartItem->getData('has_error');
        if (!$hasError) {
            return null;
        }

        $errors = [];
        foreach ($cartItem->getErrorInfos() as $error) {
            $errorType = $error['code'] ?? self::ERROR_UNDEFINED;
            $errorEnumCode = $this->enumLookup->getEnumValueFromField(
                'CartItemErrorType',
                (string)$errorType
            );
            $errors[] = [
                'code' => $errorEnumCode,
                'message' => $error['message']
            ];
        }

        return $errors;
    }
}
