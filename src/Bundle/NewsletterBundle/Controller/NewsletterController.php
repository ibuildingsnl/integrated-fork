<?php

namespace Integrated\Bundle\NewsletterBundle\Controller;

use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Integrated\Bundle\NewsletterBundle\Service\Renderer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NewsletterController extends AbstractController
{
    public function __construct(
        private readonly Renderer $renderer,
        private readonly ObjectRepository $repository,
    ) {
    }

    public function preview(Request $request): Response
    {
        $newsletter = $this->repository->find($request->get('id'));
        if (!$newsletter instanceof Newsletter) {
            $this->createNotFoundException();
        }
        return new Response($this->renderer->render($newsletter));
    }
}
