<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types = 1);

namespace Magento\PageBuilderPwa\Setup\Converters;

use Magento\Cms\Model\Template\Filter;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PageBuilderAddImageDimensionsTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var MockObject
     */
    private $filterProviderMock;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $pageFilterMock = $this->createMock(Filter::class);
        $pageFilterMock->expects($this->any())
            ->method('filter')
            ->willReturnCallback(function ($mediaDirective) {
                preg_match('/{{media url=(.*)}}/', $mediaDirective, $matches);
                return __DIR__ . "/../_files/" . $matches[1];
            });
        $this->filterProviderMock = $this->createMock(FilterProvider::class);
        $this->filterProviderMock->expects($this->any())->method('getPageFilter')->willReturn($pageFilterMock);
    }

    /**
     * Test Batch Conversion of Page Builder Content
     *
     * @dataProvider conversionData
     * @param string $input
     * @param string $expected
     */
    public function testConvert(string $input, string $expected)
    {
        $convertPageBuilderAddImageDimensionsMock = $this->objectManager
            ->create(
                PageBuilderAddImageDimensions::class,
                ['filterProvider' => $this->filterProviderMock]
            );

        $result = $convertPageBuilderAddImageDimensionsMock->convert($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function conversionData(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            [
                // Basic Image Tag with height > width;
                '<figure data-content-type="image"><img src="{{media url=images/150x200.jpg}}"></figure>',
                '<figure data-content-type="image"><img src="{{media url=images/150x200.jpg}}" data-image-dimensions="{&quot;height&quot;:200,&quot;width&quot;:150,&quot;ratio&quot;:1.33}"></figure>'
            ],
            [
                // Basic Image Tag with height < width;
                '<figure data-content-type="image"><img src="{{media url=images/480x360.jpg}}"></figure>',
                '<figure data-content-type="image"><img src="{{media url=images/480x360.jpg}}" data-image-dimensions="{&quot;height&quot;:360,&quot;width&quot;:480,&quot;ratio&quot;:0.75}"></figure>'
            ],
            [
                // Basic Image Tag with height = width;
                '<figure data-content-type="image"><img src="{{media url=images/150x150.jpg}}"></figure>',
                '<figure data-content-type="image"><img src="{{media url=images/150x150.jpg}}" data-image-dimensions="{&quot;height&quot;:150,&quot;width&quot;:150,&quot;ratio&quot;:1}"></figure>'
            ],
            [
                // Full template of stylized row with a styled desktop and mobile image inside.
                '<style>#html-body [data-pb-style=ABCDEFG]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;border-color:#0000fe;margin:8px 7px 6px 5px;padding:4px 3px 2px 1px}#html-body [data-pb-style=AAAAAAA]{margin:1px 2px 3px 4px;padding:5px 6px 7px 8px;border-style:none}#html-body [data-pb-style=7654321],#html-body [data-pb-style=1234567]{border-color:#fc0009;max-width:100%;height:auto}@media only screen and (max-width: 768px) { #html-body [data-pb-style=AAAAAAA]{border-style:none} }</style><div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="ABCDEFG"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="AAAAAAA"><img class="pagebuilder-mobile-hidden" src="{{media url=images/480x360.jpg}}" alt="" title="" data-element="desktop_image" data-pb-style="1234567"><img class="pagebuilder-mobile-only" src="{{media url=images/150x200.jpg}}" alt="" title="" data-element="mobile_image" data-pb-style="7654321"></figure></div></div>',
                '<style>#html-body [data-pb-style=ABCDEFG]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;border-color:#0000fe;margin:8px 7px 6px 5px;padding:4px 3px 2px 1px}#html-body [data-pb-style=AAAAAAA]{margin:1px 2px 3px 4px;padding:5px 6px 7px 8px;border-style:none}#html-body [data-pb-style=7654321],#html-body [data-pb-style=1234567]{border-color:#fc0009;max-width:100%;height:auto}@media only screen and (max-width: 768px) { #html-body [data-pb-style=AAAAAAA]{border-style:none} }</style><div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="ABCDEFG"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="AAAAAAA"><img class="pagebuilder-mobile-hidden" src="{{media url=images/480x360.jpg}}" alt="" title="" data-element="desktop_image" data-pb-style="1234567" data-image-dimensions="{&quot;height&quot;:360,&quot;width&quot;:480,&quot;ratio&quot;:0.75}"><img class="pagebuilder-mobile-only" src="{{media url=images/150x200.jpg}}" alt="" title="" data-element="mobile_image" data-pb-style="7654321" data-image-dimensions="{&quot;height&quot;:200,&quot;width&quot;:150,&quot;ratio&quot;:1.33}"></figure></div></div>'
            ],
            [
                // Full template of stylized row with a styled desktop and skip malformed mobile image.
                '<style>#html-body [data-pb-style=ABCDEFG]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;border-color:#0000fe;margin:8px 7px 6px 5px;padding:4px 3px 2px 1px}#html-body [data-pb-style=AAAAAAA]{margin:1px 2px 3px 4px;padding:5px 6px 7px 8px;border-style:none}#html-body [data-pb-style=7654321],#html-body [data-pb-style=1234567]{border-color:#fc0009;max-width:100%;height:auto}@media only screen and (max-width: 768px) { #html-body [data-pb-style=AAAAAAA]{border-style:none} }</style><div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="ABCDEFG"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="AAAAAAA"><img class="pagebuilder-mobile-hidden" src="{{media url=images/480x360.jpg}}" alt="" title="" data-element="desktop_image" data-pb-style="1234567"><img class="pagebuilder-mobile-only" src="{{media url=images/does_not_exist.jpg}}" alt="" title="" data-element="mobile_image" data-pb-style="7654321"></figure></div></div>',
                '<style>#html-body [data-pb-style=ABCDEFG]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;border-color:#0000fe;margin:8px 7px 6px 5px;padding:4px 3px 2px 1px}#html-body [data-pb-style=AAAAAAA]{margin:1px 2px 3px 4px;padding:5px 6px 7px 8px;border-style:none}#html-body [data-pb-style=7654321],#html-body [data-pb-style=1234567]{border-color:#fc0009;max-width:100%;height:auto}@media only screen and (max-width: 768px) { #html-body [data-pb-style=AAAAAAA]{border-style:none} }</style><div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="ABCDEFG"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="AAAAAAA"><img class="pagebuilder-mobile-hidden" src="{{media url=images/480x360.jpg}}" alt="" title="" data-element="desktop_image" data-pb-style="1234567" data-image-dimensions="{&quot;height&quot;:360,&quot;width&quot;:480,&quot;ratio&quot;:0.75}"><img class="pagebuilder-mobile-only" src="{{media url=images/does_not_exist.jpg}}" alt="" title="" data-element="mobile_image" data-pb-style="7654321"></figure></div></div>'
            ]
        ];
        // phpcs:enable Generic.Files.LineLength.TooLong
    }
}
