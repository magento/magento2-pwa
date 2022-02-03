<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$writer = $objectManager->get(WriterInterface::class);


$configurations = [
    // Forms
    'recaptcha_frontend/type_for/place_order' => '',
    'recaptcha_frontend/type_for/contact' => '',
    'recaptcha_frontend/type_for/customer_forgot_password' => '',
    'recaptcha_frontend/type_for/customer_edit' => '',
    'recaptcha_frontend/type_for/customer_login' => '',
    'recaptcha_frontend/type_for/customer_create' => '',
    'recaptcha_frontend/type_for/newsletter' => '',
    'recaptcha_frontend/type_for/product_review' => '',
    'recaptcha_frontend/type_for/sendfriend' => '',
    'recaptcha_frontend/type_for/braintree' => '',
    // ReCAPTCHA API
    'recaptcha_frontend/type_recaptcha_v3/public_key' => '',
    'recaptcha_frontend/type_recaptcha_v3/private_key' => ''
];

foreach ($configurations as $path => $value) {
    $writer->save($path, $value);
}

$scopeConfig = $objectManager->get(ScopeConfigInterface::class);
$scopeConfig->clean();
