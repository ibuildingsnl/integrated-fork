<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Controller;

use Integrated\Bundle\ContentBundle\Controller\ContentController;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use PHPUnit\Framework\TestCase;

final class ContentControllerSeoMetaDescriptionAutofillTest extends TestCase
{
    public function testAutofillUsesIntroWhenSeoDescriptionIsEmpty(): void
    {
        $controller = (new \ReflectionClass(ContentController::class))->newInstanceWithoutConstructor();
        $article = new Article();
        $article->setIntro('Dit is de intro tekst voor SEO.');
        $article->setDescription('Fallback description');
        $article->setContent('<p>Body tekst</p>');

        $this->invokeApplyAutoSeoMetaDescriptionAutofill($controller, $article);

        self::assertSame('Dit is de intro tekst voor SEO.', $article->getSeoMetadata()?->getMetadescription());
    }

    public function testAutofillFallsBackToDescriptionWhenIntroIsEmpty(): void
    {
        $controller = (new \ReflectionClass(ContentController::class))->newInstanceWithoutConstructor();
        $article = new Article();
        $article->setIntro('');
        $article->setDescription('Dit is de description fallback voor SEO.');
        $article->setContent('<p>Body tekst</p>');

        $this->invokeApplyAutoSeoMetaDescriptionAutofill($controller, $article);

        self::assertSame('Dit is de description fallback voor SEO.', $article->getSeoMetadata()?->getMetadescription());
    }

    public function testAutofillUsesStrippedAndTruncatedBodyContent(): void
    {
        $controller = (new \ReflectionClass(ContentController::class))->newInstanceWithoutConstructor();
        $article = new Article();
        $article->setIntro('');
        $article->setDescription('');
        $article->setContent('<p>'.str_repeat('Lange content met woorden ', 20).'</p>');

        $this->invokeApplyAutoSeoMetaDescriptionAutofill($controller, $article);

        $description = (string) $article->getSeoMetadata()?->getMetadescription();
        self::assertNotSame('', $description);
        self::assertStringNotContainsString('<p>', $description);
        self::assertLessThanOrEqual(156, \function_exists('mb_strlen') ? mb_strlen($description) : \strlen($description));
    }

    public function testAutofillDoesNotOverrideExistingSeoDescription(): void
    {
        $controller = (new \ReflectionClass(ContentController::class))->newInstanceWithoutConstructor();
        $article = new Article();
        $article->setIntro('Nieuwe intro');
        $article->getSeoMetadata()?->setMetadescription('Bestaande SEO description');

        $this->invokeApplyAutoSeoMetaDescriptionAutofill($controller, $article);

        self::assertSame('Bestaande SEO description', $article->getSeoMetadata()?->getMetadescription());
    }

    private function invokeApplyAutoSeoMetaDescriptionAutofill(ContentController $controller, object $content): void
    {
        $method = new \ReflectionMethod(ContentController::class, 'applyAutoSeoMetaDescriptionAutofill');
        $method->setAccessible(true);
        $method->invoke($controller, $content);
    }
}
