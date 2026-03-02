<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ContentTypeDeleteCascadeFlowTest extends TestCase
{
    public function testDeleteControllerSupportsOptionalRelatedContentDeletion(): void
    {
        $controller = file_get_contents(__DIR__.'/../../Controller/ContentTypeController.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString('use Integrated\\Bundle\\ContentBundle\\Event\\ContentDeletedEvent;', $controller);
        $this->assertStringContainsString('use Integrated\\Common\\Content\\Form\\Events as ContentEvents;', $controller);
        $this->assertStringContainsString('use Symfony\\Component\\Form\\Extension\\Core\\Type\\CheckboxType;', $controller);
        $this->assertStringContainsString('$relatedContent = $this->getRelatedContent($contentType);', $controller);
        $this->assertStringContainsString('$deleteRelatedContent = $form->has(\'delete_related_content\') && (bool) $form->get(\'delete_related_content\')->getData();', $controller);
        $this->assertStringContainsString('if (\count($relatedContent) > 0 && !$deleteRelatedContent) {', $controller);
        $this->assertStringContainsString('This content type still contains content. Select "Delete related content" to proceed.', $controller);
        $this->assertStringContainsString('$this->removeRelatedContent($relatedContent);', $controller);
        $this->assertStringContainsString('private function removeRelatedContent(array $relatedContent): void', $controller);
        $this->assertStringContainsString('new ContentDeletedEvent($content)', $controller);
        $this->assertStringContainsString('ContentEvents::CONTENT_DELETED', $controller);
        $this->assertStringContainsString("'mapped' => false,", $controller);
        $this->assertStringContainsString('private function getRelatedContent(ContentTypeInterface $contentType): array', $controller);
    }

    public function testDeleteTemplateShowsRelatedContentWarning(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content_type/delete.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('{% if hasRelatedContent %}', $template);
        $this->assertStringContainsString('This content type has related content. Select "Delete related content" to remove that content before deleting this content type.', $template);
    }
}
