<?php

namespace Integrated\Bundle\NewsletterBundle\Tests\Features;

use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Integrated\Bundle\NewsletterBundle\Document\Schedule\ScheduleEntryFactory;
use Integrated\Bundle\NewsletterBundle\EventListener\NewsletterChangeListener;
use Integrated\Common\Content\Form\Event\ValidationEvent;
use Integrated\Common\Form\Mapping\Metadata\Document;
use Integrated\Common\Form\Mapping\MetadataInterface;
use PHPUnit\Framework\TestCase;
use Stratadox\Clock\UnmovingClock;

final class GeneratingEmailsTest extends TestCase
{
    private NewsletterChangeListener $listener;
    private MetadataInterface $metadata;
    private ScheduleEntryFactory $schedule;

    protected function setUp(): void
    {
        $this->listener = new NewsletterChangeListener(
            // @todo add renderer or service with renderer
            UnmovingClock::standingStillAt(new \DateTimeImmutable('1-1-2000 10:30')),
            __DIR__ . '/Files/',
        );
        $this->metadata = new Document(Newsletter::class);
        $this->schedule = new ScheduleEntryFactory();
    }

    protected function tearDown(): void
    {
        foreach (glob(__DIR__ . '/Files/') ?: [] as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function testGeneratingWhenWithinWindow()
    {
        $newsletter = new Newsletter();
        $newsletter->schedule = $this->schedule->daily(12, 0);
        $newsletter->hoursBefore = 2;

        $this->listener->onPostValidate(new ValidationEvent(
            $this->contentType('newsletter', Newsletter::class),
            $this->metadata,
            $newsletter
        ));

        self::assertFileExists(__DIR__ . 'Files/2000/01/01/1200.html');
    }

    public function testNotGeneratingWhenOutsideWindow()
    {
        $newsletter = new Newsletter();
        $newsletter->schedule = $this->schedule->daily(12, 0);
        $newsletter->hoursBefore = 1;

        $this->listener->onPostValidate(new ValidationEvent(
            $this->contentType('newsletter', Newsletter::class),
            $this->metadata,
            $newsletter
        ));

        self::assertDirectoryDoesNotExist(__DIR__ . 'Files/2000/');
        self::assertFileDoesNotExist(__DIR__ . 'Files/2000/01/01/1200.html');
    }

    public function testIgnoreNonNewsletters()
    {
        $article = new Article();

        $this->listener->onPostValidate(new ValidationEvent(
            $this->contentType('article', Article::class),
            $this->metadata,
            $article
        ));

        self::assertDirectoryDoesNotExist(__DIR__ . 'Files/2000/');
    }

    private function contentType(string $type, string $class): ContentType
    {
        $contentType = new ContentType();
        $contentType->setId($type);
        $contentType->setName(ucfirst($type));
        $contentType->setClass($class);

        return $contentType;
    }
}
