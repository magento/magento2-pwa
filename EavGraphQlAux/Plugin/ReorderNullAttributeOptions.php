<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQlAux\Plugin;

use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\DB\Select;

class ReorderNullAttributeOptions
{
    /**
     * Plugin to reorder attributes with null value
     *
     * @param Table $subject
     * @param Table $result
     * @param AbstractCollection $collection
     * @param string $dir
     * @return Table
     */
    public function afterAddValueSortToCollection(
        Table $subject,
        Table $result,
        AbstractCollection $collection,
        string $dir = Select::SQL_ASC
    ): Table {
        $attribute = $subject->getAttribute();
        if ($attribute && $attribute->getUsedForSortBy() && $attribute->getIsSearchable()) {
            $attributeCode = $subject->getAttribute()->getAttributeCode();
            $collection->getSelect()->reset(Select::ORDER);
            $collection->getSelect()->order(
                new \Laminas\Db\Sql\Expression("ISNULL({$attributeCode}_value), {$attributeCode}_value {$dir}")
            );
        }
        return $result;
    }
}
