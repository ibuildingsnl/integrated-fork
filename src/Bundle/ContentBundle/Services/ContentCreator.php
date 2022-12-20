<?php

namespace Integrated\Bundle\ContentBundle\Services;

use Doctrine\Persistence\ObjectManager;
use Integrated\Bundle\ContentBundle\Form\Type\ActionsType;
use Integrated\Common\Content\Form\ContentFormType;
use Integrated\Common\ContentType\ResolverInterface;
use Integrated\Common\Security\PermissionInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ContentCreator
{
    public function __construct(
        private readonly ResolverInterface $typeResolver,
        private readonly ObjectManager $objectManager,
        private readonly AuthorizationCheckerInterface $checker,
        private readonly FormFactoryInterface $formFactory,
        private readonly RouterInterface $router,
    ) {
    }

    /**
     * @param Request $request The users original request
     *
     * @return FormInterface|null The form element for further processing, or null when the user cancels
     *
     * @throws AccessDeniedException When the user does not have permission to create this (type of) content
     */
    public function new(Request $request, ?string $route = null): ?FormInterface
    {
        $contentType = $this->typeResolver->getType($request->get('type'));

        $content = $contentType->create();

        if (!$this->checker->isGranted(PermissionInterface::WRITE, $contentType)) {
            throw new AccessDeniedException();
        }

        $form = $this->formFactory->create(ContentFormType::class, $content, [
            'method' => 'POST',
            'attr' => [
                'class' => 'content-form',
                'data-content-type' => $contentType->getId(),
            ],
            'content_type' => $contentType,
        ] + ($route ? ['action' => $this->router->generate($route)] : []));

        $form->add('actions', ActionsType::class, ['buttons' => ['create', 'cancel']]);

        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $form;
        }

        if ($form->get('actions')->getData() === 'cancel') {
            return null;
        }

        if (!$form->isValid()) {
            return $form;
        }

        $this->objectManager->persist($content);

        return $form;
    }
}
