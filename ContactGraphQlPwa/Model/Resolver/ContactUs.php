<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ContactGraphQlPwa\Model\Resolver;

use Magento\Contact\Model\ConfigInterface;
use Magento\Contact\Model\MailInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Psr\Log\LoggerInterface;

class ContactUs implements ResolverInterface
{
    public const EMPTY_OPTIONAL_VALUE = '-';

    /** @var \Magento\Contact\Model\MailInterface */
    private $mail;

    /** @var \Magento\Contact\Model\ConfigInterface */
    private $contactConfig;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /**
     * @param \Magento\Contact\Model\MailInterface $mail
     * @param \Magento\Contact\Model\ConfigInterface $contactConfig
     * @param \Psr\Log\LoggerInterface|null $logger
     */
    public function __construct(
        MailInterface $mail,
        ConfigInterface $contactConfig,
        LoggerInterface $logger = null
    ) {
        $this->mail = $mail;
        $this->contactConfig = $contactConfig;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
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

        $input = $this->validatedParams($args['input']);
        $email = $input['email'];

        $variables = ['data' => $input];
        try {
            $this->mail->send($email, $variables);
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
     * Validate and return input data
     *
     * @param array<string, string> $input
     *
     * @return array<string, string>
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlInputException
     */
    public function validatedParams(array $input): array
    {
        $email = isset($input['email']) ? trim($input['email']) : '';
        if (empty($email) || strpos($email, '@') === false) {
            throw new GraphQlInputException(
                __('The email address is invalid. Verify the email address and try again.')
            );
        }
        $input['email'] = $email;

        $name = $input['name'] ?? '';
        if (trim($name) === '') {
            throw new GraphQlInputException(__('Enter the Name and try again.'));
        }

        $comment = $input['comment'] ?? '';
        if (trim($comment) === '') {
            throw new GraphQlInputException(__('Enter the comment and try again.'));
        }

        // Base email template requires telephone
        if (!isset($input['telephone'])) {
            $input['telephone'] = self::EMPTY_OPTIONAL_VALUE;
        }

        return $input;
    }
}
