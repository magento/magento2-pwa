<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReCaptchaPwa\Plugin\Model;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\InputException;
use Magento\ReCaptchaCheckout\Model\WebapiConfigProvider;
use Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface;
use Magento\ReCaptchaUi\Model\ValidationConfigResolverInterface;
use Magento\ReCaptchaValidationApi\Api\Data\ValidationConfigInterface;
use Magento\ReCaptchaWebapiApi\Api\Data\EndpointInterface;

/**
 * Validate reCaptcha on Braintree payment method
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CheckoutWebapiConfigProvider
{
    private const BRAINTREE_CAPTCHA_ID = 'braintree';

    /**
     * @var IsCaptchaEnabledInterface
     */
    private $isEnabled;

    /**
     * @var ValidationConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var Http
     */
    private $request;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param IsCaptchaEnabledInterface $isEnabled
     * @param ValidationConfigResolverInterface $configResolver
     * @param Http $request
     * @param Session $checkoutSession
     */
    public function __construct(
        IsCaptchaEnabledInterface $isEnabled,
        ValidationConfigResolverInterface $configResolver,
        Http $request,
        Session $checkoutSession
    ) {
        $this->isEnabled = $isEnabled;
        $this->configResolver = $configResolver;
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Adds missing config
     *
     * @param WebapiConfigProvider $subject
     * @param ValidationConfigInterface|null $result
     * @param EndpointInterface $endpoint
     * @return ValidationConfigInterface|null
     * @throws InputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetConfigFor(
        WebapiConfigProvider $subject,
        $result,
        EndpointInterface $endpoint
    ): ?ValidationConfigInterface {
        $serviceClass = $endpoint->getServiceClass();
        $serviceMethod = $endpoint->getServiceMethod();

        //phpcs:disable Magento2.PHP.LiteralNamespaces
        if ($serviceMethod === 'savePaymentInformation'
            || $serviceClass === 'Magento\QuoteGraphQl\Model\Resolver\SetPaymentMethodOnCart') {

            $paymentMethodCode = $this->checkoutSession->getData('payment_method_code');

            if ($this->isEnabled->isCaptchaEnabledFor(self::BRAINTREE_CAPTCHA_ID)
                && $this->request->getHeader('X-ReCaptcha')
                && $paymentMethodCode === self::BRAINTREE_CAPTCHA_ID) {
                return $this->configResolver->get(self::BRAINTREE_CAPTCHA_ID);
            }
        }
        //phpcs:enable Magento2.PHP.LiteralNamespaces

        return $result;
    }
}
