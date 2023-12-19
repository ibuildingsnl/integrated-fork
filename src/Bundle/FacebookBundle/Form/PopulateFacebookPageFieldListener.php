<?php

namespace Integrated\Bundle\FacebookBundle\Form;

use Integrated\Bundle\FacebookBundle\Connector\FacebookClient;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class PopulateFacebookPageFieldListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly FacebookClient $client,
    )
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
        ];
    }

    public function onPreSetData(FormEvent $event): void
    {
//        $formData['token_secret'] = null;
//        return;

        $form = $event->getForm();
        $formData = $event->getData();

        if(!isset($formData['token_secret'])) {
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

                if(!isset($formData['page'])) {
                    return;
                }

                $token = $this->client->getPageToken($formData['token_secret'], $formData['page'], $pages);
                $formData['page_token'] = $token;
            }
        } catch (\Exception $exception) {
            throw $exception;
            $formData['token_secret'] = null;
            $formData['api_status'] = 'Invalid token. Save the form to obtain a new token.';
        }
    }
}
