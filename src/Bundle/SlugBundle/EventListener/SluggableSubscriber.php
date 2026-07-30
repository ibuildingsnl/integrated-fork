<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\SlugBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Doctrine\ODM\MongoDB\UnitOfWork as ODMUnitOfWork;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\UnitOfWork as ORMUnitOfWork;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\ManagerEventArgs;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\SlugBundle\Mapping\MetadataFactoryInterface;
use Integrated\Bundle\SlugBundle\Slugger\SluggerInterface;
use MongoDB\BSON\Regex;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Doctrine ORM and ODM subscriber for slug generation.
 *
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class SluggableSubscriber implements EventSubscriber
{
    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var SluggerInterface
     */
    private $slugger;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    public function __construct(MetadataFactoryInterface $metadataFactory, SluggerInterface $slugger)
    {
        $this->metadataFactory = $metadataFactory;
        $this->slugger = $slugger;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'prePersist',
            'onFlush',
            'preUpdate',
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        // used for slug as id
        $this->handleEvent($args, 'prePersist');
    }

    /**
     * Generates the slugs that are derived from the identifier.
     *
     * These cannot be generated in prePersist, because the identifier is only
     * assigned after that event has been dispatched. They used to be generated
     * in postPersist, but by then the insert has already been written, so the
     * recomputed change set was silently dropped and the slug never reached
     * the database. onFlush is the last point at which a change set can still
     * be altered, and the identifier is already available there.
     */
    public function onFlush(ManagerEventArgs $args)
    {
        $om = $args->getObjectManager();
        $uow = $om->getUnitOfWork();

        if ($uow instanceof ODMUnitOfWork) {
            $objects = array_merge($uow->getScheduledDocumentInsertions(), $uow->getScheduledDocumentUpserts());
        } elseif ($uow instanceof ORMUnitOfWork) {
            $objects = $uow->getScheduledEntityInsertions();
        } else {
            return;
        }

        foreach ($objects as $object) {
            // used for id in slug
            $this->handleObject($om, $object, 'postPersist');
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->handleEvent($args, 'preUpdate');
    }

    /**
     * @param string $event
     */
    protected function handleEvent(LifecycleEventArgs $args, $event)
    {
        $this->handleObject($args->getObjectManager(), $args->getObject(), $event);
    }

    /**
     * @param object $object
     * @param string $event
     */
    protected function handleObject(ObjectManager $om, $object, $event)
    {
        $class = \get_class($object);

        if (!$om instanceof DocumentManager && !$om instanceof EntityManagerInterface) {
            return;
        }

        $classMetadata = $this->metadataFactory->getMetadata($class);
        $classMetadataInfo = $om->getClassMetadata($class);

        $identifierFields = $classMetadataInfo->getIdentifierFieldNames();

        foreach ($classMetadata->getProperties() as $propertyMetadata) {
            if (\count($propertyMetadata->getFields())) {
                $hasIdentifierFields = \count(array_intersect($identifierFields, $propertyMetadata->getFields())) > 0;

                if ($event == 'prePersist' &&
                    $hasIdentifierFields ||
                    $event == 'postPersist' &&
                    !$hasIdentifierFields
                ) {
                    continue; // generate slug in another event
                }

                $slug = null;

                if ($event == 'preUpdate') {
                    $uow = $om->getUnitOfWork();
                    if ($om instanceof DocumentManager) {
                        $changeset = $uow->getDocumentChangeSet($object);
                    } else {
                        $changeset = $uow->getEntityChangeSet($object);
                    }
                    if (\array_key_exists($propertyMetadata->getName(), $changeset)) {
                        // generate custom slug
                        $slug = $this->slugger->slugify(
                            $changeset[$propertyMetadata->getName()][1],
                            $propertyMetadata->getSeparator()
                        );
                    } elseif (null !== $propertyMetadata->getValue($object)) {
                        continue; // no changes
                    }
                } else {
                    // generate custom slug
                    $slug = $this->slugger->slugify(
                        $propertyMetadata->getValue($object),
                        $propertyMetadata->getSeparator()
                    );
                }

                if (!trim($slug)) {
                    // generate slug from the sluggable fields
                    $slug = $this->generateSlugFromMetadata(
                        $object,
                        $propertyMetadata->getFields(),
                        $propertyMetadata->getSeparator()
                    );
                }

                if ($propertyMetadata->getLengthLimit()) {
                    $slug = substr($slug, 0, $propertyMetadata->getLengthLimit());
                }

                $id = $event == 'preUpdate' && method_exists($object, 'getId') ? $object->getId() : null;

                // generate unique slug
                $slug = $this->generateUniqueSlug(
                    $om,
                    $object,
                    $propertyMetadata->getName(),
                    $slug,
                    $propertyMetadata->getSeparator(),
                    $id,
                    $propertyMetadata->getFields()
                );

                $propertyMetadata->setValue($object, $slug);
                $this->recomputeSingleObjectChangeSet($om, $object);
            }
        }
    }

    /**
     * @param object $object
     * @param string $separator
     *
     * @return string
     */
    protected function generateSlugFromMetadata($object, array $fields, $separator = '-')
    {
        $values = [];

        foreach ($fields as $field) {
            $values[] = $this->propertyAccessor->getValue($object, $field);
        }

        // generate slug value
        return $this->slugger->slugify(implode(' ', $values), $separator);
    }

    /**
     * @param object $object
     * @param mixed  $value
     * @param array  $fields
     *
     * @return bool
     */
    protected function checkIfFieldValue($object, $value, $fields)
    {
        foreach ($fields as $field) {
            if ($value == $this->propertyAccessor->getValue($object, $field)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ObjectManager|DocumentManager|EntityManager $om
     * @param object                                      $object
     * @param string                                      $field
     * @param string                                      $slug
     * @param string                                      $separator
     * @param string                                      $id
     * @param array                                       $slugFields
     *
     * @return string
     */
    protected function generateUniqueSlug(ObjectManager $om, $object, $field, $slug, $separator = '-', $id = null, $slugFields = [])
    {
        if (!trim($slug)) {
            return null;
        }

        $class = \get_class($object);

        if ($this->isUniqueSlug($om, $class, $field, $slug, $id)) {
            return $slug;
        }

        // slug with counter pattern
        $pattern = '/(.+)'.preg_quote($separator, '/').'(\d+)$/i';

        if (preg_match($pattern, $slug, $match)) {
            // Check if integer at the end of the slug matches any slug fields, if not, remove the int
            if (!$this->checkIfFieldValue($object, $match[2], $slugFields)) {
                // remove counter from slug
                $slug = $match[1];
            }
        }

        $objects = $this->findSimilarSlugs($om, $class, $field, $slug, $separator);

        if (\count($objects)) {
            $oid = spl_object_hash($object);
            $slugs = [];

            foreach ($objects as $object2) {
                if (property_exists($object2, $field) && $oid !== spl_object_hash($object2)) {
                    $value = $this->propertyAccessor->getValue($object2, $field);
                    $slugs[] = $value;
                }
            }

            if (!empty($slugs)) {
                for ($i = 1; $i <= (max(array_keys($slugs)) + 2); ++$i) {
                    $slug2 = $slug.($i > 1 ? $separator.$i : '');

                    if (!\in_array($slug2, $slugs)) {
                        return $slug2;
                    }
                }
            }
        }

        return $slug;
    }

    /**
     * @param ObjectManager|DocumentManager|EntityManager $om
     * @param string                                      $class
     * @param string                                      $field
     * @param string                                      $slug
     * @param string                                      $id
     *
     * @return bool
     */
    protected function isUniqueSlug(ObjectManager $om, $class, $field, $slug, $id = null)
    {
        // check in document manager
        foreach ($this->getScheduledObjects($om) as $object) {
            if (property_exists($object, $field) && $slug === $this->propertyAccessor->getValue($object, $field)) {
                if (!(null !== $id && method_exists($object, 'getId') && $id == $object->getId())) {
                    return false;
                }
            }
        }

        $uow = $om->getUnitOfWork();

        // check in database
        if ($uow instanceof ODMUnitOfWork) {
            $builder = $this->getRepository($om, $class)->createQueryBuilder();
            $builder->field($field)->equals($slug);

            if (null !== $id) {
                // exclude current document
                $builder->field('id')->notEqual($id);
            }

            $query = $builder->count()->getQuery();

            return $query->execute() === 0;
        }

        throw new \RuntimeException('Not implemented yet'); // @todo (INTEGRATED-294)
    }

    /**
     * @param ObjectManager|DocumentManager|EntityManager $om
     * @param string                                      $class
     * @param string                                      $field
     * @param string                                      $slug
     * @param string                                      $separator
     *
     * @return array
     */
    protected function findSimilarSlugs(ObjectManager $om, $class, $field, $slug, $separator = '-')
    {
        $objects = $this->getScheduledObjects($om);
        $uow = $om->getUnitOfWork();

        if ($uow instanceof ODMUnitOfWork) {
            return array_merge($objects, $this->getRepository($om, $class)->findBy([
                $field => new Regex(
                    '^'.preg_quote($slug, '/').'('.preg_quote($separator, '/').'\d+)?$'
                ), // counter is optional
            ]));
        }

        throw new \RuntimeException('Not implemented yet'); // @todo (INTEGRATED-294)
    }

    /**
     * @param ObjectManager|EntityManagerInterface|DocumentManager $om
     *
     * @return array
     */
    protected function getScheduledObjects(ObjectManager $om)
    {
        $uow = $om->getUnitOfWork();

        if ($uow instanceof ODMUnitOfWork) {
            return array_merge($uow->getScheduledDocumentInsertions(), $uow->getScheduledDocumentUpdates());
        } elseif ($uow instanceof ORMUnitOfWork) {
            return array_merge($uow->getScheduledEntityInsertions(), $uow->getScheduledEntityUpdates());
        }

        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * @param ObjectManager|DocumentManager|EntityManager $om
     * @param string                                      $class
     *
     * @return ObjectRepository|DocumentRepository|EntityRepository
     */
    protected function getRepository(ObjectManager $om, $class)
    {
        $uow = $om->getUnitOfWork();

        if ($uow instanceof ODMUnitOfWork) {
            $classMetadata = $om->getClassMetadata($class);
            $reflection = $classMetadata->getReflectionClass();

            $parents = [];

            // get parent class
            while ($parent = $reflection->getParentClass()) {
                $parents[] = $parent->getName();
                $reflection = $parent;
            }

            if (\count($parents)) {
                $class = end($parents);
            }

            return $om->getRepository($class);
        } elseif ($uow instanceof ORMUnitOfWork) {
            throw new \RuntimeException('Not implemented yet'); // @todo (INTEGRATED-294)
        }
        throw new \RuntimeException('Not supported');
    }

    /**
     * @param ObjectManager|DocumentManager|EntityManager $om
     * @param object                                      $object
     */
    protected function recomputeSingleObjectChangeSet(ObjectManager $om, $object)
    {
        if ($om->contains($object)) {
            $classMetadata = $om->getClassMetadata(\get_class($object));
            $uow = $om->getUnitOfWork();

            if ($uow instanceof ODMUnitOfWork) {
                $uow->recomputeSingleDocumentChangeSet($classMetadata, $object);
            } elseif ($uow instanceof ORMUnitOfWork) {
                $uow->recomputeSingleEntityChangeSet($classMetadata, $object);
            }
        }
    }
}
