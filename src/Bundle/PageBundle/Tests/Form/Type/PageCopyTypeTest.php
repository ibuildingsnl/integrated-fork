<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Form\Type;

use Integrated\Bundle\ChannelBundle\Form\Type\ChannelChoiceType;
use Integrated\Bundle\PageBundle\Form\Type\PageCopyType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;

final class PageCopyTypeTest extends TestCase
{
    public function testSourceAndTargetChannelChoicesAreRestrictedToWebsiteChannels(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder
            ->expects(self::exactly(3))
            ->method('add')
            ->willReturnCallback(function (string $name, ?string $type = null, array $options = []) use ($builder) {
                if (\in_array($name, ['sourceChannel', 'targetChannel'], true)) {
                    self::assertSame(ChannelChoiceType::class, $type);
                    self::assertSame(['type.$id' => 'website'], $options['filter'] ?? null);
                }

                return $builder;
            });

        (new PageCopyType())->buildForm($builder, [
            'sourceChannel' => null,
            'targetChannel' => null,
        ]);
    }
}
