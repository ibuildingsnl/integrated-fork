<?php

namespace Integrated\Bundle\NewsletterBundle\Tests\Features;

use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Integrated\Bundle\NewsletterBundle\Document\Schedule\CombinedRecurringScheduleEntry;
use Integrated\Bundle\NewsletterBundle\Document\Schedule\ScheduleEntryFactory;
use Integrated\Bundle\NewsletterBundle\Service\NewsletterGenerator;
use Integrated\Bundle\NewsletterBundle\Service\TestMailSender;
use Integrated\Bundle\NewsletterBundle\Tests\Features\Doubles\FixedRenderer;
use Integrated\Bundle\NewsletterBundle\Tests\Features\Doubles\SpyingCampaignUpdater;
use Integrated\Bundle\NewsletterBundle\Tests\Features\Doubles\SpyingTestMailTrigger;
use PHPUnit\Framework\TestCase;
use Stratadox\Clock\RewindableDateTimeClock;
use Stratadox\Clock\SneakyTestClock;
use Stratadox\Clock\UnmovingClock;

final class SendingTestMailsTest extends TestCase
{
    private ScheduleEntryFactory $schedule;
    private TestMailSender $testMail;
    private SneakyTestClock $clock;
    private SpyingCampaignUpdater $campaign;
    private SpyingTestMailTrigger $testMailTrigger;

    protected function setUp(): void
    {
        if (!is_dir(__DIR__ . '/Files/')) {
            mkdir(__DIR__ . '/Files/');
        }
        $this->schedule = new ScheduleEntryFactory();
        $this->clock = SneakyTestClock::using(RewindableDateTimeClock::using(
            UnmovingClock::standingStillAt(new \DateTimeImmutable('1-1-2000 10:30'))
        ));
        $this->campaign = new SpyingCampaignUpdater();
        $this->testMailTrigger = new SpyingTestMailTrigger();
        $this->testMail = new TestMailSender(
            new NewsletterGenerator(
                $this->clock,
                new FixedRenderer('<html><body>Look mum, it&apos;s a newsetter</body></html>'),
                __DIR__ . '/Files/',
            ),
            $this->clock,
            $this->campaign,
            $this->testMailTrigger,
        );
    }

    public function testSendingTestMailOnceTheWindowOpens()
    {
        $newsletter = new Newsletter();
        $newsletter->setId('abc123');
        $newsletter->schedule = $this->schedule->daily(12, 0);
        $newsletter->hoursBefore = 2;

        $this->testMail->maybeSend($newsletter);

        self::assertFileExists(__DIR__ . '/Files/abc123/2000/01/01/1200.html');
        self::assertTrue($this->campaign->wasUpdated($newsletter));
        self::assertTrue($this->testMailTrigger->wasTriggeredFor($newsletter));
    }

    public function testNotSendingTestMailBeforeTheWindowOpens()
    {
        $newsletter = new Newsletter();
        $newsletter->setId('abc123');
        $newsletter->schedule = $this->schedule->daily(12, 0);
        $newsletter->hoursBefore = 1;

        $this->testMail->maybeSend($newsletter);

        self::assertFileDoesNotExist(__DIR__ . '/Files/abc123/2000/01/01/1200.html');
        self::assertFalse($this->campaign->wasUpdated($newsletter));
        self::assertFalse($this->testMailTrigger->wasTriggeredFor($newsletter));
    }

    public function testNotSendingTestMailIfAlreadySentThisWindow()
    {
        $newsletter = new Newsletter();
        $newsletter->setId('abc123');
        $newsletter->schedule = $this->schedule->daily(12, 0);
        $newsletter->hoursBefore = 2;

        $this->testMail->maybeSend($newsletter);

        $this->campaign->reset($newsletter);
        $this->testMailTrigger->reset($newsletter);

        $this->clock->sneakForwards(\DateInterval::createFromDateString('+1 hours'));

        $this->testMail->maybeSend($newsletter);

        self::assertFalse($this->campaign->wasUpdated($newsletter));
        self::assertFalse($this->testMailTrigger->wasTriggeredFor($newsletter));
    }

    public function testSendingAfterPreviousScheduledSameDayMail()
    {
        $newsletter = new Newsletter();
        $newsletter->setId('abc123');
        $newsletter->schedule = new CombinedRecurringScheduleEntry(
            $this->schedule->daily(11, 0),
            $this->schedule->daily(12, 0),
        );
        $newsletter->hoursBefore = 2;

        $this->testMail->maybeSend($newsletter);

        $this->clock->sneakForwards(\DateInterval::createFromDateString('+1 hours'));

        $this->testMail->maybeSend($newsletter);

        self::assertFileExists(__DIR__ . '/Files/abc123/2000/01/01/1100.html');
        self::assertFileExists(__DIR__ . '/Files/abc123/2000/01/01/1200.html');
    }

    public function testNotSendingBeforePreviousScheduledSameDayMail()
    {
        $newsletter = new Newsletter();
        $newsletter->setId('abc123');
        $newsletter->schedule = new CombinedRecurringScheduleEntry(
            $this->schedule->daily(11, 0),
            $this->schedule->daily(12, 0),
        );
        $newsletter->hoursBefore = 2;

        $this->testMail->maybeSend($newsletter);

        $this->clock->sneakForwards(\DateInterval::createFromDateString('+25 minutes'));

        $this->testMail->maybeSend($newsletter);

        self::assertFileExists(__DIR__ . '/Files/abc123/2000/01/01/1100.html');
        // It's 10:55, technically less than 2h before 12:00, but since we're also sending one at 11h, there's no
        // test mail expected for this one yet
        self::assertFileDoesNotExist(__DIR__ . '/Files/abc123/2000/01/01/1200.html');
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
