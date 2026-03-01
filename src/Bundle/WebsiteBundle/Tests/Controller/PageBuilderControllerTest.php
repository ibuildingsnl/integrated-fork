<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\PageBundle\PageBuilder\V2\Validation\LayoutPayloadValidator;
use Integrated\Bundle\WebsiteBundle\Controller\PageBuilderController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class PageBuilderControllerTest extends TestCase
{
    /** @var DocumentManager&MockObject */
    private DocumentManager $documentManager;
    private LayoutPayloadValidator $validator;

    protected function setUp(): void
    {
        $this->documentManager = $this->createMock(DocumentManager::class);
        $this->validator = new LayoutPayloadValidator(
            __DIR__.'/../../../PageBundle/Resources/schema/pagebuilder/v2'
        );
    }

    public function testRejectsInvalidPayloadWith422(): void
    {
        $this->documentManager->expects($this->never())->method('flush');

        $controller = $this->createController();
        $response = $controller->save(Request::create(
            '/pagebuilder/save',
            'POST',
            [],
            [],
            [],
            [],
            (string) json_encode([
                'page' => 'page-id',
                'payload' => ['components' => []],
            ])
        ));

        self::assertSame(422, $response->getStatusCode());
        $decoded = json_decode((string) $response->getContent(), true);
        self::assertSame(false, $decoded['success']);
        self::assertSame('/root', $decoded['errors'][0]['path']);
        self::assertSame('required', $decoded['errors'][0]['code']);
    }

    private function createController(): PageBuilderController
    {
        return new class($this->documentManager, $this->validator) extends PageBuilderController {
            protected function isGranted(mixed $attribute, mixed $subject = null): bool
            {
                return true;
            }
        };
    }
}
