<?php

namespace Integrated\Bundle\NewsletterBundle\Controller;

use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Integrated\Bundle\NewsletterBundle\Service\ContentFetcher;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NewsletterController extends AbstractController
{
    public function __construct(
        private readonly ContentFetcher $content,
        private readonly ThemeManager $themes,
        private readonly ObjectRepository $repository,
    ) {
    }

    public function preview(Request $request): Response
    {
        $newsletter = $this->repository->find($request->get('id'));
        if (!$newsletter instanceof Newsletter) {
            $this->createNotFoundException();
        }
        return $this->render(
            $this->themes->locateTemplate('content/newsletter/show.html.twig')
                ?: '@IntegratedNewsletter/fallback.html.twig',
            [
                'newsletter' => $newsletter,
                'content' => $this->content->fetchFor($newsletter),
            ]
        );
    }
}
