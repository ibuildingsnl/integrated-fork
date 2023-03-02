<?php

namespace Integrated\Bundle\NewsletterBundle\Tests\Features;

use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Integrated\Bundle\NewsletterBundle\Document\Schedule\ScheduleEntryFactory;
use Integrated\Bundle\NewsletterBundle\EventListener\NewsletterChangeListener;
use Integrated\Bundle\NewsletterBundle\Service\NewsletterGenerator;
use Integrated\Bundle\NewsletterBundle\Tests\Features\Doubles\FixedRenderer;
use Integrated\Bundle\NewsletterBundle\Tests\Features\Doubles\SpyingCampaignUpdater;
use Integrated\Bundle\NewsletterBundle\Tests\Features\Doubles\SpyingTestMailTrigger;
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
    private SpyingCampaignUpdater $campaign;

    protected function setUp(): void
    {
        if (!is_dir(__DIR__ . '/Files/')) {
            mkdir(__DIR__ . '/Files/');
        }
        $clock = UnmovingClock::standingStillAt(new \DateTimeImmutable('1-1-2000 10:30'));
        $this->campaign = new SpyingCampaignUpdater();
        $this->listener = new NewsletterChangeListener(
            new NewsletterGenerator(
                $clock,
                new FixedRenderer('<html><body>NEWSLETTER!</body></html>'),
                __DIR__ . '/Files/',
            ),
            $this->campaign,
            $clock,
        );
        $this->metadata = new Document(Newsletter::class);
        $this->schedule = new ScheduleEntryFactory();
    }

    public function testGeneratingWhenWithinWindow()
    {
        $newsletter = new Newsletter();
        $newsletter->setId('abc123');
        $newsletter->schedule = $this->schedule->daily(12, 0);
        $newsletter->hoursBefore = 2;

        $this->listener->onPostValidate(new ValidationEvent(
            $this->contentType('newsletter', Newsletter::class),
            $this->metadata,
            $newsletter
        ));

        self::assertFileExists(__DIR__ . '/Files/abc123/2000/01/01/1200.html');
        self::assertTrue($this->campaign->wasUpdated($newsletter));
    }

    public function testNotGeneratingWhenOutsideWindow()
    {
        $newsletter = new Newsletter();
        $newsletter->setId('abc123');
        $newsletter->schedule = $this->schedule->daily(12, 0);
        $newsletter->hoursBefore = 1;

        $this->listener->onPostValidate(new ValidationEvent(
            $this->contentType('newsletter', Newsletter::class),
            $this->metadata,
            $newsletter
        ));

        self::assertDirectoryDoesNotExist(__DIR__ . '/Files/2000/');
        self::assertFileDoesNotExist(__DIR__ . '/Files/abc123/2000/01/01/1200.html');
        self::assertFalse($this->campaign->wasUpdated($newsletter));
    }

    public function testIgnoreNonNewsletters()
    {
        $article = new Article();
        $article->setId('abc123');

        $this->listener->onPostValidate(new ValidationEvent(
            $this->contentType('article', Article::class),
            $this->metadata,
            $article
        ));

        self::assertDirectoryDoesNotExist(__DIR__ . '/Files/abc123/');
    }

    private function contentType(string $type, string $class): ContentType
    {
        $contentType = new ContentType();
        $contentType->setId($type);
        $contentType->setName(ucfirst($type));
        $contentType->setClass($class);

        return $contentType;
    }

    protected function tearDown(): void
    {
        $this->remove(__DIR__ . '/Files/');
    }

    private function remove(string $dir): void
    {
        foreach (glob($dir . '/*') ?: [] as $path) {
            if (is_dir($path)) {
                $this->remove($path);
            }
            if (is_file($path)) {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
