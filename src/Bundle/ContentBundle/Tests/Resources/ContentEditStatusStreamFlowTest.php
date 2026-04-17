<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class ContentEditStatusStreamFlowTest extends TestCase
{
    public function testControllerRendersDedicatedStatusAndHistoryTurboStreamTemplate(): void
    {
        $controller = file_get_contents(__DIR__.'/../../Controller/ContentController.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString('@IntegratedContent/content/edit.status_options.turbo_stream.html.twig', $controller);
        $this->assertStringContainsString("'form' => \$form->createView()", $controller);
        $this->assertStringContainsString("'publications' => \$this->getPublications(\$content)", $controller);
    }

    public function testEditTemplateContainsTargetedStatusAndHistoryContainers(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/edit.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('id="content-edit-page" data-inline-workflow-state-init="true"', $template);
        $this->assertStringContainsString('@IntegratedContent/content/partial/workflow_info.html.twig', $template);
        $this->assertStringContainsString('@IntegratedContent/content/partial/status_options.html.twig', $template);
        $this->assertStringContainsString('id="content-history-section"', $template);
        $this->assertStringContainsString('turbo:before-stream-render', $template);
        $this->assertStringContainsString('response.next_states', $template);
        $this->assertStringContainsString('data-content-lock-watch', $template);
        $this->assertStringContainsString('I want to watch', $template);
        $this->assertStringContainsString('<div class="content-lock-overlay__message">', $template);
        $this->assertStringContainsString('<div class="content-lock-overlay__actions mt-4">', $template);
    }

    public function testStatusOptionsTurboStreamTemplateReplacesWorkflowStatusAndHistoryTargets(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/edit.status_options.turbo_stream.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('target="toolbar"', $template);
        $this->assertStringContainsString('@IntegratedContent/partials/block.toolbar.html.twig', $template);
        $this->assertStringContainsString('target="content-workflow-section"', $template);
        $this->assertStringContainsString('@IntegratedContent/content/partial/workflow_info.html.twig', $template);
        $this->assertStringContainsString('target="content-status-options"', $template);
        $this->assertStringContainsString('@IntegratedContent/content/partial/status_options.html.twig', $template);
        $this->assertStringContainsString('target="content-publications-section"', $template);
        $this->assertStringContainsString('@IntegratedContent/content/partial/publications.html.twig', $template);
        $this->assertStringContainsString('target="content-history-section"', $template);
        $this->assertStringContainsString('ContentHistoryController::history', $template);
    }

    public function testEditTemplateReinitializesPublicationInteractionsAfterTurboStreamReplace(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/edit.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString("streamTarget !== 'content-workflow-section'", $template);
        $this->assertStringContainsString("&& streamTarget !== 'content-publications-section'", $template);
        $this->assertStringContainsString("&& streamTarget !== 'toolbar'", $template);
        $this->assertStringContainsString("if (streamTarget === 'content-publications-section')", $template);
        $this->assertStringContainsString("if (streamTarget === 'toolbar')", $template);
        $this->assertStringContainsString("document.dispatchEvent(new Event('turbo:render'));", $template);
        $this->assertStringContainsString('window.schedulePublicationSettingsInit()', $template);
    }

    public function testEditTemplateInitializesWorkflowSectionIdempotently(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/edit.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString("\$workflowRoot.attr('data-workflow-state-initialized') === 'true'", $template);
        $this->assertStringContainsString("\$workflowRoot.attr('data-workflow-state-initialized', 'true');", $template);
        $this->assertStringContainsString('data-workflow-pending-signature', $template);
        $this->assertStringContainsString('data-workflow-last-applied-signature', $template);
        $this->assertStringContainsString('window.requestAnimationFrame(initializeWorkflowSection);', $template);
        $this->assertStringNotContainsString('window.setTimeout(initializeWorkflowSection, 50);', $template);
        $this->assertStringContainsString('function enableLockWatchMode(form)', $template);
        $this->assertStringContainsString("event.target.closest('[data-content-lock-watch]')", $template);
        $this->assertStringContainsString('overlayMessage.appendChild(overlayActions);', $template);
        $this->assertStringContainsString('document.createTextNode', $template);
        $this->assertStringNotContainsString('messageNode.textContent = message || LOCKED_ITEM_MESSAGE;', $template);
    }

    public function testStatusOptionsUsePublishableWorkflowStateForPlannedContent(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/partial/status_options.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('{% set isPublishable = workflow is defined ? workflow.isPublishable() : (content is defined ? (content.isPublished(false) and content.getChannels|length > 0) : false) %}', $template);
        $this->assertStringContainsString("{% set isPlanned = isPublishable and content is defined and 'now'|date('U') < content.publishTime.startDate|date('U') %}", $template);
        $this->assertStringContainsString('{% set isPublished = isPublishable and not isPlanned %}', $template);
    }
}
