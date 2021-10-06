<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQlAux\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\Enum\DataMapperInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * @inheritdoc
 */
class ProductAttributes implements ResolverInterface
{
    /**
     * @var DataMapperInterface
     */
    private $enumDataMapper;

    /**
     * @param DataMapperInterface $enumDataMapper
     */
    public function __construct(
        DataMapperInterface $enumDataMapper
    ) {
        $this->enumDataMapper = $enumDataMapper;
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

        $listsMap = $this->enumDataMapper->getMappedEnums($field->getTypeName());
        $lists = [];

        foreach ($listsMap as $name => $value) {
            if ($attribute->getData($value)) {
                $lists[] = strtoupper($name);
            }
        }

        return $lists;
    }
}
