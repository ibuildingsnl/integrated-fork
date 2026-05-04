<?php

namespace Integrated\Bundle\ContentBundle\Tests\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Controller\SearchSelectionController;
use Integrated\Bundle\ContentBundle\Services\SearchContentReferenced;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class SearchSelectionControllerSortingTest extends TestCase
{
    public function testCustomSortPreservesSafeMultiFieldSortExpression(): void
    {
        $filters = $this->applySortingSettings($this->createSortingForm(
            '__custom__',
            'desc',
            'profile_type_sort_text desc, title_sort asc'
        ));

        self::assertSame('custom:profile_type_sort_text desc, title_sort asc', $filters['sort']);
        self::assertSame('desc', $filters['order']);
    }

    public function testCustomSortDropsUnsafeSortParts(): void
    {
        $filters = $this->applySortingSettings($this->createSortingForm(
            '__custom__',
            'desc',
            'profile_type_sort_text desc, bad;field asc, title_sort asc'
        ));

        self::assertSame('custom:profile_type_sort_text desc, title_sort asc', $filters['sort']);
    }

    public function testCustomSortOptionsAreExpandedForAdminContentSearch(): void
    {
        $options = $this->applyCustomSorts([
            'sort' => 'custom:profile_type_sort_text desc, title_sort asc',
        ]);

        self::assertSame([
            'profile_type_sort_text' => 'desc',
            'title_sort' => 'asc',
        ], $options['sorts']);
    }

    /**
     * @param FormInterface<mixed> $form
     *
     * @return array<string, mixed>
     */
    private function applySortingSettings(FormInterface $form): array
    {
        $controller = new SearchSelectionController(
            $this->createMock(RequestStack::class),
            $this->createMock(DocumentManager::class),
            $this->createMock(PaginatorInterface::class),
            $this->createMock(SearchContentReferenced::class),
        );

        $method = new \ReflectionMethod($controller, 'applySearchSelectionSortingSettings');
        $method->setAccessible(true);

        return $method->invoke($controller, $form, []);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    private function applyCustomSorts(array $options): array
    {
        $controller = new SearchSelectionController(
            $this->createMock(RequestStack::class),
            $this->createMock(DocumentManager::class),
            $this->createMock(PaginatorInterface::class),
            $this->createMock(SearchContentReferenced::class),
        );

        $method = new \ReflectionMethod($controller, 'applyCustomSorts');
        $method->setAccessible(true);

        return $method->invoke($controller, $options);
    }

    /**
     * @return FormInterface<mixed>
     */
    private function createSortingForm(string $sort, string $order, string $customSort): FormInterface
    {
        $form = $this->createMock(FormInterface::class);
        $form->method('get')->willReturnMap([
            ['sort', $this->createFormField($sort)],
            ['order', $this->createFormField($order)],
            ['customSort', $this->createFormField($customSort)],
        ]);

        return $form;
    }

    /**
     * @return FormInterface<mixed>
     */
    private function createFormField(string $data): FormInterface
    {
        $field = $this->createMock(FormInterface::class);
        $field->method('getData')->willReturn($data);

        return $field;
    }
}
