<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ContactGraphQlPwa\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class ProductCanonicalConfig implements ResolverInterface
{
    /**
     * Product canonical configuration path
     */
    public const PRODUCT_CANONICAL_CONFIG = 'catalog/seo/product_canonical_tag';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        $store = $context->getExtensionAttributes()->getStore();
        $productCanonicalConfig = $this->scopeConfig->isSetFlag(
            self::PRODUCT_CANONICAL_CONFIG,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );

        return $productCanonicalConfig;
    }
}
