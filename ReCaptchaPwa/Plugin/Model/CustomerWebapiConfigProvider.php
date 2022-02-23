<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReCaptchaPwa\Plugin\Model;

use Magento\Framework\Exception\InputException;
use Magento\ReCaptchaCustomer\Model\WebapiConfigProvider;
use Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface;
use Magento\ReCaptchaUi\Model\ValidationConfigResolverInterface;
use Magento\ReCaptchaValidationApi\Api\Data\ValidationConfigInterface;
use Magento\ReCaptchaWebapiApi\Api\Data\EndpointInterface;

class CustomerWebapiConfigProvider
{
    private const RESET_PASSWORD_CAPTCHA_ID = 'customer_forgot_password';

    /**
     * @var IsCaptchaEnabledInterface
     */
    private $isEnabled;

    /**
     * @var ValidationConfigResolverInterface
     */
    private $configResolver;

    /**
     * @param IsCaptchaEnabledInterface $isEnabled
     * @param ValidationConfigResolverInterface $configResolver
     */
    public function __construct(
        IsCaptchaEnabledInterface $isEnabled,
        ValidationConfigResolverInterface $configResolver
    ) {
        $this->isEnabled = $isEnabled;
        $this->configResolver = $configResolver;
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
        if ($serviceMethod === 'resetPassword'
            || $serviceMethod === 'initiatePasswordReset'
            || $serviceClass === 'Magento\CustomerGraphQl\Model\Resolver\ResetPassword'
            || $serviceClass === 'Magento\CustomerGraphQl\Model\Resolver\RequestPasswordResetEmail') {
            if ($this->isEnabled->isCaptchaEnabledFor(self::RESET_PASSWORD_CAPTCHA_ID)) {
                return $this->configResolver->get(self::RESET_PASSWORD_CAPTCHA_ID);
            }
        }
        //phpcs:enable Magento2.PHP.LiteralNamespaces

        return $result;
    }
}
