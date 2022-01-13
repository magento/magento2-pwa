<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQlAux\Test\Unit\Plugin;

use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder\Price;
use Magento\CatalogGraphQlAux\Plugin\Price as PricePlugin;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PriceTest extends TestCase
{
    /**
     * @var string
     * @see \Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder\Price::PRICE_BUCKET
     */
    private const PRICE_BUCKET = 'price_bucket';

    /**
     * @var PricePlugin
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $eavConfigMock;

    protected function setUp(): void
    {
        $this->eavConfigMock = $this->createMock(Config::class);

        $this->model = new PricePlugin(
            $this->eavConfigMock
        );
    }

    public function testAfterBuildWithEmptyArray()
    {
        $priceMock = $this->createMock(Price::class);

        $this->assertEquals(
            [],
            $this->model->afterBuild($priceMock, [])
        );
    }

    public function testAfterBuildWithPriceAttribute()
    {
        $priceAttributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPosition'])
            ->getMock();

        $priceAttributeMock->expects($this->any())
            ->method('getPosition')
            ->willReturn(10);

        $this->eavConfigMock
            ->expects($this->once())
            ->method('getAttribute')
            ->willReturn($priceAttributeMock);

        $resultMock = [
            self::PRICE_BUCKET => [
                'attribute_code' => 'price',
                'position' => 1
            ]
        ];

        $priceMock = $this->createMock(Price::class);
        $expectedResult = [
            self::PRICE_BUCKET => [
                'attribute_code' => 'price',
                'position' => $priceAttributeMock->getPosition()
            ]
        ];

        $this->assertEquals(
            $expectedResult,
            $this->model->afterBuild($priceMock, $resultMock)
        );
    }
}
