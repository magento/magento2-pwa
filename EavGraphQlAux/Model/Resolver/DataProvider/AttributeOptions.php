<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQlAux\Model\Resolver\DataProvider;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Eav\Api\Data\AttributeInterface;

/**
 * @inheritdoc
 */
class AttributeOptions
{
    /** @var Uid */
    private $uidEncoder;

    /**
     * @param Uid $uidEncoder
     */
    public function __construct(
        Uid $uidEncoder
    ) {
        $this->uidEncoder = $uidEncoder;
    }

    /**
     * Get attribute options data
     *
     * @param AttributeInterface $attribute
     * @return array
     * @throws LocalizedException
     */
    public function getAttributeOptions(AttributeInterface $attribute): array
    {
        $options = $attribute->getOptions() ?? [];
        $optionsData = [];
        foreach ($options as $option) {
            if ($option->getValue() === '') {
                continue;
            }

            $optionDetails = [
                $attribute->getEntityType()->getEntityTypeCode(),
                $attribute->getAttributeCode(),
                (string) $option->getValue()
            ];

            $uidString = implode('/', $optionDetails);

            $optionsData[] = [
                'uid' => $this->uidEncoder->encode($uidString),
                'value' => $option->getValue(),
                'label' => $option->getLabel(),
                'is_default' => $option->getValue() === $attribute->getDefaultValue()
            ];
        }
        return $optionsData;
    }
}
