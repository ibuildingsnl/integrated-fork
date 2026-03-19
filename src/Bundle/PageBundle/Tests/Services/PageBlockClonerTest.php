<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Services;

use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Integrated\Bundle\BlockBundle\Document\Block\InlineTextBlock;
use Integrated\Bundle\BlockBundle\Document\Block\TextBlock;
use Integrated\Bundle\ContentBundle\Document\Block\ContentBlock;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\PageBundle\Services\PageCopy\PageBlockCloner;
use PHPUnit\Framework\TestCase;

final class PageBlockClonerTest extends TestCase
{
    public function testClonesTextBlockWithoutReflection(): void
    {
        $source = new TextBlock();
        $source->setId('source-block');
        $source->setTitle('Source block');
        $source->setContent('Hello');
        $source->setCssClass('hero');

        $cloner = new PageBlockCloner();
        $copiedPage = new Page();
        $copiedPage->setPath('/copy');
        $copiedPage->setLayout('default.html.twig');
        $copiedPage->setTitle('Copy');

        $cloned = $cloner->cloneBlock($source, 'target-block', $copiedPage);

        self::assertInstanceOf(TextBlock::class, $cloned);
        self::assertNotSame($source, $cloned);
        self::assertSame('target-block', $cloned->getId());
        self::assertSame('Source block', $cloned->getTitle());
        self::assertSame('Hello', $cloned->getContent());
        self::assertSame('hero', $cloned->getCssClass());
    }

    public function testClonesInlineTextBlockAgainstCopiedPage(): void
    {
        $sourcePage = new Page();
        $sourcePage->setPath('/source');
        $sourcePage->setLayout('default.html.twig');
        $sourcePage->setTitle('Source');

        $copiedPage = new Page();
        $copiedPage->setPath('/copy');
        $copiedPage->setLayout('default.html.twig');
        $copiedPage->setTitle('Copy');

        $source = new InlineTextBlock($sourcePage);
        $source->setId('inline-source');
        $source->setContent('Inline');

        $cloned = (new PageBlockCloner())->cloneBlock($source, 'inline-copy', $copiedPage);

        self::assertInstanceOf(InlineTextBlock::class, $cloned);
        self::assertSame('inline-copy', $cloned->getId());
        self::assertSame('Inline', $cloned->getContent());
        self::assertSame($copiedPage, $cloned->getPage());
    }

    public function testClonesContentBlockSubclass(): void
    {
        $source = new class extends ContentBlock {};
        $source->setId('content-source');
        $source->setTitle('Content block');
        $source->setItemsPerPage(12);
        $source->setMaxItems(24);
        $source->setGridSize(3);
        $source->setReadMoreUrl('/more');
        $source->setReadMoreText('Read more');
        $source->setFacetFields(['brand']);
        $source->setPublishedTitle('Published title');
        $source->setUseTitle(true);

        $copiedPage = new Page();
        $copiedPage->setPath('/copy');
        $copiedPage->setLayout('default.html.twig');
        $copiedPage->setTitle('Copy');

        $cloned = (new PageBlockCloner())->cloneBlock($source, 'content-copy', $copiedPage);

        self::assertInstanceOf(ContentBlock::class, $cloned);
        self::assertSame('content-copy', $cloned->getId());
        self::assertSame('Content block', $cloned->getTitle());
        self::assertSame(12, $cloned->getItemsPerPage());
        self::assertSame(24, $cloned->getMaxItems());
        self::assertSame(3, $cloned->getGridSize());
        self::assertSame('/more', $cloned->getReadMoreUrl());
        self::assertSame('Read more', $cloned->getReadMoreText());
        self::assertSame(['brand'], $cloned->getFacetFields());
        self::assertSame('Published title', $cloned->getPublishedTitle());
        self::assertTrue($cloned->getUseTitle());
    }

    public function testClonesCustomProjectBlockSubclass(): void
    {
        $source = new TestCompanyProfileBlock();
        $source->setId('company-source');
        $source->setTitle('Company block');
        $source->setProfileType('Full profile');
        $source->setContentType('company');
        $source->setRecipients(['sales@example.com']);
        $source->setPublishedTitle('Company published title');
        $source->setRecaptcha(true);
        $source->setUpsells(['spotlight']);
        $source->setUpsellChannels(['main']);

        $copiedPage = new Page();
        $copiedPage->setPath('/copy');
        $copiedPage->setLayout('default.html.twig');
        $copiedPage->setTitle('Copy');

        $cloned = (new PageBlockCloner())->cloneBlock($source, 'company-copy', $copiedPage);

        self::assertInstanceOf(TestCompanyProfileBlock::class, $cloned);
        self::assertSame('company-copy', $cloned->getId());
        self::assertSame('Company block', $cloned->getTitle());
        self::assertSame('Full profile', $cloned->getProfileType());
        self::assertSame('company', $cloned->getContentType());
        self::assertSame(['sales@example.com'], $cloned->getRecipients());
        self::assertSame('Company published title', $cloned->getPublishedTitle());
        self::assertTrue($cloned->isRecaptcha());
        self::assertSame(['spotlight'], $cloned->getUpsells());
        self::assertSame(['main'], $cloned->getUpsellChannels());
    }

    public function testPreservesConcreteCustomContentBlockSubclass(): void
    {
        $source = new TestContentWithAdvertBlock();
        $source->setId('advert-source');
        $source->setTitle('Advert block');
        $source->setItemsPerPage(6);
        $source->setMaxItems(12);
        $source->setGridSize(2);
        $source->setReadMoreUrl('/discover');
        $source->setReadMoreText('Discover more');
        $source->setFacetFields(['brand']);

        $copiedPage = new Page();
        $copiedPage->setPath('/copy');
        $copiedPage->setLayout('default.html.twig');
        $copiedPage->setTitle('Copy');

        $cloned = (new PageBlockCloner())->cloneBlock($source, 'advert-copy', $copiedPage);

        self::assertInstanceOf(TestContentWithAdvertBlock::class, $cloned);
        self::assertSame('advert-copy', $cloned->getId());
        self::assertSame(6, $cloned->getItemsPerPage());
        self::assertSame(12, $cloned->getMaxItems());
        self::assertSame(2, $cloned->getGridSize());
        self::assertSame('/discover', $cloned->getReadMoreUrl());
        self::assertSame('Discover more', $cloned->getReadMoreText());
        self::assertSame(['brand'], $cloned->getFacetFields());
    }

    public function testThrowsForUnsupportedBlockType(): void
    {
        $source = new class('required') extends Block {
            public function __construct(string $required)
            {
                if ($required === '') {
                    throw new \InvalidArgumentException();
                }

                parent::__construct();
            }

            public function getType()
            {
                return 'unsupported';
            }
        };
        $source->setId('unsupported');
        $source->setTitle('Unsupported');

        $targetPage = new Page();
        $targetPage->setPath('/copy');
        $targetPage->setLayout('default.html.twig');
        $targetPage->setTitle('Copy');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported block type');

        (new PageBlockCloner())->cloneBlock($source, 'unsupported-copy', $targetPage);
    }
}

final class TestCompanyProfileBlock extends Block
{
    private string $profileType = '';
    private string $contentType = '';
    /** @var string[] */
    private array $recipients = [];
    private ?string $publishedTitle = null;
    private bool $recaptcha = false;
    /** @var string[] */
    private array $upsells = [];
    /** @var string[] */
    private array $upsellChannels = [];

    public function getType()
    {
        return 'test_company_profile';
    }

    public function setProfileType(string $profileType): void
    {
        $this->profileType = $profileType;
    }

    public function getProfileType(): string
    {
        return $this->profileType;
    }

    public function setContentType(string $contentType): void
    {
        $this->contentType = $contentType;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @param string[] $recipients
     */
    public function setRecipients(array $recipients): void
    {
        $this->recipients = $recipients;
    }

    /**
     * @return string[]
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }

    public function setPublishedTitle(string $publishedTitle): void
    {
        $this->publishedTitle = $publishedTitle;
    }

    public function getPublishedTitle(): ?string
    {
        return $this->publishedTitle;
    }

    public function setRecaptcha(bool $recaptcha): void
    {
        $this->recaptcha = $recaptcha;
    }

    public function isRecaptcha(): bool
    {
        return $this->recaptcha;
    }

    /**
     * @param string[] $upsells
     */
    public function setUpsells(array $upsells): void
    {
        $this->upsells = $upsells;
    }

    /**
     * @return string[]
     */
    public function getUpsells(): array
    {
        return $this->upsells;
    }

    /**
     * @param string[] $upsellChannels
     */
    public function setUpsellChannels(array $upsellChannels): void
    {
        $this->upsellChannels = $upsellChannels;
    }

    /**
     * @return string[]
     */
    public function getUpsellChannels(): array
    {
        return $this->upsellChannels;
    }
}

final class TestContentWithAdvertBlock extends ContentBlock
{
    public function getType()
    {
        return 'test_content_with_advert';
    }
}
