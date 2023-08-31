<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PaymentAux\Plugin;
// use Magento\Quote\Model\Quote;


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
    public function afterGetCountry($subject,$result, \Magento\Quote\Model\Quote $quote)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom_log.log');            
        $logger = new \Zend_Log();            
        $logger->addWriter($writer);            
        $logger->info('only for testing');            
        $logger->info('country = ' . $quote->getShippingAddress()->getCountry());

        $abc= (!empty($address) && !empty($address->getCountry()))
            ? $address->getCountry()
            : $quote->getShippingAddress()->getCountry();
        $logger->info($abc);    
        // $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/custom_log.log');
        // $logger = new \Zend\Log\Logger();
        // $logger->addWriter($writer);
        // $logger->info('only for testing');
        // print_r("Plugin Working");
        // die;
        $address = $quote->getBillingAddress() ? : $quote->getShippingAddress();
        return (!empty($address) && !empty($address->getCountry()))
            ? $address->getCountry()
            : $quote->getShippingAddress()->getCountry();
    }
}




