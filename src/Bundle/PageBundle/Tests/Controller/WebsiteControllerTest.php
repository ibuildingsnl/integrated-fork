<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Controller;

use Integrated\Bundle\PageBundle\Controller\WebsiteController;
use Integrated\Bundle\WebsiteBundle\Service\EditableChecker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class WebsiteControllerTest extends TestCase
{
    /** @var EditableChecker&MockObject */
    private EditableChecker $editableChecker;

    protected function setUp(): void
    {
        $this->editableChecker = $this->createMock(EditableChecker::class);
    }

    public function testCreatePageReturnsEmptyResponseWhenWebsiteEditModeIsOff(): void
    {
        $this->editableChecker->expects($this->never())->method('checkEditable');

        $controller = $this->createController([
            'ROLE_WEBSITE_MANAGER' => true,
            'ROLE_ADMIN' => false,
        ]);

        $response = $controller->createPage(Request::create('/_page/create-page', 'GET', [
            'path' => '/missing-page',
        ]));

        self::assertSame('', $response->getContent());
    }

    public function testCreatePageReturnsEmptyResponseWhenRequestIsNotEditable(): void
    {
        $this->editableChecker
            ->expects($this->once())
            ->method('checkEditable')
            ->willReturn(false);

        $controller = $this->createController([
            'ROLE_WEBSITE_MANAGER' => true,
            'ROLE_ADMIN' => false,
        ]);

        $response = $controller->createPage(Request::create('/_page/create-page', 'GET', [
            'path' => '/missing-page',
            'integrated_website_edit' => '1',
        ]));

        self::assertSame('', $response->getContent());
    }

    /**
     * @param array<string, bool> $grants
     */
    private function createController(array $grants): WebsiteController
    {
        $editableChecker = $this->editableChecker;

        return new class($editableChecker, $grants) extends WebsiteController {
            /**
             * @param array<string, bool> $grants
             */
            public function __construct(
                EditableChecker $editableChecker,
                private readonly array $grants,
            ) {
                parent::__construct($editableChecker);
            }

            protected function isGranted(mixed $attribute, mixed $subject = null): bool
            {
                return $this->grants[(string) $attribute] ?? false;
            }
        };
    }
}
