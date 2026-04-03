<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\EventListener;

use Integrated\Bundle\AssetBundle\Manager\AssetManager;
use Integrated\Bundle\WebsiteBundle\EventListener\WebsiteEditableListener;
use Integrated\Bundle\WebsiteBundle\Service\EditableChecker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class WebsiteEditableListenerTest extends TestCase
{
    /** @var EditableChecker&MockObject */
    private EditableChecker $editableChecker;
    private WebsiteEditableListener $listener;

    protected function setUp(): void
    {
        $this->editableChecker = $this->createMock(EditableChecker::class);
        $this->listener = new WebsiteEditableListener($this->editableChecker, new AssetManager());
    }

    public function testOnControllerSkipsCheckerWhenEditQueryFlagIsMissing(): void
    {
        $this->editableChecker
            ->expects($this->never())
            ->method('checkEditable');

        $request = Request::create('/consent/state');
        $event = $this->createControllerEvent($request);

        $this->listener->onController($event);

        self::assertFalse($request->attributes->getBoolean('integrated_block_edit'));
        self::assertFalse($request->attributes->getBoolean('integrated_menu_edit'));
    }

    public function testOnControllerEnablesEditAttributesWhenEditableModeIsAllowed(): void
    {
        $this->editableChecker
            ->expects($this->once())
            ->method('checkEditable')
            ->willReturn(true);

        $request = Request::create('/preview', 'GET', ['integrated_website_edit' => '1']);
        $event = $this->createControllerEvent($request);

        $this->listener->onController($event);

        self::assertTrue($request->attributes->getBoolean('integrated_block_edit'));
        self::assertTrue($request->attributes->getBoolean('integrated_menu_edit'));
    }

    public function testOnControllerDoesNotEnableEditAttributesWhenEditableCheckFails(): void
    {
        $this->editableChecker
            ->expects($this->once())
            ->method('checkEditable')
            ->willReturn(false);

        $request = Request::create('/preview', 'GET', ['integrated_website_edit' => '1']);
        $event = $this->createControllerEvent($request);

        $this->listener->onController($event);

        self::assertFalse($request->attributes->getBoolean('integrated_block_edit'));
        self::assertFalse($request->attributes->getBoolean('integrated_menu_edit'));
    }

    private function createControllerEvent(Request $request): ControllerEvent
    {
        /** @var HttpKernelInterface&MockObject $kernel */
        $kernel = $this->createMock(HttpKernelInterface::class);

        return new ControllerEvent(
            $kernel,
            static function (): void {
            },
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );
    }
}
