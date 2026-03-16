<?php

declare(strict_types=1);

namespace Integrated\Bundle\FormTypeBundle\Tests\Resources;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Doctrine\ContentTypeManager;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelType;
use Integrated\Bundle\FormTypeBundle\Form\Type\FilterableContentChoiceType;
use Integrated\Common\Content\Channel\ChannelManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormView;

final class FilterableContentChoiceTypeTest extends TestCase
{
    public function testOnlyWebsiteChannelsAreExposedToTheWidget(): void
    {
        $channelManager = $this->createMock(ChannelManagerInterface::class);
        $channelManager->method('findAll')->willReturn([
            $this->createChannel('website', 'Website NL', 'Website'),
            $this->createChannel('newsletter', 'Nieuwsbrief', 'Newsletter'),
        ]);

        $contentTypeManager = $this->createMock(ContentTypeManager::class);
        $contentTypeManager->method('getAll')->willReturn([]);

        $type = new FilterableContentChoiceType(
            $this->createMock(DocumentManager::class),
            'Integrated\\Bundle\\ContentBundle\\Document\\Content\\Content',
            'integrated_content_content_index',
            ['_format' => 'json'],
            $channelManager,
            $contentTypeManager,
        );

        $view = new FormView();
        $view->vars['attr'] = [];
        $view->vars['full_name'] = 'items';

        $type->buildView($view, $this->createStub(\Symfony\Component\Form\FormInterface::class), [
            'repository_class' => 'Integrated\\Bundle\\ContentBundle\\Document\\Content\\Content',
            'route' => 'integrated_content_content_index',
            'params' => ['_format' => 'json'],
            'content_types' => null,
            'multiple' => true,
            'compound' => false,
            'required' => false,
            'placeholder' => null,
            'allow_clear' => false,
            'show_channel_filter' => true,
            'show_content_type_filter' => true,
        ]);

        self::assertSame([
            ['value' => 'website', 'label' => 'Website NL'],
        ], $view->vars['channel_choices']);
        self::assertTrue($view->vars['show_channel_filter']);
        self::assertTrue($view->vars['show_content_type_filter']);
    }

    public function testFilterVisibilityCanBeDisabledPerOption(): void
    {
        $channelManager = $this->createMock(ChannelManagerInterface::class);
        $channelManager->method('findAll')->willReturn([]);

        $contentTypeManager = $this->createMock(ContentTypeManager::class);
        $contentTypeManager->method('getAll')->willReturn([]);

        $type = new FilterableContentChoiceType(
            $this->createMock(DocumentManager::class),
            'Integrated\\Bundle\\ContentBundle\\Document\\Content\\Content',
            'integrated_content_content_index',
            ['_format' => 'json'],
            $channelManager,
            $contentTypeManager,
        );

        $view = new FormView();
        $view->vars['attr'] = [];
        $view->vars['full_name'] = 'items';

        $type->buildView($view, $this->createStub(\Symfony\Component\Form\FormInterface::class), [
            'repository_class' => 'Integrated\\Bundle\\ContentBundle\\Document\\Content\\Content',
            'route' => 'integrated_content_content_index',
            'params' => ['_format' => 'json'],
            'content_types' => null,
            'multiple' => true,
            'compound' => false,
            'required' => false,
            'placeholder' => null,
            'allow_clear' => false,
            'show_channel_filter' => false,
            'show_content_type_filter' => true,
        ]);

        self::assertFalse($view->vars['show_channel_filter']);
        self::assertTrue($view->vars['show_content_type_filter']);
    }

    private function createChannel(string $id, string $name, string $typeName): Channel
    {
        $channel = new Channel();
        $channel->setId($id);
        $channel->setName($name);
        $channel->setType(new ChannelType(strtolower($typeName), $typeName));

        return $channel;
    }
}
