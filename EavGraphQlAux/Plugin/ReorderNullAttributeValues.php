<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQlAux\Plugin;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\DB\Select;

class ReorderNullAttributeValues
{
    /**
     * Plugin to reorder attributes with null value to the bottom
     *
     * @param AbstractCollection $subject
     * @param AbstractCollection $result
     * @param string $attribute
     * @param string $dir
     * @return AbstractCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     */
    public function afterAddAttributeToSort(
        AbstractCollection $subject,
        AbstractCollection $result,
        string $attribute,
        string $dir = Select::SQL_ASC
    ): AbstractCollection {
        $attributeInstance = $subject->getEntity()->getAttribute($attribute);
        if ($attribute !== 'price' &&
            $attributeInstance &&
            $attributeInstance->getUsedForSortBy() &&
            $attributeInstance->getIsSearchable()
        ) {
            $sorOrders = $result->getSelect()->getPart(Select::ORDER);
            $orderBy = null;
            if (isset($sorOrders[0]) && !$sorOrders[0] instanceof \Zend_Db_Expr) {
                $orderBy = $sorOrders[0][0] ?? null;
            }

            if ($orderBy) {
                $result->getSelect()->reset(Select::ORDER);
                $result->getSelect()->order(new \Zend_Db_Expr("ISNULL({$orderBy}), {$orderBy} {$dir}"));
            }
        }
        return $result;
    }
}
