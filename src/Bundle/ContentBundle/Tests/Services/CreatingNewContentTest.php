<?php

namespace Integrated\Bundle\ContentBundle\Tests\Services;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Document\ContentType\Embedded\Field;
use Integrated\Bundle\ContentBundle\Security\ContentTypeVoter;
use Integrated\Bundle\ContentBundle\Services\ContentCreator;
use Integrated\Bundle\FormTypeBundle\Form\Extension\ButtonTypeExtension;
use Integrated\Bundle\UserBundle\Model\Group;
use Integrated\Bundle\UserBundle\Model\Role;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Common\Content\Extension\EventDispatcher;
use Integrated\Common\Content\Form\ContentFormType;
use Integrated\Common\ContentType\Resolver\MemoryResolver;
use Integrated\Common\ContentType\ResolverInterface;
use Integrated\Common\Form\Mapping\Metadata\Field as Attribute;
use Integrated\Common\Form\Mapping\MetadataFactoryInterface;
use Integrated\Common\Form\Mapping\MetadataInterface;
use Integrated\Common\Security\Permission;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\TestBrowserToken;
use Symfony\Component\Form\Extension\HttpFoundation\Type\FormTypeHttpFoundationExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\Strategy\UnanimousStrategy;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CreatingNewContentTest extends TypeTestCase
{
    private TokenStorage $tokenStorage;
    private ContentCreator $creator;
    private ObjectManager&MockObject $objectManager;
    private RouterInterface&MockObject $router;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenStorage = new TokenStorage();
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->router = $this->createMock(RouterInterface::class);

        $this->creator = new ContentCreator(
            new MemoryResolver([
                'taxonomy' => $this->contentType('taxonomy', Taxonomy::class, [
                    $this->field('title'),
                    $this->field('description'),
                ], [$this->permission('ok', Permission::WRITE)]),
                'article' => $this->contentType('article', Article::class, [
                    $this->field('title'),
                    $this->field('subtitle'),
                    $this->field('intro'),
                ], [$this->permission('writers', Permission::WRITE)]),
            ]),
            $this->objectManager,
            new AuthorizationChecker(
                $this->tokenStorage,
                new AccessDecisionManager(
                    [new ContentTypeVoter($this->createMock(ObjectRepository::class))],
                    new UnanimousStrategy(),
                ),
            ),
            $this->factory,
            $this->router,
        );
    }

    protected function getExtensions()
    {
        $metadataFactory = $this->createMock(MetadataFactoryInterface::class);
        $metadata = $this->createMock(MetadataInterface::class);
        $metadataFactory->method('getMetadata')->willReturn($metadata);
        $metadata->method('getFields')->willReturn([
            new Attribute('title'),
            new Attribute('description'),
            new Attribute('subtitle'),
            new Attribute('intro'),
        ]);
        return [new PreloadedExtension([new ContentFormType(
            $metadataFactory,
            $this->createMock(ResolverInterface::class),
            new EventDispatcher()
        )], [])];
    }

    protected function getTypeExtensions()
    {
        return [
            new ButtonTypeExtension(),
            new FormTypeHttpFoundationExtension(),
        ];
    }

    /** @test */
    public function generatingTheTaxonomyForm()
    {
        $this->tokenStorage->setToken($this->user('ok'));
        $form = $this->creator->new(Request::create('foo/bar', 'GET', [
            "type" => "taxonomy",
        ]));

        self::assertFalse($form->isSubmitted());
        self::assertEquals('', $form->getConfig()->getAction());
    }

    /** @test */
    public function generatingTheTaxonomyFormWithActionRoute()
    {
        $this->router->method('generate')->with('route')->willReturn('foo/bar');
        $this->tokenStorage->setToken($this->user('ok'));
        $form = $this->creator->new(Request::create('foo/bar', 'GET', [
            "type" => "taxonomy",
        ]), 'route');

        self::assertFalse($form->isSubmitted());
        self::assertEquals('foo/bar', $form->getConfig()->getAction());
    }

    /** @test */
    public function notShowingTaxonomyFormWhenNotHavingWriteAccess()
    {
        $this->tokenStorage->setToken($this->user('not-ok'));

        $this->expectException(AccessDeniedException::class);

        $this->creator->new(Request::create('foo/bar', 'POST', [
            "type" => "taxonomy",
            "integrated_content" => [
                "actions" => ["create" => ""],
                "title" => "Tax!",
                "description" => "Taxonomy!!!",
                "primaryChannel" => "",
            ],
        ]));
    }

    /** @test */
    public function creatingANewTaxonomyItem()
    {
        $this->tokenStorage->setToken($this->user('ok'));

        $expected = new Taxonomy();
        $expected->setTitle('Tax!');
        $expected->setDescription('Taxonomy!!!');
        $expected->setContentType('taxonomy');

        $this->shouldPersist($expected);

        $form = $this->creator->new(Request::create('foo/bar', 'POST', [
            "type" => "taxonomy",
            "integrated_content" => [
                "actions" => ["create" => ""],
                "title" => "Tax!",
                "description" => "Taxonomy!!!",
                "primaryChannel" => "",
            ],
        ]));

        self::assertTrue($form->isSubmitted());
        self::assertTrue($form->isValid());
    }

    /** @test */
    public function cancellingTheCreationOfANewTaxonomyItem()
    {
        $this->tokenStorage->setToken($this->user('ok'));

        $form = $this->creator->new(Request::create('foo/bar', 'POST', [
            "type" => "taxonomy",
            "integrated_content" => [
                "actions" => ["cancel" => ""],
                "title" => "Tax!",
                "description" => "Taxonomy!!!",
                "primaryChannel" => "",
            ],
        ]));

        self::assertNull($form);
    }

    /** @test */
    public function creatingANewArticle()
    {
        $this->tokenStorage->setToken($this->user('writers'));

        $expected = new Article();
        $expected->setTitle('Art!');
        $expected->setSubtitle('Article!!!');
        $expected->setIntro('Amazing Article');
        $expected->setContentType('article');

        $this->shouldPersist($expected);

        $form = $this->creator->new(Request::create('foo/bar', 'POST', [
            "type" => "article",
            "integrated_content" => [
                "actions" => ["create" => ""],
                "title" => "Art!",
                "subtitle" => "Article!!!",
                "intro" => "Amazing Article",
                "primaryChannel" => "",
            ],
        ]));

        self::assertTrue($form->isSubmitted());
        self::assertTrue($form->isValid());
    }

    private function contentType(string $id, string $class, array $fields, array $permissions = []): ContentType
    {
        $type = new ContentType();
        $type->setId($id);
        $type->setClass($class);
        $type->setFields($fields);
        foreach ($permissions as $permission) {
            $type->addPermission($permission);
        }
        return $type;
    }

    private function field(string $name, array $options = []): Field
    {
        $field = new Field();
        $field->setName($name);
        $field->setOptions($options);
        return $field;
    }

    private function user(string $group, string ...$roles): TokenInterface
    {
        $user = new User();
        $user->addGroup(new Group($group));
        foreach ($roles as $role) {
            $user->addRole(new Role($role));
        }
        $token = new TestBrowserToken($roles, $user);
        $token->setAuthenticated(true);
        return $token;
    }

    private function permission(string $group, int $mask): Permission
    {
        $permission = new Permission();
        $permission->setGroup($group);
        $permission->setMask($mask);
        return $permission;
    }

    private function shouldPersist(Content $expected): void
    {
        $this->objectManager->expects($this->once())->method('persist')->with(new IsEqual($expected, 1));
    }
}
