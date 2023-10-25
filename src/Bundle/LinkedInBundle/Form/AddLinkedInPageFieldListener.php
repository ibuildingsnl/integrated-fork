<?php

namespace Integrated\Bundle\LinkedInBundle\Form;

//use JanuSoftware\Facebook\Facebook;
use Integrated\Bundle\LinkedInBundle\Connector\LinkedInConnector;
use Integrated\Bundle\LinkedInBundle\Connector\LinkedInFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Integrated\Common\Channel\Connector\Config\OptionsInterface;

class AddLinkedInPageFieldListener implements EventSubscriberInterface
{

//    public function __construct(LinkedIn $linkedinFactory)
    public function __construct(LinkedInFactory $linkedinFactory)
    {
        $this->linkedinFactory = $linkedinFactory;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
        ];
    }

    public function getRelatedOrganisations($client, $token): array {
        $requestOptions['headers'] = $this->linkedinFactory->getHeaders();

        $availableCompaniesRequest = $client->getAuthenticatedRequest('GET', 'api.linkedin.com/rest/organizationAcls?q=roleAssignee', $token, $requestOptions);

        $response = $client->getResponse($availableCompaniesRequest);

        $organizations = [];
        foreach (json_decode((string) $response->getBody())->elements as $element) {
            if ($element->state == 'APPROVED' && $element->role == 'ADMINISTRATOR') {
                $organizations[] = str_replace("urn:li:organization:", "", $element->organization);
            }
        }

        return $organizations;
    }

    public function getOrganisationDetails($client, $token, array $organizations): array {
        $requestOptions['headers'] = $this->linkedinFactory->getHeaders();

        $url = 'api.linkedin.com/rest/organizations?ids=List(' . implode(",", $organizations) . ')';

        $availableCompaniesRequest = $client->getAuthenticatedRequest('GET', $url, $token, $requestOptions);

        $response = $client->getResponse($availableCompaniesRequest);

        $responseBody = json_decode((string) $response->getBody())->results;

        $details = [];
        foreach ($organizations as $organizationId) {
            $details[$responseBody->{$organizationId}->localizedName] = $organizationId;
        }

//        dd($details);

//        foreach ($organizations as $organizationId) {
//            $details[$organizationId] = [
//                "id" => $organizationId,
//                "id_full" => "urn:li:organization:" . $organizationId,
//                'name' => $responseBody->{$organizationId}->localizedName
//            ];
//        }

        //array_combine(array_column($a, 'id'), array_column($a, 'name'));

        return $details;
    }

    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $formData = $event->getData();

        if ($formData && $formData['token']) {
            try {
                $client = $this->linkedinFactory->createClient($formData['token']);
                $organizations = $this->getRelatedOrganisations($client, $formData['token']);
                $pages = $this->getOrganisationDetails($client, $formData["token"], $organizations);

//                dd($oages);

//                $pages = [
//                    "water" => "vuur",
//                    "zon" => "aarde",
//                    "meerkoet" => "rare vogels",
//                ];

                ksort($pages);

                $form->add('page', ChoiceType::class, ['choices' => $pages, 'label' => 'Linkedin page']);

                if (\count($pages) == 0) {
                    $formData['apiStatus'] = 'The linked account does not seem to be administrator of a LinkedIn page';
                } else {
                    $formData['apiStatus'] = 'OK';

//                    $form->add('page_token', TextType::class, ['attr' => ['readonly' => 'true']]);
                }
            } catch (\Exception $e) {
                $formData['token'] = null;
                $formData['apiStatus'] = 'Token seems to be invalid. Save the form to get a new one. ('.$e->getMessage().')';
            }
        } else {
            $formData['apiStatus'] = 'Save the configuration to connect to LinkedIn.';
        }
    }
}

