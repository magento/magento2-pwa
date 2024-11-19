<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQlPwa\Model\Resolver;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\EmailNotConfirmedException;

/**
 * Is confirmed status resolver
 */
class IsConfirmed implements ResolverInterface
{
    /**
     * @param AccountManagementInterface $accountManagement
     */
    public function __construct(
        private readonly AccountManagementInterface $accountManagement
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        try {
            $id = (int) $value['model']->getId() ?? '';
            if ($this->accountManagement->getConfirmationStatus($id)) {
                throw new EmailNotConfirmedException(
                    __(
                        'Your account is created, You must confirm your account. 
                        Please check your email for the confirmation link.'
                    )
                );
            }
            return true;
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }
    }
}
