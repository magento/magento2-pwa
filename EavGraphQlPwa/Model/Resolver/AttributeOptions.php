<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQlPwa\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * @inheritdoc
 */
class AttributeOptions implements ResolverInterface
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
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['attribute_options'])) {
            throw new LocalizedException(__('"attribute_options" value should be specified'));
        }
        $options = $value['attribute_options'];
        $optionsData = [];
        foreach ($options as $option) {
            if ($option->getValue() === '') {
                continue;
            }

            $optionDetails = [
                $value['entity_type'],
                $value['attribute_code'],
                (string) $option->getValue()
            ];

            $uidString = implode('/', $optionDetails);

            $optionsData[] = [
                'uid' => $this->uidEncoder->encode($uidString),
                'label' => $option->getLabel()
            ];
        }
        return $optionsData;
    }
}
