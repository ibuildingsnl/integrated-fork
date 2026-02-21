<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\CommentBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\CommentBundle\Document\Comment;
use Integrated\Bundle\CommentBundle\Document\Embedded\Reply;
use Integrated\Bundle\CommentBundle\Form\Type\CommentType;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\UserBundle\Model\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    private DocumentManager $manager;

    public function __construct(DocumentManager $manager)
    {
        $this->manager = $manager;
    }

    public function new(Request $request, Content $content, string $field): Response
    {
        $comment = new Comment();
        $comment->setContent($content);
        $comment->setField($field);

        $user = $this->getUser();
        if ($user instanceof User && $relation = $user->getRelation()) {
            $comment->setAuthor($relation);
        }

        $form = $this->createForm(CommentType::class, $comment, [
            'action' => $request->getUri(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($comment);
            $this->manager->flush();

            return new JsonResponse(['id' => $comment->getId()]);
        }

        return $this->render('@IntegratedComment/comment/new.html.twig', [
            'form' => $form,
        ]);
    }

    public function getComment(Request $request, Comment $comment): Response
    {
        $reply = new Reply();
        $reply->setDate(new \DateTime());

        $user = $this->getUser();
        if ($user instanceof User && $relation = $user->getRelation()) {
            $comment->setAuthor($relation);
        }

        $form = $this->createForm(CommentType::class, $reply, [
            'action' => $request->getUri(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->addReply($reply);
            $this->manager->flush();

            return new JsonResponse(['id' => $comment->getId()]);
        }

        return $this->render('@IntegratedComment/comment/get.html.twig', [
            'comment' => $comment,
            'form' => $form,
        ]);
    }

    public function delete(Comment $comment): Response
    {
        $this->manager->remove($comment);
        $this->manager->flush();

        return new JsonResponse([
            'deleted' => true,
            'id' => $comment->getId(),
        ]);
    }

    public function deleteReply(Comment $comment, $replyId): Response
    {
        $result = $comment->removeReplyById($replyId);

        $this->manager->flush();

        return new JsonResponse([
            'deleted' => $result,
            'id' => $replyId,
        ]);
    }
}
