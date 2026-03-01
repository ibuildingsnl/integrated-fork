<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ToolbarCssContractTest extends TestCase
{
    public function testDefinesToolbarCssVariablesAndWebsiteListContainer(): void
    {
        $baseDir = __DIR__.'/../../Resources/views/themes/default/objects';
        $entryPath = $baseDir.'/toolbar-css.html.twig';
        $partialPaths = [
            $baseDir.'/toolbar-css/_base.css.twig',
            $baseDir.'/toolbar-css/_toolbar-shell.css.twig',
            $baseDir.'/toolbar-css/_editor-menu-account.css.twig',
            $baseDir.'/toolbar-css/_toolbar-responsive.css.twig',
        ];

        $entryContent = (string) file_get_contents($entryPath);
        $partialsContent = '';
        foreach ($partialPaths as $partialPath) {
            $partialsContent .= (string) file_get_contents($partialPath);
        }

        self::assertStringContainsString("include integrated_active_theme('objects/toolbar-css/_base.css.twig')", $entryContent);
        self::assertStringContainsString('--integrated-toolbar-height: 44px;', $partialsContent);
        self::assertStringContainsString('--integrated-toolbar-primary: #00ae93;', $partialsContent);
        self::assertStringContainsString('top: var(--integrated-toolbar-top-offset);', $partialsContent);
        self::assertStringContainsString('height: var(--integrated-toolbar-height);', $partialsContent);
        self::assertStringContainsString('.integrated-toolbar-websites {', $partialsContent);
        self::assertStringContainsString('body.integrated-sortable-active', $partialsContent);
        self::assertStringContainsString('.integrated-grid-drop-target', $partialsContent);
        self::assertStringContainsString('.integrated-menu-drop-before', $partialsContent);
        self::assertStringContainsString('.integrated-editor-commandbar-group-viewport', $partialsContent);
        self::assertStringContainsString('body.integrated-editor-preview-tablet', $partialsContent);
        self::assertStringContainsString('body.integrated-editor-preview-mobile', $partialsContent);
        self::assertStringContainsString('.integrated-editor-device-preview', $partialsContent);
        self::assertStringContainsString('.integrated-website-save-status[data-state="dirty"]', $partialsContent);
        self::assertStringContainsString('.integrated-website-toolbar-button.is-disabled', $partialsContent);
        self::assertStringContainsString('.integrated-website-toolbar a:focus-visible', $partialsContent);
    }
}
