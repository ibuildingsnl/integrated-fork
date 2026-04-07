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

    public function testNavdropdownsCacheBypassesInitialQueueAndAssignedLookups(): void
    {
        $controller = file_get_contents(__DIR__.'/../../Controller/ContentController.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString("\$cacheItem = \$cache->getItem('navdropdowns_'.md5(\$userId.'|'.\$request->getLocale()));", $controller);
        $this->assertStringContainsString("if (\$cacheItem->isHit()) {\n            return new Response((string) \$cacheItem->get());\n        }", $controller);
        $this->assertStringContainsString("'queuecount' => 0", $controller);
        $this->assertStringContainsString("'queuepercentage' => 100", $controller);
        $this->assertStringContainsString("'assignedContent' => []", $controller);
    }

    public function testAdminStatusPollersUseTimeoutSchedulingWithDeferredFirstPoll(): void
    {
        $navdropdownsTemplate = file_get_contents(__DIR__.'/../../Resources/views/content/navdropdowns.html.twig');
        $lockPollingScript = file_get_contents(__DIR__.'/../../Resources/public/js/content_index_lock_polling.js');

        $this->assertIsString($navdropdownsTemplate);
        $this->assertIsString($lockPollingScript);
        $this->assertStringContainsString('<ul class="header-nav">', $navdropdownsTemplate);
        $this->assertStringContainsString('data-assigned-poll-interval="30000"', $navdropdownsTemplate);
        $this->assertStringContainsString('data-queue-poll-interval="30000"', $navdropdownsTemplate);
        $this->assertStringContainsString('data-queue-idle-poll-interval="300000"', $navdropdownsTemplate);
        $this->assertStringContainsString('function scheduleAssignedStatus(delay)', $navdropdownsTemplate);
        $this->assertStringContainsString('function scheduleQueueStatus(delay)', $navdropdownsTemplate);
        $this->assertStringContainsString('scheduleAssignedStatus(assignedPollInterval);', $navdropdownsTemplate);
        $this->assertStringContainsString('scheduleQueueStatus(queuePollInterval);', $navdropdownsTemplate);
        $this->assertStringContainsString('var nextQueuePollDelay = queueIdlePollInterval;', $navdropdownsTemplate);
        $this->assertStringContainsString('nextQueuePollDelay = queueIdlePollInterval;', $navdropdownsTemplate);
        $this->assertStringContainsString('nextQueuePollDelay = queuePollInterval;', $navdropdownsTemplate);
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
