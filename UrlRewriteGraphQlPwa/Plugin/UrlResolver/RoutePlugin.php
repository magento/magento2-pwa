<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewriteGraphQlPwa\Plugin\UrlResolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewriteGraphQl\Model\DataProvider\EntityDataProviderComposite;
use Magento\UrlRewriteGraphQl\Model\Resolver\Route;

class RoutePlugin
{
    /** @var UrlFinderInterface */
    private $urlFinder;

    /** @var StoreRepositoryInterface */
    private $storeRepository;

    /** @var EntityDataProviderComposite */
    private $entityDataProviderComposite;

    /**
     * @param UrlFinderInterface $urlFinder
     * @param StoreRepositoryInterface $storeRepository
     * @param EntityDataProviderComposite $entityDataProviderComposite
     */
    public function __construct(
        UrlFinderInterface $urlFinder,
        StoreRepositoryInterface $storeRepository,
        EntityDataProviderComposite $entityDataProviderComposite
    ) {
        $this->urlFinder = $urlFinder;
        $this->storeRepository = $storeRepository;
        $this->entityDataProviderComposite = $entityDataProviderComposite;
    }

    /**
     * Determine if alternative locale URL is available for redirection
     *
     * @param Route $subject
     * @param array<scalar>|null $result
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array<scalar>|null $value
     * @param array<scalar>|null $args
     *
     * @return array<scalar>|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterResolve(
        Route $subject,
        ?array $result,
        Field $field,
        ContextInterface $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): ?array {
        if ($result !== null) {
            return $result;
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $urlParts = parse_url($args['url']);
        $url = $urlParts['path'] ?? $args['url'];

        if ($url !== '/' && strpos($url, '/') === 0) {
            $url = ltrim($url, '/');
        }

        $targetStoreId = (int) $context->getExtensionAttributes()->getStore()->getId();

        $otherStores = $this->getOtherStoreIds($targetStoreId);
        if (empty($otherStores)) {
            return null;
        }

        $validUrlEntity = $this->getUrlEntity($url, $otherStores);
        if ($validUrlEntity === null) {
            return null;
        }

        $targetStoreView = $this->urlFinder->findOneByData(
            [
                UrlRewrite::ENTITY_ID => $validUrlEntity->getEntityId(),
                UrlRewrite::ENTITY_TYPE => $validUrlEntity->getEntityType(),
                UrlRewrite::TARGET_PATH => $validUrlEntity->getTargetPath(),
                UrlRewrite::STORE_ID => $targetStoreId,
            ]
        );

        if ($targetStoreView === null) {
            return null;
        }

        $targetUrl = $targetStoreView->getRequestPath();

        $type = $this->sanitizeType($targetStoreView->getEntityType());

        $result = $this->entityDataProviderComposite->getData(
            $type,
            (int) $targetStoreView->getEntityId(),
            $info,
            $targetStoreId
        );

        $result['redirect_code'] = 302;
        $result['relative_url'] = $targetUrl;
        $result['type'] = $type;

        return $result;
    }

    /**
     * Get UrlRewrite entity
     *
     * @param string $url
     * @param int[] $storeIds
     * @return UrlRewrite|null
     */
    private function getUrlEntity(string $url, array $storeIds): ?UrlRewrite
    {
        $validUrlEntity = null;
        foreach ($storeIds as $storeId) {
            $validUrlEntity = $this->urlFinder->findOneByData(
                [
                    UrlRewrite::REQUEST_PATH => $url,
                    UrlRewrite::STORE_ID => $storeId,
                ]
            );

            if ($validUrlEntity !== null) {
                break;
            }
        }

        return $validUrlEntity;
    }

    /**
     * Get store IDs other than current
     *
     * @param int $currentStoreId
     * @return int[]
     */
    private function getOtherStoreIds(int $currentStoreId): array
    {
        $stores = $this->storeRepository->getList();
        $otherStores = [];

        foreach ($stores as $store) {
            $storeId = (int) $store->getId();

            if ($storeId === 0 || ($storeId === $currentStoreId) || !$store->getIsActive()) {
                continue;
            }

            $otherStores[] = $storeId;
        }

        return $otherStores;
    }

    /**
     * Sanitize the type to fit schema specifications
     *
     * @param string $type
     * @return string
     */
    private function sanitizeType(string $type): string
    {
        return strtoupper(str_replace('-', '_', $type));
    }
}
