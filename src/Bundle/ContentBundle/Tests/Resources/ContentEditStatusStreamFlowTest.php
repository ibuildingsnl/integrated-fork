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
        $this->assertStringContainsString('@IntegratedContent/content/partial/workflow_info.html.twig', $template);
        $this->assertStringContainsString('@IntegratedContent/content/partial/status_options.html.twig', $template);
        $this->assertStringContainsString('id="content-history-section"', $template);
        $this->assertStringContainsString('turbo:before-stream-render', $template);
        $this->assertStringContainsString('response.next_states', $template);
    }

    public function testStatusOptionsTurboStreamTemplateReplacesWorkflowStatusAndHistoryTargets(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/edit.status_options.turbo_stream.html.twig');

        $this->assertIsString($template);
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
        $this->assertStringContainsString("streamTarget !== 'content-workflow-section' && streamTarget !== 'content-publications-section'", $template);
        $this->assertStringContainsString("if (streamTarget === 'content-publications-section')", $template);
        $this->assertStringContainsString('window.schedulePublicationSettingsInit()', $template);
    }

    public function testStatusOptionsOnlyMarkPlannedWhenContentIsActuallyPublished(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/partial/status_options.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('{% set isPublished = content is defined and ((content.isPublished and content.getChannels|length > 0) or content.published == \'true\') %}', $template);
        $this->assertStringContainsString('{% set isPlanned = isPublished and content is defined and (\'now\'|date(\'U\') < content.publishTime.startDate|date(\'U\')) %}', $template);
        $this->assertStringContainsString('{% elseif isPlanned %}', $template);
        $this->assertStringNotContainsString("{% elseif (content is defined and ('now'|date('U') < content.publishTime.startDate|date('U'))) %}", $template);
    }
}
