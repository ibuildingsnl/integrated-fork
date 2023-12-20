<?php

namespace Integrated\Bundle\FacebookBundle\Form;

use Integrated\Bundle\ChannelBundle\Event\FormConfigEvent;
use Integrated\Bundle\ChannelBundle\IntegratedChannelEvents;
use Integrated\Bundle\FacebookBundle\Connector\FacebookClient;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Router;

class PopulateFacebookPageFieldListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly FacebookClient $client,
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
            $formData['api_status'] = 'Save the connector to connect to Facebook.';
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
                $form->add('page', ChoiceType::class, ['choices' => $choices]);
                $formData['api_status'] = 'OK';

                if (!isset($formData['page'])) {
                    return;
                }

                $token = $this->client->getPageToken($formData['token_secret'], $formData['page'], $pages);
                $formData['page_token'] = $token;
            }
        } catch (\Exception $exception) {
            $formData['token_secret'] = null;
            $form['api_status'] = 'Invalid token. Save the form to obtain a new token.';
        }
    }

    public function onSubmit(FormConfigEvent $event)
    {
        $form = $event->getForm();

        if ($form->getClickedButton() instanceof SubmitButton) {
            if ($form->getClickedButton()->getConfig()->getName() !== 'refresh') {
                return;
            }

            $this->client->clearPagesCache();
            $event->setResponse(new RedirectResponse($this->stack->getMainRequest()->getUri()));
        }
    }
}
