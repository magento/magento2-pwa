<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageBuilderPwa\Plugin;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\EavGraphQlAux\Model\Resolver\DataProvider\AttributeMetadata;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\Framework\Exception\RuntimeException;

class UiInputTypePageBuilder
{
    private const TYPE = 'pagebuilder';

    /**
     * @var EnumLookup
     */
    private $enumLookup;

    /**
     * @param EnumLookup $enumLookup
     */
    public function __construct(
        EnumLookup $enumLookup
    ) {
        $this->enumLookup = $enumLookup;
    }

    /**
     * Change ui input type for the attribute if PageBuilder is enabled for it
     *
     * @param AttributeMetadata $subject
     * @param array $result
     * @param AttributeInterface $attribute
     * @return array
     * @throws RuntimeException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAttributeMetadata(
        AttributeMetadata $subject,
        array $result,
        AttributeInterface $attribute
    ): array {
        if ($attribute->getIsPagebuilderEnabled()) {
            $result['ui_input']['ui_input_type'] = $this->enumLookup->getEnumValueFromField(
                'UiInputTypeEnum',
                self::TYPE
            );
        }
        return $result;
    }
}
