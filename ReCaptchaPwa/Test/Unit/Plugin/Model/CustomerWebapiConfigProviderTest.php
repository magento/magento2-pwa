<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReCaptchaPwa\Test\Unit\Plugin\Model;

use Magento\ReCaptchaWebapiApi\Api\Data\EndpointInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\ReCaptchaCustomer\Model\WebapiConfigProvider;
use Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface;
use Magento\ReCaptchaUi\Model\ValidationConfigResolverInterface;
use Magento\ReCaptchaValidationApi\Api\Data\ValidationConfigInterface;
use Magento\ReCaptchaPwa\Plugin\Model\CustomerWebapiConfigProvider as WebapiConfigProviderPlugin;

class CustomerWebapiConfigProviderTest extends TestCase
{
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
     * Setup
     */
    protected function setUp(): void
    {
        $this->isCaptchaEnabledInterfaceMock = $this->createMock(IsCaptchaEnabledInterface::class);
        $this->validationConfigResolverInterfaceMock = $this->createMock(ValidationConfigResolverInterface::class);

        $this->validationConfigInterfaceMock = $this->createMock(ValidationConfigInterface::class);

        $this->model = new WebapiConfigProviderPlugin(
            $this->isCaptchaEnabledInterfaceMock,
            $this->validationConfigResolverInterfaceMock
        );
    }

    /**
     * Test afterGetConfigFor for reset password form
     *
     * @throws \Magento\Framework\Exception\InputException
     */
    public function testAfterGetConfigForResetPasswordForm()
    {
        $this->isCaptchaEnabledInterfaceMock
            ->expects($this->once())
            ->method('isCaptchaEnabledFor')
            ->willReturn(true);
        $this->validationConfigResolverInterfaceMock
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->validationConfigInterfaceMock);

        $webapiConfigProviderMock = $this->createMock(WebapiConfigProvider::class);
        $resultMock = null;
        $endpointInterfaceMock = $this->createMock(EndpointInterface::class);
        $endpointInterfaceMock
            ->expects($this->once())
            ->method('getServiceMethod')
            ->willReturn('resetPassword');
        $endpointInterfaceMock
            ->expects($this->once())
            ->method('getServiceClass')
            ->willReturn('Magento\CustomerGraphQl\Model\Resolver\ResetPassword');

        $this->assertEquals(
            $this->validationConfigInterfaceMock,
            $this->model->afterGetConfigFor($webapiConfigProviderMock, $resultMock, $endpointInterfaceMock)
        );
    }

    /**
     * Test afterGetConfigFor for password reset form
     *
     * @throws \Magento\Framework\Exception\InputException
     */
    public function testAfterGetConfigForPasswordResetForm()
    {
        $this->isCaptchaEnabledInterfaceMock
            ->expects($this->once())
            ->method('isCaptchaEnabledFor')
            ->willReturn(true);
        $this->validationConfigResolverInterfaceMock
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->validationConfigInterfaceMock);

        $webapiConfigProviderMock = $this->createMock(WebapiConfigProvider::class);
        $resultMock = null;
        $endpointInterfaceMock = $this->createMock(EndpointInterface::class);
        $endpointInterfaceMock
            ->expects($this->once())
            ->method('getServiceMethod')
            ->willReturn('initiatePasswordReset');
        $endpointInterfaceMock
            ->expects($this->once())
            ->method('getServiceClass')
            ->willReturn('Magento\CustomerGraphQl\Model\Resolver\RequestPasswordResetEmail');

        $this->assertEquals(
            $this->validationConfigInterfaceMock,
            $this->model->afterGetConfigFor($webapiConfigProviderMock, $resultMock, $endpointInterfaceMock)
        );
    }

    /**
     * Test afterGetConfigFor for fake form
     *
     * @throws \Magento\Framework\Exception\InputException
     */
    public function testAfterGetConfigForFakeForm()
    {
        $webapiConfigProviderMock = $this->createMock(WebapiConfigProvider::class);
        $resultMock = null;
        $endpointInterfaceMock = $this->createMock(EndpointInterface::class);
        $endpointInterfaceMock
            ->expects($this->once())
            ->method('getServiceMethod')
            ->willReturn('fakeForm');
        $endpointInterfaceMock
            ->expects($this->once())
            ->method('getServiceClass')
            ->willReturn('Magento\CustomerGraphQl\Model\Resolver\FakeForm');

        $this->assertEquals(
            $resultMock,
            $this->model->afterGetConfigFor($webapiConfigProviderMock, $resultMock, $endpointInterfaceMock)
        );
    }
}
