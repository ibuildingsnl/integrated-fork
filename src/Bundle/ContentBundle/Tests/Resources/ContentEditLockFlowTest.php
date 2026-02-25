<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class ContentEditLockFlowTest extends TestCase
{
    public function testRoutingContainsLockAcquireEndpoint(): void
    {
        $routing = file_get_contents(__DIR__.'/../../Resources/config/routing/content.xml');

        $this->assertIsString($routing);
        $this->assertStringContainsString('id="integrated_content_content_lock"', $routing);
        $this->assertStringContainsString('path="/{id}/lock" methods="POST"', $routing);
    }

    public function testControllerContainsLockAcquireActionAndPendingLockFlow(): void
    {
        $controller = file_get_contents(__DIR__.'/../../Controller/ContentController.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString('public function lock(Request $request, string $id): JsonResponse', $controller);
        $this->assertStringContainsString('$request->query->has(\'lock\')', $controller);
        $this->assertStringContainsString("get('known_lock'", $controller);
        $this->assertStringContainsString('elseif ($requestedLockId === $currentLockId)', $controller);
        $this->assertStringContainsString("get('_token'", $controller);
        $this->assertStringContainsString('$this->isCsrfTokenValid(\'integrated_content_lock_\'', $controller);
        $this->assertStringContainsString('The lock request token is invalid. Please reload and try again.', $controller);
        $this->assertStringContainsString('$submittedAction = (string) ($form->get(\'actions\')->getData() ?? \'\');', $controller);
        $this->assertStringContainsString('foreach ([\'cancel\', \'back\', \'reload\', \'save\', \'reload_changed\'] as $candidate)', $controller);
        $this->assertStringContainsString('$parameters = array_merge($request->query->all(), [\'id\' => $content->getId()]);', $controller);
        $this->assertStringContainsString('$parameters[\'lock\'] = $locking[\'lock\']->getId();', $controller);
        $this->assertStringContainsString("'data-content-locked' => (\$locking['locked'] && !(\$locking['pending'] ?? false)) ? '1' : '0'", $controller);
        $this->assertStringContainsString('$hasUsableLock = $locking[\'lock\'] && !($locking[\'locked\'] ?? false);', $controller);
        $this->assertStringContainsString('$reloadSubmitted = $request instanceof Request && $this->isSubmittedAction($request, \'reload\');', $controller);
        $this->assertStringContainsString('return $form->add(\'actions\', ActionsType::class, [\'buttons\' => [\'reload\', \'save\', \'cancel\']]);', $controller);
        $this->assertStringContainsString('private function isSubmittedAction(Request $request, string $action): bool', $controller);
        $this->assertStringContainsString('$this->getExistingLock($content)', $controller);
        $this->assertStringContainsString('$this->createPendingLocking()', $controller);
    }

    public function testEditTemplateInitializesLockAfterTurboLoad(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/edit.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('data-lock-pending', $template);
        $this->assertStringContainsString('data-lock-init-url', $template);
        $this->assertStringContainsString('integrated-content-lock:', $template);
        $this->assertStringContainsString("csrf_token('integrated_content_lock_' ~ content.id)", $template);
        $this->assertStringContainsString("requestBody.set('_token', LOCK_CSRF_TOKEN);", $template);
        $this->assertStringContainsString('window.__integratedLockPolling', $template);
        $this->assertStringContainsString('data-content-lock-overlay', $template);
        $this->assertStringContainsString('setLockOverlay(form,', $template);
        $this->assertStringContainsString('document.addEventListener(\'turbo:load\'', $template);
        $this->assertStringContainsString('window.history.replaceState', $template);
        $this->assertStringContainsString('startLockPolling', $template);
    }

    public function testIframeTemplateSendsCsrfTokenForLockAcquireRequest(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/edit.iframe.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString("csrf_token('integrated_content_lock_' ~ content.id)", $template);
        $this->assertStringContainsString("requestBody.set('_token', LOCK_CSRF_TOKEN);", $template);
    }
}
