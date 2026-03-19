<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\ContentBundle\Controller\ContentController;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Forms;

class ContentControllerSavePublicationGuardTest extends TestCase
{
    public function testGetPublicationsReturnsEmptyForUnsavedContentWithoutIdentifier(): void
    {
        $controller = (new \ReflectionClass(ContentController::class))->newInstanceWithoutConstructor();
        $content = new Article();

        $result = $this->invokeGetPublications($controller, $content);

        self::assertSame([], $result);
    }

    public function testGetPublicationsReturnsEmptyForInvalidNonStringIdentifier(): void
    {
        $controller = (new \ReflectionClass(ContentController::class))->newInstanceWithoutConstructor();
        $content = $this->createMock(Article::class);
        $content
            ->method('getId')
            ->willReturn(123);

        $result = $this->invokeGetPublications($controller, $content);

        self::assertSame([], $result);
    }

    public function testGetPublicationsQueriesRepositoryByContentIdentifier(): void
    {
        $controller = (new \ReflectionClass(ContentController::class))->newInstanceWithoutConstructor();

        $repository = $this->createMock(DocumentRepository::class);
        $repository
            ->expects(self::once())
            ->method('findBy')
            ->with(['content.$id' => 'existing-id'])
            ->willReturn([$this->createMock(Publication::class)]);

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager
            ->method('getRepository')
            ->willReturn($repository);

        $property = new \ReflectionProperty(ContentController::class, 'documentManager');
        $property->setAccessible(true);
        $property->setValue($controller, $documentManager);

        $content = new Article();
        $content->setId('existing-id');

        self::assertCount(1, $this->invokeGetPublications($controller, $content));
    }

    public function testHandleDuplicateSlugSaveFailureReturnsFriendlyMessageAndAddsSlugError(): void
    {
        $controller = (new \ReflectionClass(ContentController::class))->newInstanceWithoutConstructor();
        $form = Forms::createFormFactoryBuilder()
            ->getFormFactory()
            ->createBuilder(FormType::class)
            ->add('slug', TextType::class)
            ->getForm();

        $message = $this->invokeHandleDuplicateSlugSaveFailure(
            $controller,
            $form,
            new \RuntimeException('E11000 duplicate key error collection: content index: slug_1 dup key')
        );

        self::assertSame('This slug already exists. Please choose another slug.', $message);
        self::assertCount(1, iterator_to_array($form->get('slug')->getErrors()));
    }

    public function testHandleDuplicateSlugSaveFailureIgnoresOtherErrors(): void
    {
        $controller = (new \ReflectionClass(ContentController::class))->newInstanceWithoutConstructor();
        $form = Forms::createFormFactoryBuilder()
            ->getFormFactory()
            ->createBuilder(FormType::class)
            ->add('slug', TextType::class)
            ->getForm();

        $message = $this->invokeHandleDuplicateSlugSaveFailure(
            $controller,
            $form,
            new \RuntimeException('some other persistence problem')
        );

        self::assertNull($message);
        self::assertCount(0, iterator_to_array($form->get('slug')->getErrors()));
    }

    /**
     * @return array<int, mixed>
     */
    private function invokeGetPublications(ContentController $controller, Content $content): array
    {
        $method = new \ReflectionMethod(ContentController::class, 'getPublications');
        $method->setAccessible(true);

        /** @var array<int, mixed> $result */
        $result = $method->invoke($controller, $content);

        return $result;
    }

    /**
     * @param \Symfony\Component\Form\FormInterface<mixed> $form
     */
    private function invokeHandleDuplicateSlugSaveFailure(
        ContentController $controller,
        \Symfony\Component\Form\FormInterface $form,
        \Throwable $exception,
    ): ?string {
        $method = new \ReflectionMethod(ContentController::class, 'handleDuplicateSlugSaveFailure');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $form, $exception);

        return \is_string($result) ? $result : null;
    }
}
