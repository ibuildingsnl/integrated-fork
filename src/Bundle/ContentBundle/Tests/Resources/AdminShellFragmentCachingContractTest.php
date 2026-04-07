<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class AdminShellFragmentCachingContractTest extends TestCase
{
    public function testChannelFragmentUsesFilesystemCacheWithSessionAwareKey(): void
    {
        $controller = file_get_contents(__DIR__.'/../../Controller/ChannelController.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString("private const CHANNELS_CACHE_NAMESPACE = 'integrated_content_fragments_channels';", $controller);
        $this->assertStringContainsString("\$sessionId = \$request->hasSession() ? (string) \$request->getSession()->getId() : '';", $controller);
        $this->assertStringContainsString("'session' => \$sessionId", $controller);
        $this->assertStringContainsString('new FilesystemAdapter(self::CHANNELS_CACHE_NAMESPACE)', $controller);
        $this->assertStringContainsString('$cacheItem->isHit()', $controller);
    }

    public function testNavdropdownsRenderUsesFreshQueueStatusAndAssignedStatusPayload(): void
    {
        $controller = file_get_contents(__DIR__.'/../../Controller/ContentController.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString('$assignedStatus = $this->getAssignedStatusPayload();', $controller);
        $this->assertStringContainsString('$queueStatus = $this->isGranted(\'ROLE_ADMIN\')', $controller);
        $this->assertStringContainsString('$this->getQueueStatus($request)', $controller);
        $this->assertStringContainsString("'queuecount' => \$queueStatus['queuecount']", $controller);
        $this->assertStringContainsString("'queuepercentage' => \$queueStatus['queuepercentage']", $controller);
        $this->assertStringContainsString("'assignedContent' => \$assignedStatus['items']", $controller);
        $this->assertStringNotContainsString("\$cacheItem = \$cache->getItem('navdropdowns_'.md5(\$userId.'|'.\$request->getLocale()));", $controller);
    }

    public function testTopbarNavdropdownsFragmentIsNotTurboPermanent(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/base.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('<div id="topbar-navdropdowns-fragment"', $template);
        $this->assertStringContainsString('<div class="sidebar-menu close-outside" data-turbo-prefetch="false">', $template);
        $this->assertStringNotContainsString('<div id="topbar-navdropdowns-fragment"
                         data-turbo-permanent>', $template);
        $this->assertStringNotContainsString('id="topbar-navdropdowns-fragment"
                         data-turbo-permanent', $template);
    }

    public function testAdminTopbarNoLongerPollsAssignedOrQueueStatus(): void
    {
        $navdropdownsTemplate = file_get_contents(__DIR__.'/../../Resources/views/content/navdropdowns.html.twig');
        $lockPollingScript = file_get_contents(__DIR__.'/../../Resources/public/js/content_index_lock_polling.js');

        $this->assertIsString($navdropdownsTemplate);
        $this->assertIsString($lockPollingScript);
        $this->assertStringContainsString('<ul class="header-nav">', $navdropdownsTemplate);
        $this->assertStringContainsString("{% if is_granted('ROLE_ADMIN') and queuecount > 0 %}", $navdropdownsTemplate);
        $this->assertStringNotContainsString("{% if is_granted('ROLE_ADMIN') %}", $navdropdownsTemplate);
        $this->assertStringNotContainsString('{% if queuecount == 0 %}hidden{% endif %}', $navdropdownsTemplate);
        $this->assertStringNotContainsString('data-assigned-status-url=', $navdropdownsTemplate);
        $this->assertStringNotContainsString('data-assigned-poll-interval=', $navdropdownsTemplate);
        $this->assertStringNotContainsString('function scheduleAssignedStatus(delay)', $navdropdownsTemplate);
        $this->assertStringNotContainsString('pollAssignedStatus()', $navdropdownsTemplate);
        $this->assertStringNotContainsString('data-queue-status-url=', $navdropdownsTemplate);
        $this->assertStringNotContainsString('data-queue-poll-interval=', $navdropdownsTemplate);
        $this->assertStringNotContainsString('data-queue-idle-poll-interval=', $navdropdownsTemplate);
        $this->assertStringNotContainsString('function scheduleQueueStatus(delay)', $navdropdownsTemplate);
        $this->assertStringNotContainsString('pollQueueStatus()', $navdropdownsTemplate);
        $this->assertStringContainsString('var POLL_DELAY = 30000;', $lockPollingScript);
        $this->assertStringContainsString('var INITIAL_POLL_DELAY = 30000;', $lockPollingScript);
        $this->assertStringContainsString("info.setAttribute('data-turbo-temporary', 'true');", $lockPollingScript);
        $this->assertStringContainsString('var clearTemporaryLocks = function () {', $lockPollingScript);
        $this->assertStringContainsString("slot.querySelectorAll('[data-turbo-temporary=\"true\"]')", $lockPollingScript);
        $this->assertStringContainsString("clearTemporaryLocks();\n            stop();", $lockPollingScript);
        $this->assertStringContainsString('scheduleNext(INITIAL_POLL_DELAY);', $lockPollingScript);
        $this->assertStringNotContainsString("document.addEventListener('DOMContentLoaded', initContentIndexLockPolling);", $lockPollingScript);
    }
}
