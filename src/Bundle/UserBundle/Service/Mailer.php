<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\UserBundle\Service;

use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Bundle\UserBundle\Model\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class Mailer
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var KeyGenerator
     */
    private $keyGenerator;

    /**
     * @var ThemeManager
     */
    private $themeManager;

    /**
     * @var string|null
     */
    private $from;

    /**
     * @var string|null
     */
    private $name;

    public function __construct(MailerInterface $mailer, TranslatorInterface $translator, KeyGenerator $keyGenerator, ThemeManager $themeManager, ?string $from, ?string $name)
    {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->keyGenerator = $keyGenerator;
        $this->themeManager = $themeManager;
        $this->from = $from;
        $this->name = $name;
    }

    /**
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function sendPasswordResetMail(User $user, bool $website = false): void
    {
        $timestamp = time();
        $key = $this->keyGenerator->generateKey($timestamp, $user);

        $data = [
            'subject' => '[Integrated] '.$this->translator->trans('Password reset'),
            'user' => $user,
            'timestamp' => $timestamp,
            'key' => $key,
            'website' => $website,
        ];

        $message = (new TemplatedEmail())
            ->from(new Address($this->from, $this->name))
            ->to($user->getUserIdentifier())
            ->htmlTemplate($this->themeManager->locateTemplate('/mail/password.reset.html.twig'))
            ->subject($data['subject'])
            ->context($data);

        $this->mailer->send($message);
    }

    public function sendActivateMail(User $user)
    {
        $timestamp = time();
        $key = $this->keyGenerator->generateKey($timestamp, $user);

        $data = [
            'subject' => '[Integrated] '.$this->translator->trans('Registration'),
            'user' => $user,
            'timestamp' => $timestamp,
            'key' => $key,
        ];

        $message = (new TemplatedEmail())
            ->from(new Address($this->from, $this->name))
            ->to($user->getUserIdentifier())
            ->htmlTemplate($this->themeManager->locateTemplate('/mail/activate.html.twig'))
            ->subject($data['subject'])
            ->context($data);

        $this->mailer->send($message);
    }

    public function sendAccountActivatedMail(User $user, bool $website = false)
    {
        $timestamp = time();
        $data = [
            'subject' => '[Integrated] '.$this->translator->trans('Activated'),
            'user' => $user,
            'timestamp' => $timestamp,
            'website' => $website,
        ];

        $message = (new TemplatedEmail())
            ->from(new Address($this->from, $this->name))
            ->to($user->getUserIdentifier())
            ->htmlTemplate($this->themeManager->locateTemplate('/mail/account-activated.html.twig'))
            ->subject($data['subject'])
            ->context($data);

        $this->mailer->send($message);
    }
}
