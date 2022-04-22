<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\PageBuilderPwa;

use Magento\Framework\UrlInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class AttributeTypePageBuilderTest extends GraphQlAbstract
{
    /**
     * @var UrlInterface
     */
    protected $url;

    protected function setUp(): void
    {
        $this->url = Bootstrap::getObjectManager()->create(UrlInterface::class);
    }

    /**
     * Test that product custom attribute of pagebuilder type returns correct metadata and value
     *
     * @magentoApiDataFixture Magento/PageBuilderPwa/_files/product_simple_with_pagebuilder_attribute.php
     */
    public function testProductWithPageBuilderAttribute()
    {
        $baseMediaUrl = $this->url->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]);
        $productSku = 'simple';
        $query
            = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
    {
        items {
            sku
            custom_attributes {
                attribute_metadata {
                    uid
                    ui_input {
                        __typename
                        ui_input_type
                    }
                }
                entered_attribute_value {
                    __typename
                    value
                }
            }
        }
    }
}

QUERY;
        $response = $this->graphQlQuery($query);

        $actualAttributes = $response['products']['items'][0]['custom_attributes'][0];

        $this->assertEquals(
            'PAGEBUILDER',
            $actualAttributes['attribute_metadata']['ui_input']['ui_input_type']
        );
        $this->assertEquals(
            'UiAttributeTypePageBuilder',
            $actualAttributes['attribute_metadata']['ui_input']['__typename']
        );
        $this->assertEquals(
            '<img src="' . $baseMediaUrl . 'wysiwyg/image.jpg">',
            $actualAttributes['entered_attribute_value']['value']
        );
    }
}
