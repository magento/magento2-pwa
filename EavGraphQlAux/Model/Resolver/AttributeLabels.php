<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQlAux\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * @inheritdoc
 */
class AttributeLabels implements ResolverInterface
{
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        StoreRepositoryInterface $storeRepository
    ) {
        $this->storeRepository = $storeRepository;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['attribute_labels'])) {
            throw new LocalizedException(__('"attribute_labels" value should be specified'));
        }
        $labels = $value['attribute_labels'];
        $labelsData = [];
        foreach ($labels as $label) {
            $labelsData[] = [
                'store_code' => $this->storeRepository->getById($label->getStoreId())->getCode(),
                'label' => $label->getLabel(),
            ];
        }

        return $labelsData;
    }
}
