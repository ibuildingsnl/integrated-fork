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
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Comment;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Address;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Author;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Job;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Phonenumber;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentBundle\Document\Content\News;
use Integrated\Bundle\ContentBundle\Document\Content\Product;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Company;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Person;

class ContentFixtures extends AbstractFixture implements DependentFixtureInterface
{
    public const FUNCTIONS = [
        'Staff',
        'Assistant manager',
        'Manager',
        'Chairman',
        'President',
    ];

    public const DEPARTMENTS = [
        'Services',
        'Marketing',
        'Human Resources',
        'Financial',
        'Purchasing',
        'Sales',
        'IT',
        'Inventory',
        'Quality Asurance',
        'Insurance',
        'Licenses',
        'Operational',
        'Customers',
        'Staff',
        'Customer Service',
        'Organizational',
        'Research & Development',
        'Market Development',
        'Business Development',
        'Management',
        'Engineering',
    ];

    private Generator $faker;

    /**
     * @var Person[]
     */
    private array $authors;

    /**
     * @var Company[]
     */
    private array $companies;

    /**
     * @var Image[]
     */
    private array $images;

    public function __construct(?Generator $faker = null)
    {
        $this->faker = $faker ?: Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        $this->faker->seed(123456789);

        try {
            $this->authors = [];
            $this->companies = [];
            $this->images = $manager->getRepository(Image::class)->findBy(['contentType' => 'image'], ['__id' => 1]);

            for ($x = 0; $x < 30; ++$x) {
                $manager->persist($this->createCompany());
            }

            for ($x = 0; $x < 15; ++$x) {
                $manager->persist($this->createAuthor());
            }

            for ($x = 0; $x < 100; ++$x) {
                $manager->persist($this->createArticle('article'));
            }

            for ($x = 0; $x < 20; ++$x) {
                $manager->persist($this->createArticle('news'));
            }

            for ($x = 0; $x < 50; ++$x) {
                $manager->persist($this->createArticle('blog'));
            }

            for ($x = 0; $x < 100; ++$x) {
                $manager->persist($this->createComment());
            }

            for ($x = 0; $x < 20; ++$x) {
                $manager->persist($this->createProduct());
            }

            $manager->flush();
        } finally {
            $this->authors = [];
            $this->companies = [];
            $this->images = [];

            $this->faker->seed(); // reset seed to be random again just to be on the safe side
        }
    }

    private function createCompany(): Company
    {
        $object = new Company();

        $object->setContentType('company');
        $object->setName($this->faker->company());
        $object->setDescription($this->faker->paragraph(4));
        $object->setAccountnumber($this->faker->numberBetween(20000, 30000));

        foreach ($this->getPhonenumbers() as $number) {
            $object->addPhonenumber($number);
        }

        $object->setEmail($this->faker->email());

        foreach ($this->getAddresses() as $address) {
            $object->addAddress($address);
        }

        $object->setDisabled($this->faker->boolean(10));
        $object->setChannels([$this->getReference(ChannelFixtures::CHANNEL, Channel::class)]);

        $this->companies[] = $object;

        return $object;
    }

    private function createAuthor(): Person
    {
        $object = new Person();

        $object->setContentType('author');
        $object->setFirstName($this->faker->firstName());
        $object->setLastName($this->faker->lastName());

        if ($this->faker->boolean(15)) {
            $object->setNickname($this->faker->userName());
        }

        $object->setGender($this->faker->randomElement(['male', 'female']));
        $object->setPrefix($this->faker->title($object->getGender()));
        $object->setDescription($this->faker->paragraph(2));
        $object->setAccountnumber($this->faker->numberBetween(5000, 9000));

        foreach ($this->getPhonenumbers() as $number) {
            $object->addPhonenumber($number);
        }

        $object->setEmail($this->faker->email());

        if ($this->faker->boolean(75)) {
            $object->addAddress($this->getAddress());
        }

        foreach ($this->getJobs() as $job) {
            $object->addJob($job);
        }

        $object->setDisabled($this->faker->boolean(10));
        $object->setChannels([$this->getReference(ChannelFixtures::CHANNEL, Channel::class)]);

        $this->authors[] = $object;

        return $object;
    }

    private function createArticle(string $type): Article
    {
        if ($type === 'news') {
            $object = new News();
        } else {
            $object = new Article();
        }

        $object->setContentType($type);
        $object->setTitle($this->faker->sentence());

        if ($this->faker->boolean(15)) {
            $object->setSubtitle($this->faker->sentence());
        }

        if ($this->faker->boolean(40)) {
            $object->setIntro($this->faker->paragraph());
        }

        $object->setContent($this->faker->paragraph(10));

        foreach ($this->getAuthors() as $author) {
            $object->addAuthor($author);
        }

        if ($this->faker->boolean(40)) {
            $object->setSource($this->faker->paragraph());
        }

        $object->setLocale($this->faker->locale());
        $object->setDisabled($this->faker->boolean(10));
        $object->setChannels([$this->getReference(ChannelFixtures::CHANNEL, Channel::class)]);

        if (\in_array($type, ['article', 'blog']) && $this->faker->boolean(40)) {
            $relation = new Relation();

            $relation->setRelationId('media');
            $relation->setRelationType('embedded');

            foreach ($this->faker->randomElements($this->images, $this->faker->biasedNumberBetween(1, 2)) as $image) {
                $relation->addReference($image);
            }

            $object->addRelation($relation);
        }

        return $object;
    }

    private function createComment(): Comment
    {
        $object = new Comment();

        $object->setContentType('comment');
        $object->setTitle($this->faker->sentence());
        $object->setName($this->faker->firstName().' '.$this->faker->lastName());
        $object->setEmail($this->faker->email());
        $object->setComment($this->faker->paragraph());
        $object->setDisabled($this->faker->boolean(10));
        $object->setChannels([$this->getReference(ChannelFixtures::CHANNEL, Channel::class)]);

        return $object;
    }

    private function createProduct(): Product
    {
        $object = new Product();

        $object->setContentType('book');
        $object->setTitle($this->faker->sentence(5));
        $object->setReference($this->faker->ean13());
        $object->setVariant($this->faker->word());
        $object->setContent($this->faker->paragraph(6));
        $object->setDisabled($this->faker->boolean(10));
        $object->setChannels([$this->getReference(ChannelFixtures::CHANNEL, Channel::class)]);

        return $object;
    }

    /**
     * @return \Generator<Phonenumber>
     */
    private function getPhonenumbers(): \Generator
    {
        for ($x = $this->faker->biasedNumberBetween(1, 3); $x > 0; --$x) {
            $object = new Phonenumber($this->faker->phoneNumber());

            if ($this->faker->boolean(60)) {
                $object->setType($this->faker->randomElement(['mobile', 'home', 'work']));
            }

            yield $object;
        }
    }

    /**
     * @return \Generator<Address>
     */
    private function getAddresses(): \Generator
    {
        for ($x = $this->faker->biasedNumberBetween(1, 2); $x > 0; --$x) {
            yield $this->getAddress();
        }
    }

    private function getAddress(): Address
    {
        $object = new Address();

        if ($this->faker->boolean(60)) {
            $object->setType($this->faker->randomElement(['Postal address', 'Visiting address']));
        }

        $object->setAddress1($this->faker->address());
        $object->setZipcode($this->faker->postcode());
        $object->setCity($this->faker->city());
        $object->setCountry($this->faker->country());

        return $object;
    }

    /**
     * @return \Generator<Job>
     */
    private function getJobs(): \Generator
    {
        if ($this->faker->boolean(20)) {
            return;
        }

        for ($x = $this->faker->biasedNumberBetween(1, 2); $x > 0; --$x) {
            $object = new Job();

            $object->setFunction($this->faker->randomElement(self::FUNCTIONS));
            $object->setDepartment($this->faker->randomElement(self::DEPARTMENTS));
            $object->setCompany($this->faker->randomElement($this->companies));

            yield $object;
        }
    }

    /**
     * @return \Generator<Author>
     */
    private function getAuthors(): \Generator
    {
        foreach ($this->faker->randomElements($this->authors, $this->faker->biasedNumberBetween(1, 3)) as $person) {
            $object = new Author();

            if ($this->faker->boolean(60)) {
                $object->setType($this->faker->randomElement(['Main author', 'Sub author']));
            }

            $object->setPerson($person);

            yield $object;
        }
    }

    public function getDependencies(): array
    {
        return [
            ChannelFixtures::class,
            MediaFixtures::class,
        ];
    }
}
