<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\UserBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class ProfileType extends AbstractType
{
    /**
     * @var UserManagerInterface
     */
    private $manager;

    public function __construct(UserManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('class', $this->manager->getClassName());

        $resolver->setDefault('choice_value', 'id');
        $resolver->setDefault('choice_label', 'username');
        $resolver->setDefault('include_user_id', null);
        $resolver->setAllowedTypes('include_user_id', ['null', 'int', 'string']);
        $resolver->setDefault('query_builder', static function (Options $options) {
            return static function (EntityRepository $repository) use ($options) {
                $queryBuilder = $repository
                    ->createQueryBuilder('User')
                    ->orderBy('User.username', 'ASC');

                $includeUserId = $options['include_user_id'];

                if (null === $includeUserId || '' === trim((string) $includeUserId)) {
                    return $queryBuilder->where('User.enabled = true');
                }

                return $queryBuilder
                    ->where('User.enabled = true OR User.id = :includeUserId')
                    ->setParameter('includeUserId', (int) $includeUserId);
            };
        });
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_user_profile_choice';
    }

    /**
     * @return UserManagerInterface
     */
    public function getManager()
    {
        return $this->manager;
    }
}
