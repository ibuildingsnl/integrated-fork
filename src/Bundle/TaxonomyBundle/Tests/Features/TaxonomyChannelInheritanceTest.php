<?php

namespace Integrated\Bundle\TaxonomyBundle\Tests\Features;

use Integrated\Bundle\ContentBundle\Document\Channel\WebsiteChannel;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\TaxonomyBundle\Domain\TaxonomyRepositoryInterface;
use Integrated\Bundle\TaxonomyBundle\EventListener\TaxonomyChannelInheritanceListener;
use Integrated\Bundle\TaxonomyBundle\Tests\Features\Doubles\MemoryTaxonomyRepository;
use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Common\Content\Form\Event\ValidationEvent;
use Integrated\Common\Form\Mapping\Metadata\Document;
use PHPUnit\Framework\TestCase;

final class TaxonomyChannelInheritanceTest extends TestCase
{
    /** @var ChannelInterface[] */
    public array $channels;
    private TaxonomyRepositoryInterface $taxonomies;
    private TaxonomyChannelInheritanceListener $listener;

    protected function setUp(): void
    {
        $this->taxonomies = new MemoryTaxonomyRepository();
        $this->listener = new TaxonomyChannelInheritanceListener($this->taxonomies);

        $this->channels = [
            'hobbits' => (new WebsiteChannel())->setId('hobbits'),
            'elves' => (new WebsiteChannel())->setId('elves'),
            'wizards' => (new WebsiteChannel())->setId('wizards'),
        ];
    }

    public function testAssigningParentsChannelToChildTaxonomies()
    {
        $parent = $this->taxonomy('shire', null, 'hobbits');
        $this->taxonomies->add($parent);
        $child = $this->taxonomy('hobbiton', 'shire');

        $this->listener->afterValidation($this->event($child));

        self::assertEquals([$this->channels['hobbits']], $child->getChannels());
    }

    public function testAssigningParentsChannelsToChildTaxonomies()
    {
        $parent = $this->taxonomy('shire', null, 'hobbits', 'wizards');
        $this->taxonomies->add($parent);
        $child = $this->taxonomy('hobbiton', 'shire');

        $this->listener->afterValidation($this->event($child));

        self::assertEquals([$this->channels['hobbits'], $this->channels['wizards']], $child->getChannels());
    }

    public function testOverwritingInitialChannels()
    {
        $parent = $this->taxonomy('shire', null, 'hobbits');
        $this->taxonomies->add($parent);
        $child = $this->taxonomy('hobbiton', 'shire', 'elves');

        $this->listener->afterValidation($this->event($child));

        self::assertEquals([$this->channels['hobbits']], $child->getChannels());
    }

    public function testDoingNothingIfItsNotATaxonomy()
    {
        $notTaxonomy = new Article();

        $this->listener->afterValidation($this->event($notTaxonomy, 'article'));

        self::assertEquals([], $notTaxonomy->getChannels());
    }

    public function testDoingNothingIfItsARoot()
    {
        $rootTaxonomy = $this->taxonomy('root', null);

        $this->listener->afterValidation($this->event($rootTaxonomy, 'article'));

        self::assertEquals([], $rootTaxonomy->getChannels());
    }

    private function taxonomy(
        string $id,
        string $parent = null,
        string ...$channels
    ): Taxonomy {
        $taxonomy = new Taxonomy();
        $taxonomy->setId($id);
        $taxonomy->setParentID($parent);
        $taxonomy->setContentType('taxonomy');
        foreach ($channels as $channel) {
            $taxonomy->addChannel($this->channels[$channel]);
        }

        return $taxonomy;
    }

    private function event(Content $content, string $type = 'taxonomy'): ValidationEvent
    {
        return new ValidationEvent(
            (new ContentType())->setId($type)->setClass(Taxonomy::class),
            new Document(Taxonomy::class),
            $content,
        );
    }
}
