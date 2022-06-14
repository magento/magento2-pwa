<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewriteGraphQlPwa\Test\Unit\Plugin\UrlResolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewriteGraphQl\Model\DataProvider\EntityDataProviderComposite;
use Magento\UrlRewriteGraphQlPwa\Plugin\UrlResolver\RoutePlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\UrlRewriteGraphQl\Model\Resolver\Route;

class RoutePluginTest extends TestCase
{
    private const URL_FROM = 'test-route';

    private const URL_TO = 'test-redirect';

    /** @var UrlFinderInterface|MockObject */
    private $urlFinder;

    /** @var StoreRepositoryInterface|MockObject */
    private $storeRepository;

    /** @var EntityDataProviderComposite|MockObject */
    private $entityDataProvider;

    /** @var RoutePlugin */
    private $routePlugin;

    protected function setUp() : void
    {
        $this->urlFinder = $this->createMock(UrlFinderInterface::class);
        $this->storeRepository = $this->createMock(StoreRepositoryInterface::class);
        $this->entityDataProvider = $this->createMock(EntityDataProviderComposite::class);

        $this->routePlugin = new RoutePlugin(
            $this->urlFinder,
            $this->storeRepository,
            $this->entityDataProvider
        );
    }

    public function testReturnsExistingResult()
    {
        $context = $this->getMockContext();
        $previousResult = ['foo' => 'bar'];

        $result = $this->callPlugin($previousResult, $context);

        $this->storeRepository->expects($this->never())
            ->method('getList');
        $this->urlFinder->expects($this->never())
            ->method('findOneByData');
        $this->entityDataProvider->expects($this->never())
            ->method('getData');

        $this->assertEquals($previousResult, $result);
    }

    public function testReturnsNullWhenNoStores()
    {
        // Test returns null if no other stores
        $context = $this->getMockContext();
        $previousResult = null;

        $this->setAvailableStores([1]);

        $result = $this->callPlugin($previousResult, $context);

        $this->urlFinder->expects($this->never())
            ->method('findOneByData');
        $this->entityDataProvider->expects($this->never())
            ->method('getData');

        $this->assertNull($result);
    }

    public function testReturnsNullWhenNotFound()
    {
        // Test returns null if no matching URL found
        $context = $this->getMockContext();
        $previousResult = null;

        $otherStoreId = 2;
        $this->setAvailableStores([1, $otherStoreId]);

        $this->urlFinder
            ->method('findOneByData')
            ->with([
                UrlRewrite::REQUEST_PATH => self::URL_FROM,
                UrlRewrite::STORE_ID => $otherStoreId,
            ])
            ->willReturn(null);

        $this->entityDataProvider->expects($this->never())
            ->method('getData');

        $result = $this->callPlugin($previousResult, $context);

        $this->assertNull($result);
    }

    public function testReturnsNullWhenNoRedirect()
    {
        // Test returns null if no matching URL found
        $context = $this->getMockContext();
        $previousResult = null;

        $otherStoreId = 2;
        $currentStoreId = 1;
        $this->setAvailableStores([$currentStoreId, $otherStoreId]);

        $entityId = 5;
        $entityType = 'product';
        $entityTargetPath = '/catalog/product/view/8';
        $entityRequestPath = self::URL_FROM;
        $this->urlFinder->expects($this->any())
            ->method('findOneByData')
            ->will(
                $this->returnValueMap(
                    [
                        [
                            [
                                UrlRewrite::REQUEST_PATH => self::URL_FROM,
                                UrlRewrite::STORE_ID => $otherStoreId
                            ],
                            $this->getMockUrlRewrite(
                                $entityId,
                                $entityType,
                                $entityTargetPath,
                                $entityRequestPath
                            )
                        ],
                        [
                            [
                                UrlRewrite::ENTITY_ID => $entityId,
                                UrlRewrite::ENTITY_TYPE => $entityType,
                                UrlRewrite::TARGET_PATH => $entityTargetPath,
                                UrlRewrite::STORE_ID => $currentStoreId
                            ],
                            null
                        ]
                    ]
                )
            );

        $this->entityDataProvider->expects($this->never())
            ->method('getData');

        $result = $this->callPlugin($previousResult, $context);

        $this->assertNull($result);
    }

    public function testReturnsRedirect()
    {
        // Test returns redirect structure
        $context = $this->getMockContext();
        $previousResult = null;

        $otherStoreId = 2;
        $currentStoreId = 1;
        $this->setAvailableStores([$currentStoreId, $otherStoreId]);

        $entityId = 5;
        $entityType = 'cms-page';
        $entityTargetPath = '/cms/page/view/8';
        $entityRequestPath = self::URL_FROM;

        $this->urlFinder->expects($this->any())
            ->method('findOneByData')
            ->will(
                $this->returnValueMap(
                    [
                        [
                            [
                                UrlRewrite::REQUEST_PATH => self::URL_FROM,
                                UrlRewrite::STORE_ID => $otherStoreId
                            ],
                            $this->getMockUrlRewrite(
                                $entityId,
                                $entityType,
                                $entityTargetPath,
                                $entityRequestPath
                            )
                        ],
                        [
                            [
                                UrlRewrite::ENTITY_ID => $entityId,
                                UrlRewrite::ENTITY_TYPE => $entityType,
                                UrlRewrite::TARGET_PATH => $entityTargetPath,
                                UrlRewrite::STORE_ID => $currentStoreId
                            ],
                            $this->getMockUrlRewrite(
                                $entityId,
                                $entityType,
                                $entityTargetPath,
                                self::URL_TO
                            )
                        ]
                    ]
                )
            );

        $this->entityDataProvider->expects($this->once())
            ->method('getData')
            ->willReturnCallback(function ($type, $id) {
                return [
                    'type_id' => $type,
                    'entity_id' => $id,
                    'some_additional' => 'foo'
                ];
            });

        $result = $this->callPlugin($previousResult, $context);

        $this->assertEquals([
            'type_id' => 'CMS_PAGE',
            'entity_id' => 5,
            'some_additional' => 'foo',
            'type' => 'CMS_PAGE',
            'redirect_code' => 302,
            'relative_url' => self::URL_TO
        ], $result);
    }

    /**
     * Call the RoutePlugin after resolve
     *
     * @param array|null $result
     * @param ContextInterface|MockObject $context
     * @return array|null
     */
    private function callPlugin($result, $context): ?array
    {

        $field = $this->createMock(Field::class);
        $info = $this->createMock(ResolveInfo::class);
        $route = $this->createMock(Route::class);

        return $this->routePlugin->afterResolve(
            $route,
            $result,
            $field,
            $context,
            $info,
            null,
            [
                'url' => self::URL_FROM
            ]
        );
    }

    /**
     * Generate mock context and current store
     *
     * @param int $currentStoreId
     * @return ContextInterface|mixed|MockObject
     */
    private function getMockContext(int $currentStoreId = 1)
    {
        $currentStore = $this->createMock(StoreInterface::class);
        $currentStore
            ->method('getId')
            ->willReturn($currentStoreId);

        $extensionAttributes = $this->getMockBuilder(ContextExtensionInterface::class)
            ->addMethods(['getStore'])
            ->getMockForAbstractClass();

        $extensionAttributes
            ->method('getStore')
            ->willReturn($currentStore);

        $context = $this->createMock(ContextInterface::class);
        $context->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        return $context;
    }

    /**
     * Set available store IDs
     *
     * @param int[] $availableStoreIds
     */
    private function setAvailableStores(array $availableStoreIds)
    {
        $stores = [];
        foreach ($availableStoreIds as $availableStoreId) {
            $store = $this->createMock(StoreInterface::class);
            $store
                ->method('getId')
                ->willReturn($availableStoreId);
            $store
                ->method('getIsActive')
                ->willReturn(true);

            $stores[] = $store;
        }

        $this->storeRepository
            ->method('getList')
            ->willReturn($stores);
    }

    /**
     * Generate a mock UrlRewrite
     *
     * @param int $id
     * @param string $type
     * @param string $targetPath
     * @param string $requestPath
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite|mixed|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getMockUrlRewrite(int $id, string $type, string $targetPath, string $requestPath)
    {
        $urlRewrite = $this->createMock(UrlRewrite::class);
        $urlRewrite->method('getEntityId')->willReturn($id);
        $urlRewrite->method('getEntityType')->willReturn($type);
        $urlRewrite->method('getTargetPath')->willReturn($targetPath);
        $urlRewrite->method('getRequestPath')->willReturn($requestPath);

        return $urlRewrite;
    }
}
