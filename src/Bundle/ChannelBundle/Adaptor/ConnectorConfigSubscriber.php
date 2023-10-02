<?php

namespace Integrated\Bundle\ChannelBundle\Adaptor;

use Doctrine\ORM\EntityManagerInterface;
use Integrated\Bundle\ChannelBundle\Event\FormConfigEvent;
use Integrated\Bundle\ChannelBundle\Event\GetResponseConfigEvent;
use Integrated\Bundle\ChannelBundle\IntegratedChannelEvents;
use Integrated\Bundle\ChannelBundle\Model\ConfigurationException;
use Integrated\Bundle\ChannelBundle\Model\OauthConfigInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ConnectorConfigSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly OauthConfigInterface $config,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly EntityManagerInterface $em,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            IntegratedChannelEvents::CONFIG_EDIT_REQUEST => 'onRequest',
            IntegratedChannelEvents::CONFIG_CREATE_SUBMITTED => 'onSubmit',
            IntegratedChannelEvents::CONFIG_EDIT_SUBMITTED => 'onSubmit',
        ];
    }

    /** @throws ConfigurationException */
    public function onSubmit(FormConfigEvent $event): void
    {
        $config = $event->getConfig();

        if ($config->getAdapter() !== $this->config->getName()) {
            return;
        }

        $options = $config->getOptions();

        if ($options->has('token')) {
            return;
        }

        $session = new Session();
        $session->set('externalReturnId', $config->getId());

        $url = $this->config->prepareAuthLink($event, $options);

        dd($options, $url);
        if (null === $url) {
            return;
        }

        $event->setResponse(new RedirectResponse($url));
    }

    /** @throws ConfigurationException */
    public function onRequest(GetResponseConfigEvent $event)
    {
        $config = $event->getConfig();

        if ($config->getAdapter() !== $this->config->getName()) {
            return;
        }

        if ($this->config->handleCallback($event, $config->getOptions())) {
            $config->setOptions(clone $config->getOptions());
            dd($config);
            $this->em->flush();

            $event->setResponse(new RedirectResponse(
                $this->urlGenerator->generate(
                    'integrated_channel_config_edit',
                    ['id' => $config->getId()]
                ),
            ));
        }
    }
}
