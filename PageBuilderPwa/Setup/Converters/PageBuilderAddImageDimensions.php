<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types = 1);

namespace Magento\PageBuilderPwa\Setup\Converters;

use Exception;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\DB\DataConverter\DataConverterInterface;
use Magento\Framework\Filesystem\Driver\Http;
use Magento\PageBuilder\Model\Dom\HtmlDocumentFactory;

/**
 * Convert Inline Styles to Internal
 */
class PageBuilderAddImageDimensions implements DataConverterInterface
{
    /**
     * @var FilterProvider
     */
    private $filterProvider;

    /**
     * @var HtmlDocumentFactory
     */
    private $htmlDocumentFactory;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var String
     */
    private $value;

    /**
     * @var Http
     */
    private $http;

    /**
     * @param HtmlDocumentFactory $htmlDocumentFactory
     * @param FilterProvider $filterProvider
     * @param State $state
     * @param Http $http
     */
    public function __construct(
        HtmlDocumentFactory $htmlDocumentFactory,
        FilterProvider $filterProvider,
        State $state,
        Http $http
    ) {
        $this->htmlDocumentFactory = $htmlDocumentFactory;
        $this->filterProvider = $filterProvider;
        $this->appState = $state;
        $this->http = $http;
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function convert($value): string
    {
        $this->value = $value;
        return $this->appState->emulateAreaCode(
            Area::AREA_FRONTEND,
            function () {
                $document = $this->htmlDocumentFactory->create([ 'document' => $this->value ]);
                $nodes = $document->querySelectorAll('figure[data-content-type="image"] img[src]');
                if ($nodes->count() > 0) {
                    foreach ($nodes as $node) {
                        $srcAttr = $node->getAttribute('src');
                        try {
                            $srcUrl = $this->filterProvider->getPageFilter()->filter($srcAttr);
                            $imageContent = $this->http->fileGetContents($srcUrl);
                            list($width, $height) = getimagesizefromstring($imageContent);
                            $data = [
                                'height' => $height,
                                'width' => $width,
                                'ratio' => round($height/$width, 2)
                            ];
                            $node->setAttribute("data-image-dimensions", str_replace('"', '\'', json_encode($data)));
                        } catch (Exception $e) {
                            continue;
                        }
                    }
                    // Fetch style tag
                    preg_match('/<head>(.*)<\/head>/s', $document->saveHTML(), $matches);
                    $styles = "";
                    if ($matches) {
                        $styles = preg_replace_callback(
                            '/=\"(%7B%7B[^"]*%7D%7D)\"/m',
                            function ($matches) {
                                return urldecode($matches[0]);
                            },
                            $matches[1]
                        );
                    }
                    $content = preg_replace(
                        '/(data-image-dimensions="{)\'(height)\'(:\d*,)\'(width)\'(:\d*,)\'(ratio)\'/',
                        '$1&quot;$2&quot;$3&quot;$4&quot;$5&quot;$6&quot;',
                        $document->stripHtmlWrapperTags()
                    );
                    return $styles . $content;
                }
                return $this->value;
            }
        );
    }
}
