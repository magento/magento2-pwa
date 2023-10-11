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
     * @param array $option_ids
     * @return array
     * @throws LocalizedException
     */
    public function getAttributeOptions(AttributeInterface $attribute, array $option_ids = []): array
    {
        $options = $option_ids ? $this->getSpecificOptions($attribute, $option_ids) :
            $this->getAllOptions($attribute);

        $optionsData = [];
        foreach ($options as $option) {
            if ($option['value'] === '') {
                continue;
            }

            $optionDetails = [
                $attribute->getEntityType()->getEntityTypeCode(),
                $attribute->getAttributeCode(),
                (string)$option['value']
            ];

            $uidString = implode('/', $optionDetails);

            $optionsData[] = [
                'uid' => $this->uidEncoder->encode($uidString),
                'value' => $option['value'],
                'label' => $option['label'],
                'is_default' => $option['value'] === $attribute->getDefaultValue()
            ];
        }
        return $optionsData;
    }

    /**
     * Get specific options for attribute
     *
     * @param AttributeInterface $attribute
     * @param array $option_ids
     * @return array
     */
    private function getSpecificOptions(AttributeInterface $attribute, array $option_ids) : array
    {
        $options = [];
        $attributeSource = $attribute->usesSource() ? $attribute->getSource() : null;
        if ($attributeSource && method_exists($attributeSource, 'getSpecificOptions')) {
            $options = $attributeSource->getSpecificOptions($option_ids);
        } else {
            $allOptions = $this->getAllOptions($attribute);
            $selectedOptionIdsLookup = array_flip($option_ids);

            foreach ($allOptions as $option) {
                if (isset($selectedOptionIdsLookup[$option['value']])) {
                    $options[] = $option;
                }
            }
        }

        return $options;
    }

    /**
     * Get all options for attribute
     *
     * @param AttributeInterface $attribute
     * @return array
     */
    private function getAllOptions(AttributeInterface $attribute) : array
    {
        $options = $attribute->getData(AttributeInterface::OPTIONS);
        if (!$options) {
            $options = $attribute->usesSource() ? $attribute->getSource()->getAllOptions() : [];
        }

        return $options;
    }
}
