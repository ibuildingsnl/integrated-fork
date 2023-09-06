<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Mailer;

use Integrated\Bundle\ThemeBundle\Exception\CircularFallbackException;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Error\Error;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class FormMailer
{
    /**
     * @var string
     */
    private $template = 'email/block/form.html.twig';

    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var ChannelContextInterface
     */
    private $channelContext;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var ThemeManager
     */
    private $themeManager;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $name;

    /**
     * @param string $from
     * @param string $name
     */
    public function __construct(MailerInterface $mailer, ChannelContextInterface $channelContext, Environment $twig, ThemeManager $themeManager, TranslatorInterface $translator, $from, $name)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->channelContext = $channelContext;
        $this->themeManager = $themeManager;
        $this->translator = $translator;
        $this->from = $from;
        $this->name = $name;
    }

    /**
     * @throws CircularFallbackException
     * @throws Error
     */
    public function send(array $data, array $emailAddresses = [], ?string $title = null)
    {
        if (!\count($emailAddresses)) {
            return;
        }

        $subject = $this->translator->trans('Form submitted');

        if ($channel = $this->channelContext->getChannel()) {
            $subject = '['.$channel->getName().'] '.$subject;
        }

        if ($title) {
            $subject .= ' - '.$title;
        }

        $message = (new Email())
            ->from(new Address($this->from, $this->name))
            ->bcc(...$emailAddresses)
            ->subject($subject)
            ->html($this->twig->render($this->themeManager->locateTemplate($this->template), ['data' => $data]));

        $this->mailer->send($message);
    }
}
