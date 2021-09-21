<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQlPwa\Model\Resolver\Query;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\Webapi\ServiceTypeToEntityTypeMap;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;

/**
 * Get frontend input type for EAV attribute
 */
class Attributes
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var ServiceTypeToEntityTypeMap
     */
    private $serviceTypeMap;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /** @var Uid */
    private $uidEncoder;

    /**
     * Comment
     *
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ServiceTypeToEntityTypeMap $serviceTypeMap
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ServiceTypeToEntityTypeMap $serviceTypeMap,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        Uid $uidEncoder
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->serviceTypeMap = $serviceTypeMap;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->uidEncoder = $uidEncoder;
    }

    /**
     * @param string $entityType
     * @param array $attributeUids
     * @param bool $showSystem
     * @return array|null
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlInputException
     */
    public function getAttributes(string $entityType, array $attributeUids = [], bool $showSystem = false): ?array
    {

        $mappedEntityType = $this->serviceTypeMap->getEntityType($entityType);

        if (!empty($attributeUids)) {
            $codes = array_map(function ($value) {
                return explode('/', $this->uidEncoder->decode($value))[1];
            }, $attributeUids);

            $this->searchCriteriaBuilder->addFilters(
                [
                    $this->filterBuilder
                        ->setField('attribute_code')
                        ->setConditionType('in')
                        ->setValue($codes)
                        ->create()
                ]
            );
        }

        if ($showSystem === false) {
            $this->searchCriteriaBuilder->addFilters(
                [
                    $this->filterBuilder
                        ->setField('is_user_defined')
                        ->setConditionType('eq')
                        ->setValue(1)
                        ->create()
                ]
            );
        }

        if ($mappedEntityType) {
            $entityType = $mappedEntityType;
        }
        try {
            $criteria = $this->searchCriteriaBuilder->create();
            $attributes = $this->attributeRepository->getList($entityType, $criteria)->getItems();
        } catch (NoSuchEntityException $e) {
            return null;
        }
        return $attributes;
    }
}
