<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types = 1);

namespace Magento\PageBuilderPwa\Setup\Patch\Data;

use Magento\Framework\DB\FieldDataConversionException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\PageBuilder\Setup\Patch\Data\UpgradePageBuilderStripStyles;
use Magento\PageBuilderPwa\Setup\Converters\PageBuilderAddImageDimensions;
use Magento\PageBuilder\Setup\UpgradeContentHelper;

/**
 * Patch Upgrade Mechanism for Converting Inline Styles to Internal
 */
class UpgradePageBuilderAddImageDimensions implements DataPatchInterface
{
    /**
     * @var UpgradeContentHelper
     */
    private $helper;

    /**
     * @param UpgradeContentHelper $helper
     */
    public function __construct(UpgradeContentHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Upgrade
     *
     * @return void
     * @throws FieldDataConversionException
     */
    public function apply(): void
    {
        $this->helper->upgrade([
            PageBuilderAddImageDimensions::class
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [
            UpgradePageBuilderStripStyles::class
        ];
    }
}
