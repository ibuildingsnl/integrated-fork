<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\UserBundle\Controller\Website;

use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Bundle\UserBundle\Doctrine\UserManager;
use Integrated\Bundle\UserBundle\Form\Type\RegisterType;
use Integrated\Bundle\UserBundle\Handler\Exception\UniqueUserException;
use Integrated\Bundle\UserBundle\Handler\RegisterHandler;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Bundle\UserBundle\Service\KeyGenerator;
use Integrated\Bundle\UserBundle\Service\Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RegistrationController extends AbstractController
{
    private UserManager $userManager;
    private Mailer $mailer;
    private KeyGenerator $keyGenerator;
    private RegisterHandler $handler;
    private ThemeManager $themeManager;

    public function __construct(UserManager $userManager, Mailer $mailer, KeyGenerator $keyGenerator, RegisterHandler $handler, ThemeManager $themeManager)
    {
        $this->userManager = $userManager;
        $this->mailer = $mailer;
        $this->keyGenerator = $keyGenerator;
        $this->handler = $handler;
        $this->themeManager = $themeManager;
    }

    public function register(Request $request): Response
    {
        $user = new User();
        $user->setEnabled(false);
        $user->setScope($request->attributes->get('scope'));

        $form = $this->createForm(
            RegisterType::class,
            $user,
            ['action' => $this->generateUrl('integrated_user_website_registration_register')]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->mailer->sendActivateMail($this->handler->handle($user));
                $this->addFlash('success', 'Registration successful');

                return $this->redirectToRoute('integrated_user_website_registration_register');
            } catch (UniqueUserException $exception) {
                $form->get('username')->addError(new FormError($exception->getMessage()));
            }
        }

        return $this->render($this->themeManager->locateTemplate('registration/register.html.twig'), ['form' => $form]);
    }

    public function activate(Request $request, int $id, int $timestamp, string $key): Response
    {
        $valid = true;
        if (!$this->keyGenerator->isValidKey($id, $timestamp, $key)) {
            $valid = false;
        }

        if (!$user = $this->userManager->findOneBy(['id' => $id, 'scope' => $request->attributes->get('scope'), 'enabled' => false])) {
            $valid = false;
        }

        if (false === $valid) {
            $this->addFlash('danger', 'Activate link is invalid or expired');

            return $this->redirectToRoute('integrated_user_website_registration_register');
        }

        $user->setEnabled(true);
        $this->userManager->persist($user);

        $this->addFlash('success', 'Your account is activated');

        return $this->redirectToRoute('integrated_user_website_security_login');
    }
}
