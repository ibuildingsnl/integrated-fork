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
use Integrated\Bundle\UserBundle\Form\Type\LoginFormType;
use Integrated\Bundle\UserBundle\Form\Type\PasswordChangeType;
use Integrated\Bundle\UserBundle\Form\Type\PasswordResetType;
use Integrated\Bundle\UserBundle\Service\KeyGenerator;
use Integrated\Bundle\UserBundle\Service\Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends AbstractController
{
    private UserManager $userManager;
    private Mailer $mailer;
    private KeyGenerator $keyGenerator;
    private ThemeManager $themeManager;

    public function __construct(UserManager $userManager, Mailer $mailer, KeyGenerator $keyGenerator, ThemeManager $themeManager)
    {
        $this->userManager = $userManager;
        $this->mailer = $mailer;
        $this->keyGenerator = $keyGenerator;
        $this->themeManager = $themeManager;
    }

    public function login(Request $request): Response
    {
        $form = $this->createForm(
            LoginFormType::class,
            null,
            ['action' => $this->generateUrl('integrated_user_website_security_check', $request->query->all())]
        );

        return $this->render($this->themeManager->locateTemplate('security/login.html.twig'), ['form' => $form]);
    }

    public function passwordReset(Request $request): Response
    {
        $form = $this->createForm(
            PasswordResetType::class,
            null,
            ['action' => $this->generateUrl('integrated_user_website_security_password_reset')]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($user = $this->userManager->findEnabledByUsernameAndScope($form->get('email')->getData(), $request->attributes->get('scope'))) {
                if ($user->isEnabled()) {
                    $this->mailer->sendPasswordResetMail($user, true);
                }
            }

            $this->addFlash('success', 'If your e-mail address has an account, a password reset link has been sent');

            return $this->redirectToRoute('integrated_user_website_security_login');
        }

        return $this->render($this->themeManager->locateTemplate('security/password_reset.html.twig'), ['form' => $form]);
    }

    public function passwordChange(Request $request, int $id, int $timestamp, string $key): Response
    {
        if (!$this->keyGenerator->isValidKey($id, $timestamp, $key)) {
            $this->addFlash('danger', 'Password reset link is invalid or expired');

            return $this->redirectToRoute('integrated_user_website_security_password_reset');
        }

        $form = $this->createForm(
            PasswordChangeType::class,
            null,
            ['action' => $this->generateUrl('integrated_user_website_security_password_change', ['id' => $id, 'timestamp' => $timestamp, 'key' => $key])]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->userManager->changePassword($id, $form->get('password')->getData())) {
                $this->addFlash('success', 'Your password has been changed');
            } else {
                $this->addFlash('danger', 'An error occurred while changing the password');
            }

            return $this->redirectToRoute('integrated_user_website_security_login');
        }

        return $this->render($this->themeManager->locateTemplate('security/password_reset.html.twig'), ['form' => $form]);
    }
}
