<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation;
use Integrated\Bundle\ContentBundle\Form\EventListener\ChannelDefaultDataListener;
use Integrated\Bundle\ContentBundle\Form\EventListener\ChannelEnforcerListener;
use Integrated\Bundle\ContentBundle\Form\EventListener\ChannelPermissionListener;
use Integrated\Bundle\ContentBundle\Form\Type\PrimaryChannelType;
use Integrated\Common\Content\Form\Event\BuilderEvent;
use Integrated\Common\Content\Form\Event\ValidationEvent;
use Integrated\Common\Content\Form\Events;
use Integrated\Common\Security\PermissionInterface;
use Integrated\Common\Services\MainFlusher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ContentFeaturedImageListener implements EventSubscriberInterface
{
    private $documentManager;
    private $flusher;

    public function __construct(DocumentManager $documentManager, MainFlusher $flusher)
    {
        $this->documentManager = $documentManager;
        $this->flusher = $flusher;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::POST_VALIDATE => ['buildForm', -60],
        ];
    }

    public function buildForm(ValidationEvent $event): void
    {
        //check which content needs this besides article
        if ($event->getContent()->getContentType() == 'article') {
            $content = $this->documentManager->getRepository(Content::class)->find($event->getContent()->getId());

            if ($content->getFeaturedImage() != null) {
                $image = $this->documentManager->getRepository(Content::class)->find($content->getFeaturedImage()->getId());

                $content->addRelation((new Relation())
                    ->setRelationId('__featured_image')
                    ->setRelationType('embedded')
                    ->addReference($image));

                $this->flusher->flush();
            }
        }
    }
}
