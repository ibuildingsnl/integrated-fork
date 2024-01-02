<?php

namespace Integrated\Bundle\InstagramBundle\Form;

use Integrated\Bundle\ChannelBundle\Event\FormConfigEvent;
use Integrated\Bundle\ChannelBundle\IntegratedChannelEvents;
use Integrated\Bundle\FacebookBundle\Connector\FacebookClient;
use Integrated\Bundle\FormTypeBundle\Form\Type\RefreshableChoiceType;
use Integrated\Bundle\InstagramBundle\Connector\InstagramClient;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Router;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class PopulateInstagramPageFieldListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly InstagramClient $client,
        private readonly RequestStack $stack,
    )
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
            IntegratedChannelEvents::CONFIG_EDIT_SUBMITTED => 'onSubmit',
        ];
    }

    public function onPreSetData(FormEvent $event): void
    {
        $form = $event->getForm();
        $formData = $event->getData();

        if (!isset($formData['token_secret'])) {
            $formData['api_status'] = 'Save the connector to connect to Instagram.';
            return;
        }

        try {
            $pages = $this->client->getPages($formData['token_secret'])['data'];
            $choices = [];

            foreach ($pages as $page) {
                $choices[$page['name']] = $page['id'];
            }

            if (empty($choices)) {
                $formData['api_status'] = 'No pages available for selection.';
            } else {
                $form->add('page', RefreshableChoiceType::class, ['select' => ['choices' => $choices]]);
                $formData['api_status'] = 'OK';

                if (!isset($formData['page_token'])) {
                    return;
                }

                $token = $this->client->getPageToken($formData['token_secret'], $formData['page']['choice'], $pages);
                $formData['page_token'] = $token;
            }
        } catch (\Exception $exception) {
            $formData['token_secret'] = null;
            $formData['api_status'] = 'Invalid token. Save the form to obtain a new token.';
        }
    }

    public function onSubmit(FormConfigEvent $event)
    {
        $form = $event->getForm();

        if ($form->getClickedButton() instanceof SubmitButton) {
            if ($form->getClickedButton()->getConfig()->getName() !== 'refresh') {
                return;
            }

            $this->client->clearPagesCache($form->getData()->getOptions()->get('token_secret'));
            $event->setResponse(new RedirectResponse($this->stack->getMainRequest()->getUri()));
        }
    }
}
