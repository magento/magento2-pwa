<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQlAux\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Search\Model\Query;

class SearchTerm implements ResolverInterface
{
    /**
     * @var Query
     */
    protected $query;

    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null) 
    {
        $result = $this->query->loadByQueryText($args["Search"]);
        return $result->getData();
    }
}
