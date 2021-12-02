<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReCaptchaGraphQlPwa\Test\GraphQl;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class ConfigResolverTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento_ReCaptchaGraphQlPwa::Test/GraphQl/_files/config_full.php
     */
    public function testFullyConfigured()
    {
        $forms = [
            'place_order',
            'contact',
            'customer_forgot_password',
            'customer_edit',
            'customer_login',
            'customer_create',
            'newsletter',
            'product_review',
            'sendfriend',
            'braintree'
        ];
        $publicKey = 'google-api-public-key';

        $query = $this->getQuery();
        $response = $this->graphQlQuery($query);

        $this->assertResponseFields(
            $response['recaptchaV3Config'],
            [
                'website_key' => $publicKey,
                'forms' => $this->getAssertionForms($forms)
            ]
        );
    }

    /**
     * @magentoApiDataFixture Magento_ReCaptchaGraphQlPwa::Test/GraphQl/_files/config_reduced_forms.php
     */
    public function testReducedForms()
    {
        $forms = [
            'place_order',
            'contact',
            'customer_forgot_password',
            'customer_edit',
            'customer_login'
        ];
        $publicKey = 'google-api-public-key';

        $query = $this->getQuery();
        $response = $this->graphQlQuery($query);

        $this->assertResponseFields(
            $response['recaptchaV3Config'],
            [
                'website_key' => $publicKey,
                'forms' => $this->getAssertionForms($forms)
            ]
        );
    }

    /**
     * @magentoApiDataFixture Magento_ReCaptchaGraphQlPwa::Test/GraphQl/_files/config_no_private_key.php
     */
    public function testEmptyPrivateKey()
    {
        $query = $this->getQuery();
        $response = $this->graphQlQuery($query);

        $this->assertNull($response['recaptchaV3Config']);
    }

    /**
     * @magentoApiDataFixture Magento_ReCaptchaGraphQlPwa::Test/GraphQl/_files/config_no_public_key.php
     */
    public function testEmptyPublicKey()
    {
        $query = $this->getQuery();
        $response = $this->graphQlQuery($query);

        $this->assertNull($response['recaptchaV3Config']);
    }

    /**
     * @magentoApiDataFixture Magento_ReCaptchaGraphQlPwa::Test/GraphQl/_files/config_no_forms.php
     */
    public function testEmptyForms()
    {
        $query = $this->getQuery();
        $response = $this->graphQlQuery($query);

        $this->assertNull($response['recaptchaV3Config']);
    }

    /**
     * @magentoApiDataFixture Magento_ReCaptchaGraphQlPwa::Test/GraphQl/_files/config_empty.php
     */
    public function testEmptyConfiguration()
    {
        $query = $this->getQuery();
        $response = $this->graphQlQuery($query);

        $this->assertNull($response['recaptchaV3Config']);
    }

    private function getQuery()
    {
        return <<<QUERY
{
  recaptchaV3Config {
    website_key
    minimum_score
    badge_position
    language_code
    failure_message
    forms
  }
 }
QUERY;
    }

    private function getAssertionForms(array $forms): array
    {
        return array_map('strtoupper', $forms);
    }
}
