<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ContactGraphQlPwa\Model\Resolver;

use Magento\Contact\Model\ConfigInterface;
use Magento\Contact\Model\MailInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Validator\EmailAddress;
use Psr\Log\LoggerInterface;

class ContactUs implements ResolverInterface
{
    public const DEFAULT_VALUES = [
        'telephone' => '-'
    ];

    /** @var \Magento\Contact\Model\MailInterface */
    private $mail;

    /** @var \Magento\Contact\Model\ConfigInterface */
    private $contactConfig;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /** @var \Magento\Framework\Validator\EmailAddress */
    private $emailValidator;

    /**
     * @param \Magento\Contact\Model\MailInterface $mail
     * @param \Magento\Contact\Model\ConfigInterface $contactConfig
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Validator\EmailAddress $emailValidator
     */
    public function __construct(
        MailInterface $mail,
        ConfigInterface $contactConfig,
        LoggerInterface $logger,
        EmailAddress $emailValidator
    ) {
        $this->mail = $mail;
        $this->contactConfig = $contactConfig;
        $this->logger = $logger;
        $this->emailValidator = $emailValidator;
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!$this->contactConfig->isEnabled()) {
            throw new GraphQlInputException(
                __('The contact form is unavailable.')
            );
        }

        $input = $this->cleanInput($args['input']);
        $this->validateInput($input);

        $variables = ['data' => $input];
        try {
            $this->mail->send($input['email'], $variables);
        } catch (\Exception $e) {
            $this->logger->critical($e);

            throw new GraphQlInputException(
                __('An error occurred while processing your form. Please try again later.')
            );
        }

        return [
            'status' => true
        ];
    }

    /**
     * Get default values map
     *
     * @return string[]
     */
    public function getDefaultValues(): array
    {
        return self::DEFAULT_VALUES;
    }

    /**
     * Clean input values and set default values
     *
     * @param string[] $input
     * @return string[]
     */
    public function cleanInput(array $input): array
    {
        $values = [];
        $defaults = $this->getDefaultValues();
        foreach ($input as $field => $value) {
            $cleanValue = $value === null ? '' : trim($value);

            if ($cleanValue === '' && isset($defaults[$field])) {
                $cleanValue = $defaults[$field];
            }

            $values[$field] = $cleanValue;
        }

        foreach ($defaults as $field => $value) {
            if (!isset($values[$field])) {
                $values[$field] = $value;
            }
        }

        return $values;
    }

    /**
     * Validate input data
     *
     * @param string[] $input
     * @return void
     * @throws GraphQlInputException
     */
    public function validateInput(array $input): void
    {
        if (!$this->emailValidator->isValid($input['email'])) {
            throw new GraphQlInputException(
                __('The email address is invalid. Verify the email address and try again.')
            );
        }

        if ($input['name'] === '') {
            throw new GraphQlInputException(__('Enter the Name and try again.'));
        }

        if ($input['comment'] === '') {
            throw new GraphQlInputException(__('Enter the comment and try again.'));
        }
    }
}
