<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\UserBundle\Controller;

use Integrated\Bundle\UserBundle\Doctrine\UserManager;
use Integrated\Bundle\UserBundle\Form\Type\LoginFormType;
use Integrated\Bundle\UserBundle\Form\Type\PasswordChangeType;
use Integrated\Bundle\UserBundle\Form\Type\PasswordResetType;
use Integrated\Bundle\UserBundle\Service\KeyGenerator;
use Integrated\Bundle\UserBundle\Service\Mailer;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends AbstractController
{
    private const PASSWORD_RESET_MAX_ATTEMPTS = 5;
    private const PASSWORD_RESET_WINDOW_SECONDS = 900;
    private UserManager $userManager;
    private Mailer $mailer;
    private KeyGenerator $keyGenerator;
    private CacheItemPoolInterface $passwordResetThrottleCache;

    public function __construct(UserManager $userManager, Mailer $mailer, KeyGenerator $keyGenerator, CacheItemPoolInterface $passwordResetThrottleCache)
    {
        $this->userManager = $userManager;
        $this->mailer = $mailer;
        $this->keyGenerator = $keyGenerator;
        $this->passwordResetThrottleCache = $passwordResetThrottleCache;
    }

    public function login(): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('integrated_content_content_index');
        }

        $form = $this->createForm(
            LoginFormType::class,
            null,
            ['action' => $this->generateUrl('integrated_user_check')]
        );

        return $this->render('@IntegratedUser/security/login.html.twig', ['form' => $form]);
    }

    public function passwordReset(Request $request): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('integrated_content_content_index');
        }

        $form = $this->createForm(
            PasswordResetType::class,
            null,
            ['action' => $this->generateUrl('integrated_user_password_reset')]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->consumePasswordResetToken($request, (string) $form->get('email')->getData())) {
                $this->addFlash('warning', 'Too many password reset attempts. Please wait 15 minutes and try again.');

                return $this->redirectToRoute('integrated_user_password_reset');
            }

            if ($user = $this->userManager->findEnabledByUsernameOrEmailAndScope($form->get('email')->getData())) {
                if ($user->isEnabled()) {
                    $this->mailer->sendPasswordResetMail($user);
                }
            }

            $this->addFlash('success', 'If your e-mail address has an account, a password reset link has been sent');

            return $this->redirectToRoute('integrated_user_login');
        }

        return $this->render('@IntegratedUser/security/password_reset.html.twig', ['form' => $form]);
    }

    private function consumePasswordResetToken(Request $request, string $email): bool
    {
        $clientIp = (string) ($request->getClientIp() ?? 'unknown');
        $normalizedEmail = strtolower(trim($email));
        $cacheKey = 'integrated_user_password_reset_'.hash('sha256', $clientIp.'|'.$normalizedEmail);
        $now = time();

        $cacheItem = $this->passwordResetThrottleCache->getItem($cacheKey);
        $payload = $cacheItem->isHit() && \is_array($cacheItem->get()) ? $cacheItem->get() : [];

        $count = (int) ($payload['count'] ?? 0);
        $windowResetAt = (int) ($payload['window_reset_at'] ?? 0);
        if ($windowResetAt <= $now) {
            $count = 0;
            $windowResetAt = $now + self::PASSWORD_RESET_WINDOW_SECONDS;
        }

        $count++;

        $cacheItem->set([
            'count' => $count,
            'window_reset_at' => $windowResetAt,
        ]);
        $cacheItem->expiresAt((new \DateTimeImmutable())->setTimestamp($windowResetAt));
        $this->passwordResetThrottleCache->save($cacheItem);

        return $count <= self::PASSWORD_RESET_MAX_ATTEMPTS;
    }

    public function passwordChange(Request $request, int $id, int $timestamp, string $key): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('integrated_user_profile_index');
        }

        if (!$this->keyGenerator->isValidKey($id, $timestamp, $key)) {
            $this->addFlash('danger', 'Password reset link is invalid or expired');

            return $this->redirectToRoute('integrated_user_login');
        }

        $form = $this->createForm(
            PasswordChangeType::class,
            null,
            ['action' => $this->generateUrl('integrated_user_password_change', ['id' => $id, 'timestamp' => $timestamp, 'key' => $key])]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->userManager->changePassword($id, $form->get('password')->getData())) {
                $this->addFlash('success', 'Your password has been changed');
            } else {
                $this->addFlash('danger', 'An error occurred while changing the password');
            }

            return $this->redirectToRoute('integrated_user_login');
        }

        return $this->render('@IntegratedUser/security/password_reset.html.twig', ['form' => $form]);
    }
}
