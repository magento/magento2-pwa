<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$writer = $objectManager->get(WriterInterface::class);
$encryptor = $objectManager->get(EncryptorInterface::class);

$publicConfigurations = [
    'recaptcha_frontend/type_for/place_order' => 'recaptcha_v3',
    'recaptcha_frontend/type_for/contact' => 'recaptcha_v3',
    'recaptcha_frontend/type_for/customer_forgot_password' => 'recaptcha_v3',
    'recaptcha_frontend/type_for/customer_edit' => 'recaptcha_v3',
    'recaptcha_frontend/type_for/customer_login' => 'recaptcha_v3',
    'recaptcha_frontend/type_for/customer_create' => 'type_invisible',
    'recaptcha_frontend/type_for/newsletter' => 'type_invisible',
    'recaptcha_frontend/type_for/product_review' => 'type_invisible',
    'recaptcha_frontend/type_for/sendfriend' => 'type_invisible',
    'recaptcha_frontend/type_for/braintree' => 'type_invisible'
];

$encryptedConfigurations = [
    'recaptcha_frontend/type_recaptcha_v3/public_key' =>'google-api-public-key',
    'recaptcha_frontend/type_recaptcha_v3/private_key' => 'google-api-PRIVATE-key'
];

foreach ($publicConfigurations as $path => $value) {
    $writer->save($path, $value);
}

foreach ($encryptedConfigurations as $path => $value) {
    $configValue = !empty($value) ? $encryptor->encrypt($value) : $value;
    $writer->save($path, $configValue);
}

$scopeConfig = $objectManager->get(ScopeConfigInterface::class);
$scopeConfig->clean();
