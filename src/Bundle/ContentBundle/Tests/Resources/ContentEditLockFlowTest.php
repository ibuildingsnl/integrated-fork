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
        $this->assertStringContainsString('$submittedActionData = $form->get(\'actions\')->getData();', $controller);
        $this->assertStringContainsString('$submittedAction = $this->resolveSubmittedAction(', $controller);
        $this->assertStringContainsString('private function resolveSubmittedAction(mixed $submittedActionData, Request $request, array $candidates): string', $controller);
        $this->assertStringContainsString('$parameters = array_merge($request->query->all(), [\'id\' => $content->getId()]);', $controller);
        $this->assertStringContainsString('if (!$locking[\'locked\'] && $locking[\'lock\'] && $locking[\'owner\']) {', $controller);
        $this->assertStringContainsString('$parameters[\'lock\'] = $locking[\'lock\']->getId();', $controller);
        $this->assertStringContainsString('unset($parameters[\'lock\']);', $controller);
        $this->assertStringContainsString('$hasUsableLock = $locking[\'lock\'] && !$locking[\'locked\'];', $controller);
        $this->assertStringContainsString("'data-content-locked' => (\$locking['locked'] && !\$locking['pending']) ? '1' : '0'", $controller);
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
        $this->assertStringContainsString('{% if content is defined %}', $template);
        $this->assertStringContainsString('data-lock-pending', $template);
        $this->assertStringContainsString('data-lock-init-url', $template);
        $this->assertStringContainsString('data-lock-csrf', $template);
        $this->assertStringContainsString("content is defined ? csrf_token('integrated_content_lock_' ~ content.id) : ''", $template);
        $this->assertStringContainsString('integrated-content-lock:', $template);
        $this->assertStringContainsString("var lockCsrfToken = form.getAttribute('data-lock-csrf') || '';", $template);
        $this->assertStringContainsString("requestBody.set('_token', lockCsrfToken);", $template);
        $this->assertStringContainsString('window.__integratedLockPolling', $template);
        $this->assertStringContainsString('data-content-lock-overlay', $template);
        $this->assertStringContainsString('setLockOverlay(form,', $template);
        $this->assertStringContainsString("var flashContainer = document.getElementById('flash-messages');", $template);
        $this->assertStringContainsString('var normalizedMessage = String(message).trim();', $template);
        $this->assertStringContainsString('if (alerts[i].textContent && alerts[i].textContent.trim() === normalizedMessage)', $template);
        $this->assertStringContainsString('if (result.status !== 423) {', $template);
        $this->assertStringContainsString('flashContainer.appendChild(alertNode);', $template);
        $this->assertStringContainsString('document.addEventListener(\'turbo:load\'', $template);
        $this->assertStringContainsString('window.history.replaceState', $template);
        $this->assertStringContainsString('startLockPolling', $template);
    }

    public function testIframeTemplateSendsCsrfTokenForLockAcquireRequest(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/edit.iframe.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('data-lock-csrf', $template);
        $this->assertStringContainsString("var lockCsrfToken = form.getAttribute('data-lock-csrf') || '';", $template);
        $this->assertStringContainsString("requestBody.set('_token', lockCsrfToken);", $template);
        $partialTemplate = file_get_contents(__DIR__.'/../../Resources/views/content/partial/edit_frame.html.twig');
        $this->assertIsString($partialTemplate);
        $this->assertStringContainsString("content is defined ? csrf_token('integrated_content_lock_' ~ content.id) : ''", $partialTemplate);
    }

    public function testEditTemplatesOnlyFlagFormInvalidAfterSubmittedInvalidPost(): void
    {
        $editTemplate = file_get_contents(__DIR__.'/../../Resources/views/content/edit.html.twig');
        $iframeTemplate = file_get_contents(__DIR__.'/../../Resources/views/content/edit.iframe.html.twig');

        $this->assertIsString($editTemplate);
        $this->assertIsString($iframeTemplate);

        $this->assertStringContainsString(
            "var formInvalid = {{ (form.vars.submitted|default(false) and not form.vars.valid) ? '1' : '0' }};",
            $editTemplate
        );
        $this->assertStringContainsString(
            "var formInvalid = {{ (form.vars.submitted|default(false) and not form.vars.valid) ? '1' : '0' }};",
            $iframeTemplate
        );
    }
}
