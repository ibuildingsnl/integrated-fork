<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\IntegratedBundle\Controller;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Integrated\Bundle\UserBundle\Model\User;
use Knp\Component\Pager\PaginatorInterface;
use Solarium\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseAbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

class AbstractController extends BaseAbstractController
{
    public function getDoctrine(): ManagerRegistry
    {
        if (!$this->container->has('doctrine')) {
            throw new \LogicException('The DoctrineBundle is not registered in your application. Try running "composer require symfony/orm-pack".');
        }

        return $this->container->get('doctrine');
    }

    public function getDoctrineODM(): ManagerRegistry
    {
        return $this->container->get('doctrine_mongodb');
    }

    public function getPaginator(): PaginatorInterface
    {
        return $this->container->get('knp_paginator');
    }

    public function getSolarium(): Client
    {
        return $this->container->get('solarium.client');
    }

    public function getTranslator(): TranslatorInterface
    {
        return $this->container->get('translator');
    }

    protected function getUser(): ?User
    {
        $user = parent::getUser();
        if ($user && !$user instanceof User) {
            throw new \RuntimeException('Invalid user type');
        }

        return $user;
    }

    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), [
            'doctrine' => ManagerRegistry::class,
            'doctrine_mongodb' => ManagerRegistry::class,
            'knp_paginator' => PaginatorInterface::class,
            'solarium.client' => Client::class,
            'translator' => TranslatorInterface::class,
        ]);
    }
}
