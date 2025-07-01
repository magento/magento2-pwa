<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchGraphQlPwa\Plugin\Customer;

use Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\Exception\LocalizedException;

class AfterExtractCustomerData 
{
    /**
     * Modify the Customer GraphQL to Add Customer's group_code field to response. 
     * 
     * @param ExtractCustomerData $subject
     * @param array<scalar>|null $result
     * @return array<scalar>|null
     */
    public function afterExecute(
        ExtractCustomerData $subject,
        ?array $result,
    )
    {
        if (is_array($result) && isset($result['model']) && $result['model'] instanceof Customer)
        {
            $result['group_code'] = $this->getCustomerGroupCodeById($result['model']->getGroupId());
        }

        return $result;
    }

    /**
     * Get customer group code.
     *
     * @param string $groupId
     * @return string
     */
    private function getCustomerGroupCodeById(string $groupId): string
    {
        try {
            $customerGroupId = $groupId;
        } catch (LocalizedException $e) {
            $customerGroupId = GroupInterface::NOT_LOGGED_IN_ID;
        }
        return \sha1((string)$customerGroupId);
    }
}