<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQlAux\Test\Unit\Plugin;

use Magento\CatalogGraphQl\Model\Resolver\Aggregations;
use Magento\CatalogGraphQlAux\Plugin\Aggregations as AggregationsPlugin;
use Magento\Eav\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AggregationsTest extends TestCase
{
    /**
     * @var string
     */
    private const CATEGORY_CODE = 'category_id';

    /**
     * @var AggregationsPlugin
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $eavConfigMock;

    protected function setUp(): void
    {
        $this->eavConfigMock = $this->createMock(Config::class);

        $this->model = new AggregationsPlugin();
    }

    public function testAfterResolveWithoutAttributes()
    {
        $aggregationsMock = $this->createMock(Aggregations::class);

        $this->assertEquals(
            null,
            $this->model->afterResolve($aggregationsMock, null)
        );
    }

    public function testAfterResolveWithAttributes()
    {
        $resultMock = [
            [
                'attribute_code' => 'attribute_c',
                'position' => 20,
                'label' => 'C'
            ],
            [
                'attribute_code' => 'attribute_b',
                'position' => 10,
                'label' => 'B'
            ],
            [
                'attribute_code' => 'attribute_d',
                'position' => 5,
                'label' => 'D'
            ],
            [
                'attribute_code' => 'attribute_a',
                'position' => 10,
                'label' => 'A'
            ],
            [
                'attribute_code' => self::CATEGORY_CODE,
                'label' => 'Category'
            ]
        ];

        $aggregationsMock = $this->createMock(Aggregations::class);
        $expectedResult = [
            [
                'attribute_code' => self::CATEGORY_CODE,
                'label' => 'Category'
            ],
            [
                'attribute_code' => 'attribute_d',
                'position' => 5,
                'label' => 'D'
            ],
            [
                'attribute_code' => 'attribute_a',
                'position' => 10,
                'label' => 'A'
            ],
            [
                'attribute_code' => 'attribute_b',
                'position' => 10,
                'label' => 'B'
            ],
            [
                'attribute_code' => 'attribute_c',
                'position' => 20,
                'label' => 'C'
            ]
        ];

        $this->assertEquals(
            $expectedResult,
            $this->model->afterResolve($aggregationsMock, $resultMock)
        );
    }
}
