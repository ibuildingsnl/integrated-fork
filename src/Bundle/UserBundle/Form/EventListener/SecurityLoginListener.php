<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\UserBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class SecurityLoginListener implements EventSubscriberInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $translationDomain;

    /**
     * Constructor.
     *
     * @param string $translationDomain
     */
    public function __construct(Request $request, TranslatorInterface $translator, $translationDomain = null)
    {
        $this->request = $request;

        $this->translator = $translator;
        $this->translationDomain = $translationDomain;
    }

    public function preSetData(FormEvent $event)
    {
        $request = $this->getRequest();
        $session = $this->getSession();

        $error = null;

        if ($request->attributes->has(\Symfony\Component\Security\Http\SecurityRequestAttributes::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(\Symfony\Component\Security\Http\SecurityRequestAttributes::AUTHENTICATION_ERROR);
        } elseif ($session && $session->has(\Symfony\Component\Security\Http\SecurityRequestAttributes::AUTHENTICATION_ERROR)) {
            $error = $session->remove(\Symfony\Component\Security\Http\SecurityRequestAttributes::AUTHENTICATION_ERROR);
        }

        if ($error instanceof AuthenticationException) {
            $event->getForm()->addError(new FormError(
                $this->translator->trans($error->getMessage(), [], $this->translationDomain),
                $error->getMessage(),
                [],
                null,
                $error
            ));
        }

        $event->setData(['_username' => $session && $session->has(\Symfony\Component\Security\Http\SecurityRequestAttributes::LAST_USERNAME) ? $session->get(\Symfony\Component\Security\Http\SecurityRequestAttributes::LAST_USERNAME) : '']);
    }

    /**
     * @return Request
     */
    protected function getRequest()
    {
        return $this->request;
    }

    protected function getSession()
    {
        return $this->request->getSession();
    }

    /**
     * @return TranslatorInterface
     */
    protected function getTranslator()
    {
        return $this->translator;
    }

    protected function getTranslationDomain()
    {
        return $this->translationDomain;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
        ];
    }
}
