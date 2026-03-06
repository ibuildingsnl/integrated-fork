<?php

namespace Integrated\Bundle\ChannelBundle\Adaptor;

use Doctrine\ORM\EntityManagerInterface;
use Integrated\Bundle\ChannelBundle\Event\FormConfigEvent;
use Integrated\Bundle\ChannelBundle\Event\GetResponseConfigEvent;
use Integrated\Bundle\ChannelBundle\IntegratedChannelEvents;
use Integrated\Bundle\ChannelBundle\Model\ConfigurationException;
use Integrated\Bundle\ChannelBundle\Model\OauthConfigInterface;
use Integrated\Bundle\ChannelBundle\Services\ChannelTokenService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ConnectorConfigSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly OauthConfigInterface $config,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly EntityManagerInterface $em,
        private readonly ChannelTokenService $channelTokenService,
    ) {
    }

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

        $channelId = $config->getChannels()[0] ?? null;
        if (!\is_string($channelId) || $channelId === '') {
            return;
        }
        $channelToken = $this->channelTokenService->getChannelTokenFor($channelId);

        if ($channelToken) {
            return;
        }

        $url = $this->config->prepareAuthLink($event, $options);

        if (null === $url) {
            return;
        }

        if ($event->getRequest()->hasSession()) {
            $event->getRequest()->getSession()->set('externalReturnId', $config->getId());
        }

        $config->setOptions(clone $config->getOptions());
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
            $this->em->flush();
            if ($event->getRequest()->hasSession()) {
                $event->getRequest()->getSession()->remove('externalReturnId');
            }

            $event->setResponse(new RedirectResponse(
                $this->urlGenerator->generate(
                    'integrated_channel_config_edit',
                    ['id' => $config->getId()]
                ),
            ));
        }
    }
}
