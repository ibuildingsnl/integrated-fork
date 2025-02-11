<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WorkflowBundle\Entity\Definition;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\PersistentCollection;
use Integrated\Bundle\WorkflowBundle\Entity\Definition;
use Integrated\Bundle\WorkflowBundle\Utils\StateVisibleConfig;
use Ramsey\Uuid\Uuid;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class State
{
    protected string $id = '';

    protected string $name = '';

    protected ?Definition $workflow = null;

    private ?string $color = null;

    private ?string $icon = null;

    protected int $order = 0;

    protected bool $publishable = false;

    /**
     * @var Collection<Permission>
     */
    protected Collection $permissions;

    /**
     * @var Collection<State>
     */
    protected Collection $transitions;

    protected int $comment = StateVisibleConfig::DISABLED;

    protected int $assignee = StateVisibleConfig::DISABLED;

    protected int $deadline = StateVisibleConfig::DISABLED;

    public function __construct()
    {
        $this->id = Uuid::uuid4()->toString();

        $this->permissions = new ArrayCollection();
        $this->transitions = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setName(?string $name): self
    {
        $this->name = (string) $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = (string) $color;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = (string) $icon;

        return $this;
    }

    public function setWorkflow(?Definition $workflow = null): self
    {
        if ($this->workflow !== $workflow && $this->workflow !== null) {
            $this->workflow->removeState($this);
        }

        $this->workflow = $workflow;

        if ($this->workflow) {
            $this->workflow->addState($this);
        }

        return $this;
    }

    /**
     * @return Definition|null
     */
    public function getWorkflow()
    {
        return $this->workflow;
    }

    public function setOrder(?int $order): self
    {
        $this->order = (int) $order;

        return $this;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function setPublishable(bool $publishable): self
    {
        $this->publishable = (bool) $publishable;

        return $this;
    }

    public function isPublishable(): bool
    {
        return $this->publishable;
    }

    /**
     * @param Permission[] $permissions
     */
    public function setPermissions(iterable $permissions): self
    {
        foreach ($this->permissions as $permission) {
            $this->removePermission($permission);
        }

        foreach ($permissions as $permission) {
            $this->addPermission($permission);
        }

        return $this;
    }

    /**
     * @return Permission[]
     */
    public function getPermissions(): array
    {
        return $this->permissions->toArray();
    }

    public function addPermission(Permission $permission): self
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions->add($permission);

            // first add the permission to the state then set the state else
            // there would be a infinite loop

            $permission->setState($this);
        }

        return $this;
    }

    public function removePermission(Permission $permission): self
    {
        if ($this->permissions->removeElement($permission)) {
            $permission->setState(null);
        }

        return $this;
    }

    public function setTransitions(iterable $transitions): self
    {
        $this->transitions->clear();
        $this->transitions = new ArrayCollection();

        foreach ($transitions as $transition) {
            $this->addTransition($transition); // type check
        }

        return $this;
    }

    /**
     * @return State[]
     */
    public function getTransitions(): array
    {
        return $this->transitions->toArray();
    }

    public function addTransition(self $state): self
    {
        if (!$this->transitions->contains($state)) {
            $this->transitions->add($state);
        }

        return $this;
    }

    public function removeTransition(self $state): self
    {
        $this->transitions->removeElement($state);

        return $this;
    }

    public function isDefault(): bool
    {
        if (isset($this->workflow)) {
            return $this === $this->workflow->getDefault();
        }

        return false;
    }

    public function getComment(): int
    {
        return $this->comment;
    }

    public function setComment(int $comment)
    {
        $this->comment = $comment;
    }

    public function getAssignee(): int
    {
        return $this->assignee;
    }

    public function setAssignee(int $assignee)
    {
        $this->assignee = $assignee;
    }

    public function getDeadline(): int
    {
        return $this->deadline;
    }

    public function setDeadline(int $deadline)
    {
        $this->deadline = $deadline;
    }

    /**
     * Fix issues with primary key constraints errors because deletes are execute
     * after updates and inserts.
     */
    public function doPermissionFix(PreFlushEventArgs $event)
    {
        // if not a PersistentCollection then its probably is a new entity else check if
        // data from the database is loaded or not.

        if (!$this->permissions instanceof PersistentCollection || !$this->permissions->isInitialized()) {
            return;
        }

        $em = $event->getObjectManager();
        $uow = $em->getUnitOfWork();

        /** @var Permission $permission */
        /* @var Permission $found */

        foreach ($this->permissions as $permission) {
            // see if there is already a entity in de identity map with this primary key. If so
            // then use that one and removed the one in the collection from the identity map. But
            // only when the state is null or a entity matching $this else the permission is
            // moved to an other state. (could give a problem if inserts are done before updates)
            //
            // NOTE: This also means that all the changes to the entity that is removed from
            // the collection wont be recorded by doctrine anymore.

            if ($found = $uow->tryGetById([$permission->getGroup(), $this->getId()], $permission::class)) {
                if ($found !== $permission && ($found->getState() === null || $found->getState() === $this)) {
                    $this->permissions->removeElement($permission);
                    $this->permissions->add($found);

                    if ($uow->isInIdentityMap($permission)) {
                        $uow->detach($permission);
                    }

                    $found->setState($this);
                    $found->setMask($permission->getMask());

                    $uow->persist($found);
                }
            }
        }
    }
}
