<?php
namespace Magento\CatalogGraphQlAux\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class RobotsConfig implements ResolverInterface
{
    protected $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function resolve($field, $context, $info, ?array $value = null, ?array $args = null)
    {
        return [
            "defaultRobots" => $this->scopeConfig->getValue(
                'design/search_engine_robots/default_robots',
                ScopeInterface::SCOPE_STORE
            ),
            "customInstructions" => $this->scopeConfig->getValue(
                'design/search_engine_robots/custom_instructions',
                ScopeInterface::SCOPE_STORE
            )
        ];
    }
}
