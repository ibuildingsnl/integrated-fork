<?php

namespace Integrated\Bundle\BrandBundle\Controller;

use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\ChannelLink;
use Integrated\Bundle\BrandBundle\EventListener\ConnectorDeletionRedirectListener;
use Integrated\Bundle\ChannelBundle\Event\FilterResponseConfigEvent;
use Integrated\Bundle\ChannelBundle\Event\FormConfigEvent;
use Integrated\Bundle\ChannelBundle\Event\GetResponseConfigEvent;
use Integrated\Bundle\ChannelBundle\Form\Type\ActionsType;
use Integrated\Bundle\ChannelBundle\Form\Type\ConfigFormType;
use Integrated\Bundle\ChannelBundle\IntegratedChannelEvents;
use Integrated\Bundle\ChannelBundle\Model\Config;
use Integrated\Bundle\ChannelBundle\Model\ConfigInterface;
use Integrated\Common\Channel\Connector\Adapter\RegistryInterface;
use Integrated\Common\Channel\Connector\Config\ConfigManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ConnectorController extends AbstractController
{
    public function __construct(
        private readonly ConfigManagerInterface $configs,
        private readonly RegistryInterface $adapters,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function configure(Request $request, Brand $brand, ChannelLink $link): Response
    {
        if (!$this->isGranted('ROLE_CHANNEL_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $config = null;
        $new = true;
        foreach ($this->configs->findByChannel($link->channel) as $config) {
            if ($config->getAdapter() === $link->type->connector) {
                $new = false;
                break;
            }
        }
        try {
            $adapter = $this->adapters->getAdapter($link->type->connector);
        } catch (\Throwable $exception) {
            $message = "It seems there is no {$link->type->connector} connector available. Please contact an administrator.";
            $this->addFlash('error', $message);
            throw $this->createNotFoundException($message, $exception);
        }
        if ($new || !$config instanceof ConfigInterface) {
            $config = new Config();
            $config->setAdapter($adapter->getManifest()->getName());
            $config->setChannels([$link->channel]);
            $config->setName("{$brand->getName()} {$link->getName()} connector");
        } else {
            $request->getSession()->set(
                \sprintf(ConnectorDeletionRedirectListener::SESSION_PATH, $config->getAdapter()),
                $this->generateUrl('integrated_content_brand_edit', ['id' => $brand->getId()])
            );
        }

        $event = new GetResponseConfigEvent($config, $request);

        if ($this->dispatcher->dispatch($event, IntegratedChannelEvents::CONFIG_CREATE_REQUEST)?->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->createForm(ConfigFormType::class, $config, [
            'adapter' => $adapter,
            'method' => 'PUT',
        ]);
        $form->add('actions', ActionsType::class, ['buttons' => [
            $new ? 'create' : 'save',
            'cancel',
        ]]);
        $form->remove('channels');

        $form->handleRequest($request);

        if ($form->get('actions')->getData() == 'cancel') {
            return $this->redirectToRoute('integrated_content_brand_edit', ['id' => $brand->getId()]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->configs->persist($config);

            if ($new) {
                $submitEvent = IntegratedChannelEvents::CONFIG_CREATE_SUBMITTED;
                $respondEvent = IntegratedChannelEvents::CONFIG_CREATE_RESPONSE;
                $message = 'The config %s is saved';
            } else {
                $submitEvent = IntegratedChannelEvents::CONFIG_EDIT_SUBMITTED;
                $respondEvent = IntegratedChannelEvents::CONFIG_EDIT_RESPONSE;
                $message = 'The changes to the config %s are saved';
            }

            $response = $this->dispatcher
                ->dispatch(new FormConfigEvent($config, $request, $form), $submitEvent)
                ->getResponse();

            if (!$response) {
                $this->addFlash('success', \sprintf($message, $config->getName()));
                $response = $this->redirectToRoute('integrated_content_brand_edit', ['id' => $brand->getId()]);
            }
            $response = $this->dispatcher
                ->dispatch(new FilterResponseConfigEvent($config, $request, $response), $respondEvent)
                ->getResponse();

            $this->configs->persist($config);

            $request->getSession()->set(
                'postReturnUri',
                $this->generateUrl('integrated_content_brand_manage_connector', [
                    'id' => $brand->getId(),
                    'link' => $link->getId(),
                ])
            );

            return $response;
        }

        return $this->render('@IntegratedBrand/brand/config_manage.html.twig', [
            'link' => $link,
            'brand' => $brand,
            'new' => $new,
            'adapter' => $adapter,
            'config' => $config,
            'form' => $form->createView(),
        ]);
    }
}
