<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReCaptchaPwa\Plugin;

use Magento\Checkout\Model\Session;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;

/**
 * Validate ReCaptcha for GraphQl mutations.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class GraphQlValidatorPlugin
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param Session $checkoutSession
     */
    public function __construct(
        Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Validate ReCaptcha for mutations if needed.
     *
     * @param ResolverInterface $subject
     * @param Field $fieldInfo
     * @param mixed $context
     * @param ResolveInfo $resolveInfo
     * @param array|null $values
     * @param array $args
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeResolve(
        ResolverInterface $subject,
        Field $fieldInfo,
        $context,
        ResolveInfo $resolveInfo,
        ?array $values,
        array $args
    ): void {
        if ($resolveInfo->operation->operation !== 'mutation') {
            return;
        }

        $paymentMethodCode = null;
        if (isset($args['input']['payment_method']['code'])) {
            $paymentMethodCode = $args['input']['payment_method']['code'];
        }

        $this->checkoutSession->setPaymentMethodCode($paymentMethodCode);
    }
}
