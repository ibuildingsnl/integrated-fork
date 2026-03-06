<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Form\Type;

use Integrated\Bundle\ContentBundle\Document\SearchSelection\SearchSelection;
use Integrated\Bundle\ContentBundle\Form\Type\CheckboxSwitcherType;
use Integrated\Bundle\ContentBundle\Form\Type\SearchSelectionType;
use Integrated\Bundle\ContentBundle\Solr\Query\SortOption;
use Integrated\Bundle\ContentBundle\Solr\Query\SortOptions;
use Integrated\Bundle\UserBundle\Model\GroupManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SearchSelectionTypeTest extends TypeTestCase
{
    private AuthorizationCheckerInterface&MockObject $authorizationChecker;
    private GroupManagerInterface&MockObject $groupManager;

    protected function getExtensions(): array
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->authorizationChecker->method('isGranted')->willReturn(false);
        $this->groupManager = $this->createMock(GroupManagerInterface::class);

        $sortOptions = new SortOptions([
            new SortOption('time', 'Publication date', 'pub_time', 'desc'),
            new SortOption('title', 'Title', 'title_sort', 'asc'),
        ]);

        $type = new SearchSelectionType($this->authorizationChecker, $this->groupManager, $sortOptions);

        return [
            new PreloadedExtension(
                [
                    $type,
                    new CheckboxSwitcherType(),
                ],
                []
            ),
        ];
    }

    public function testItPrefillsSortingFieldsFromFilters(): void
    {
        $selection = new SearchSelection();
        $selection->setFilters([
            'sort' => 'custom:publication_start_vismagazine_index_date',
            'order' => 'desc',
        ]);

        $form = $this->factory->create(SearchSelectionType::class, $selection);

        self::assertSame('__custom__', (string) $form->get('sort')->getData());
        self::assertSame('desc', (string) $form->get('order')->getData());
        self::assertSame('publication_start_vismagazine_index_date', (string) $form->get('customSort')->getData());
    }

    public function testItProvidesCustomSortingOption(): void
    {
        $selection = new SearchSelection();
        $form = $this->factory->create(SearchSelectionType::class, $selection);

        $choices = $form->get('sort')->getConfig()->getOption('choices');

        self::assertArrayHasKey('Custom', $choices);
        self::assertSame('__custom__', $choices['Custom']);
    }

    public function testItAcceptsManualSortingValues(): void
    {
        $selection = new SearchSelection();
        $form = $this->factory->create(SearchSelectionType::class, $selection);

        $form->submit([
            'title' => 'Test selection',
            'sort' => 'pub_time',
            'order' => 'asc',
            'customSort' => '',
            'inMenu' => '1',
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertSame('pub_time', (string) $form->get('sort')->getData());
        self::assertSame('asc', (string) $form->get('order')->getData());
        self::assertSame('', (string) $form->get('customSort')->getData());
    }
}
