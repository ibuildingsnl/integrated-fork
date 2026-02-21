<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Comment;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentBundle\Document\Content\News;
use Integrated\Bundle\ContentBundle\Document\Content\Product;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Company;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Person;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Document\Content\Video;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Document\ContentType\Embedded\Field;

class ContentTypeFixtures extends AbstractFixture
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->createContentType(Article::class, 'Article', [
            ['title', true],
            'subtitle',
            'authors',
            'intro',
            'content',
            'source',
            'disabled',
        ]));

        $manager->persist($this->createContentType(News::class, 'News', [
            ['title', true],
            'subtitle',
            'authors',
            'intro',
            'content',
            'source',
            'disabled',
        ]));

        $manager->persist($this->createContentType(Article::class, 'Blog', [
            ['title', true],
            'subtitle',
            'authors',
            'disabled',
        ]));

        $manager->persist($this->createContentType(Taxonomy::class, 'Taxonomy', [
            ['title', true],
            'disabled',
        ]));

        $manager->persist($this->createContentType(Company::class, 'Company', [
            ['name', true],
            'disabled',
        ]));

        $manager->persist($this->createContentType(Person::class, 'Author', [
            'firstName',
            ['lastName', true],
            'nickname',
            'gender',
            'description',
            'accountnumber',
            'phonenumbers',
            'email',
            'addresses',
            'jobs',
            'disabled',
        ]));

        $manager->persist($this->createContentType(Person::class, 'Employee', [
            'firstName',
            ['lastName', true],
            'nickname',
            'email',
            'disabled',
        ]));

        $manager->persist($this->createContentType(Comment::class, 'Comment', [
            ['title', true],
            'name',
            'email',
            'comment',
            'disabled',
        ]));

        $manager->persist($this->createContentType(Product::class, 'Book', [
            ['title', true],
            'content',
            'reference',
            'variant',
            'disabled',
        ]));

        $manager->persist($this->createContentType(Image::class, 'Image', [
            ['title', true],
            ['file', true],
            'disabled',
        ]));

        $manager->persist($this->createContentType(File::class, 'File', [
            ['title', true],
            ['file', true],
            'disabled',
        ]));

        $manager->persist($this->createContentType(Video::class, 'Video', [
            ['title', true],
            ['file', true],
            'disabled',
        ]));

        $manager->flush();
    }

    private function createContentType(string $class, string $name, array $fields): ContentType
    {
        $type = new ContentType();

        $type->setId(strtolower($name));
        $type->setClass($class);
        $type->setName($name);
        $type->setFields(iterator_to_array($this->createFields($fields)));

        $this->addReference($type->getId(), $type);

        return $type;
    }

    /**
     * @return \Generator<Field>
     */
    private function createFields(array $fields): \Generator
    {
        foreach ($fields as $field) {
            if (!\is_array($field)) {
                $field = [$field];
            }

            yield $this->createField(...$field);
        }
    }

    private function createField(string $name, bool $required = false): Field
    {
        $field = new Field();

        $field->setName($name);
        $field->setOptions(['required' => $required]);

        return $field;
    }
}
