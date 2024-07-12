<?php

/**
 * Customer Token Lifetime resolver, used for GraphQL request processing.
 */
namespace Magento\CustomerGraphQlPwa\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Integration\Helper\Oauth\Data as OauthHelper;

/**
 * Customer Token Lifetime for PWA, used for GraphQL request processing.
 */
class customerTokenLifetime implements ResolverInterface
{
    /**
     * @var OauthHelper
     */
    private $oauthHelper;

    /**
     * CustomerTokenLifetime constructor.
     *
     * @param OauthHelper $oauthHelper
     */
    public function __construct(OauthHelper $oauthHelper)
    {
        $this->oauthHelper = $oauthHelper;
    }

    /**
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed
     */
    public function resolve( 
        Field $field, 
        $context, 
        ResolveInfo $info, 
        array $value = null, 
        array $args = null
    ){
        return $this->oauthHelper->getCustomerTokenLifetime() * 3600;
    }
}
