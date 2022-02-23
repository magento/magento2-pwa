<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReCaptchaPwa\Test\Unit\Plugin\Model;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\ReCaptchaWebapiApi\Api\Data\EndpointInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\ReCaptchaCheckout\Model\WebapiConfigProvider;
use Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface;
use Magento\ReCaptchaUi\Model\ValidationConfigResolverInterface;
use Magento\ReCaptchaValidationApi\Api\Data\ValidationConfigInterface;
use Magento\ReCaptchaPwa\Plugin\Model\CheckoutWebapiConfigProvider as WebapiConfigProviderPlugin;

class CheckoutWebapiConfigProviderTest extends TestCase
{
    private const BRAINTREE_CAPTCHA_ID = 'braintree';

    /**
     * @var WebapiConfigProviderPlugin
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $isCaptchaEnabledInterfaceMock;

    /**
     * @var MockObject
     */
    protected $validationConfigResolverInterfaceMock;

    /**
     * @var MockObject
     */
    protected $validationConfigInterfaceMock;

    /**
     * @var MockObject
     */
    protected $requestMock;

    /**
     * @var Session|MockObject
     */
    protected $checkoutSessionMock;

    /**
     * Setup
     */
    protected function setUp(): void
    {
        $this->isCaptchaEnabledInterfaceMock = $this->createMock(IsCaptchaEnabledInterface::class);
        $this->validationConfigResolverInterfaceMock = $this->createMock(ValidationConfigResolverInterface::class);
        $this->validationConfigInterfaceMock = $this->createMock(ValidationConfigInterface::class);
        $this->requestMock = $this->createMock(Http::class);
        $this->checkoutSessionMock = $this->createMock(Session::class);

        $this->model = new WebapiConfigProviderPlugin(
            $this->isCaptchaEnabledInterfaceMock,
            $this->validationConfigResolverInterfaceMock,
            $this->requestMock,
            $this->checkoutSessionMock
        );
    }

    /**
     * Test afterGetConfigFor for checkout payment form
     *
     * @throws \Magento\Framework\Exception\InputException
     */
    public function testAfterGetConfigForCheckoutPaymentForm()
    {
        $this->isCaptchaEnabledInterfaceMock
            ->expects($this->once())
            ->method('isCaptchaEnabledFor')
            ->willReturn(true);
        $this->validationConfigResolverInterfaceMock
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->validationConfigInterfaceMock);
        $this->requestMock
            ->expects($this->once())
            ->method('getHeader')
            ->willReturn(true);
        $this->checkoutSessionMock
            ->expects($this->once())
            ->method('getData')
            ->willReturn(self::BRAINTREE_CAPTCHA_ID);

        $webapiConfigProviderMock = $this->createMock(WebapiConfigProvider::class);
        $resultMock = null;
        $endpointInterfaceMock = $this->createMock(EndpointInterface::class);
        $endpointInterfaceMock
            ->expects($this->once())
            ->method('getServiceMethod')
            ->willReturn('savePaymentInformation');
        $endpointInterfaceMock
            ->expects($this->once())
            ->method('getServiceClass')
            ->willReturn('Magento\QuoteGraphQl\Model\Resolver\SetPaymentMethodOnCart');

        $this->assertEquals(
            $this->validationConfigInterfaceMock,
            $this->model->afterGetConfigFor($webapiConfigProviderMock, $resultMock, $endpointInterfaceMock)
        );
    }
}
