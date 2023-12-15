<?php

namespace Integrated\Bundle\FacebookBundle\Form;

use Integrated\Bundle\FacebookBundle\Connector\FacebookClient;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

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
            }
        } catch (\Exception $exception) {
            $formData['token'] = null;
            $form['api_status'] = 'Invalid token. Save the form to obtain a new token.';
        }
    }
}
