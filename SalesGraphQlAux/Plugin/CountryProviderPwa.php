<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesGraphQlAux\Plugin;
use Magento\Quote\Model\Quote;

/**
 * Select country which will be used for payment.
 *
 * This class may be extended if logic fo country selection should be modified.
 *
 * @api
 * @since 100.0.2
 */
class CountryProviderPwa
{
    /**
     * Get payment country
     *
     * @param Quote $quote
     * @return int
     */
    public function afterGetCountry($subject, $result, $quote)
    { 
        $address = $quote->getBillingAddress() ? : $quote->getShippingAddress();
        return (!empty($address) && !empty($address->getCountry()))
            ? $address->getCountry()
            : $quote->getShippingAddress()->getCountry();
    }
}
