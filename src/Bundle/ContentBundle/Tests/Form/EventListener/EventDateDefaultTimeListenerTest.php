<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Form\EventListener;

use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Event;
use Integrated\Bundle\ContentBundle\Form\EventListener\EventDateDefaultTimeListener;
use Integrated\Common\Content\Form\Event\BuilderEvent;
use Integrated\Common\Form\Mapping\Metadata\Document;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Forms;

final class EventDateDefaultTimeListenerTest extends TestCase
{
    private EventDateDefaultTimeListener $listener;

    protected function setUp(): void
    {
        $this->listener = new EventDateDefaultTimeListener();
    }

    public function testDefaultsEmptyEventTimesToMidnightOnSubmit(): void
    {
        $builder = $this->createBuilder(Event::class);
        $this->listener->onPostBuild($this->createBuilderEvent(Event::class, $builder));

        $form = $builder->getForm();
        $form->submit([
            'startDate' => ['date' => '2026-03-18', 'time' => ''],
            'endDate' => ['date' => '2026-03-19', 'time' => ''],
        ]);

        /** @var Event $data */
        $data = $form->getData();

        self::assertSame('2026-03-18 00:00', $data->getStartDate()->format('Y-m-d H:i'));
        self::assertSame('2026-03-19 00:00', $data->getEndDate()->format('Y-m-d H:i'));
    }

    public function testKeepsExplicitEventTimesUntouched(): void
    {
        $builder = $this->createBuilder(Event::class);
        $this->listener->onPostBuild($this->createBuilderEvent(Event::class, $builder));

        $form = $builder->getForm();
        $form->submit([
            'startDate' => ['date' => '2026-03-18', 'time' => '14:30'],
            'endDate' => ['date' => '2026-03-19', 'time' => '16:45'],
        ]);

        /** @var Event $data */
        $data = $form->getData();

        self::assertSame('2026-03-18 14:30', $data->getStartDate()->format('Y-m-d H:i'));
        self::assertSame('2026-03-19 16:45', $data->getEndDate()->format('Y-m-d H:i'));
    }

    public function testDoesNothingForNonEventContentTypes(): void
    {
        $builder = $this->createBuilder(Event::class);
        $listenerCount = \count($builder->getEventDispatcher()->getListeners(FormEvents::PRE_SUBMIT));

        $this->listener->onPostBuild($this->createBuilderEvent(Article::class, $builder));

        self::assertCount($listenerCount, $builder->getEventDispatcher()->getListeners(FormEvents::PRE_SUBMIT));
    }

    /**
     * @return FormBuilderInterface<mixed>
     */
    private function createBuilder(string $dataClass): FormBuilderInterface
    {
        $builder = Forms::createFormFactoryBuilder()
            ->getFormFactory()
            ->createNamedBuilder('form', FormType::class, new $dataClass(), [
                'data_class' => $dataClass,
            ]);

        $builder->add('startDate', DateTimeType::class, [
            'required' => false,
            'html5' => true,
            'date_widget' => 'single_text',
            'time_widget' => 'single_text',
        ]);
        $builder->add('endDate', DateTimeType::class, [
            'required' => false,
            'html5' => true,
            'date_widget' => 'single_text',
            'time_widget' => 'single_text',
        ]);

        return $builder;
    }

    public function testRenderedEventDateInputsDoNotRequireTime(): void
    {
        $view = $this->createBuilder(Event::class)->getForm()->createView();

        self::assertFalse($view['startDate']['time']->vars['required']);
        self::assertFalse($view['endDate']['time']->vars['required']);
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     */
    private function createBuilderEvent(string $contentClass, FormBuilderInterface $builder): BuilderEvent
    {
        $type = new \Integrated\Bundle\ContentBundle\Document\ContentType\ContentType();
        $type->setClass($contentClass);

        return new BuilderEvent($type, new Document(null), $builder, []);
    }
}
