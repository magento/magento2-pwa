<?php
declare(strict_types=1);

namespace Magento\PageBuilderPwa\Plugin\Cms;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Model\BlockRepository;
use Magento\PageBuilderPwa\Setup\Converters\PageBuilderAddImageDimensions;
use Psr\Log\LoggerInterface;

class BlockSavePlugin
{
    /**
     * @var PageBuilderAddImageDimensions
     */
    private PageBuilderAddImageDimensions $converter;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    public function __construct(
        PageBuilderAddImageDimensions $converter,
        LoggerInterface $logger
    ) {
        $this->converter = $converter;
        $this->logger = $logger;
    }

    /**
     * Before save plugin — modify block content before saving
     *
     * @param BlockRepository $subject
     * @param BlockInterface $block
     * @return array
     */
    public function beforeSave(BlockRepository $subject, BlockInterface $block): array
    { 
        try {
            $originalContent = $block->getContent();
            if ($originalContent) {
                $converted = $this->converter->convert($originalContent);
                $block->setContent($converted);
            }
        } catch (\Throwable $e) {
            $this->logger->error('[PageBuilderAddImageDimensions] Error during block save: ' . $e->getMessage());
        }

        return [$block];
    }
}
