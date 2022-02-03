<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQlAux\Test\Unit\Plugin\Model\Resolver\Category;

use Magento\CatalogGraphQl\Model\Resolver\Category\SortFields;
use Magento\CatalogGraphQlAux\Plugin\Model\Resolver\Category\SortFields as SortFieldsPlugin;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SortFieldsTest extends TestCase
{
    /**
     * @var SortFieldsPlugin
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $eavConfigMock;

    protected function setUp(): void
    {
        $this->eavConfigMock = $this->createMock(Config::class);

        $this->model = new SortFieldsPlugin(
            $this->eavConfigMock
        );
    }

    public function testAfterResolveWithEmptyResult()
    {
        $sortFieldsMock = $this->createMock(SortFields::class);

        $resultMock = [];

        $this->assertEquals(
            $resultMock,
            $this->model->afterResolve($sortFieldsMock, $resultMock)
        );
    }

    public function testAfterResolveWithData()
    {
        $attributeValue = 'attribute_code';
        $attributeAdminLabel = 'Admin Label';
        $attributeStoreLabel = 'Store Label';

        $attribute = $this->getMockBuilder(AbstractAttribute::class)
            ->addMethods(['getStoreLabel'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attribute->expects($this->any())
            ->method('getStoreLabel')
            ->willReturn($attributeStoreLabel);

        $this->eavConfigMock->expects($this->atLeastOnce())
            ->method('getAttribute')
            ->willReturn($attribute);

        $expectedResult = [
            'options' => [
                [
                    'label' => $attributeStoreLabel,
                    'value' => $attributeValue
                ]
            ]
        ];

        $sortFieldsMock = $this->createMock(SortFields::class);

        $resultMock = [
            'options' => [
                [
                    'label' => $attributeAdminLabel,
                    'value' => $attributeValue
                ]
            ]
        ];

        $this->assertEquals(
            $expectedResult,
            $this->model->afterResolve($sortFieldsMock, $resultMock)
        );
    }
}
