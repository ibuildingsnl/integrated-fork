<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WorkflowBundle\Doctrine\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Integrated\Bundle\WorkflowBundle\Entity\Definition;
use Integrated\Bundle\WorkflowBundle\Entity\Definition\State;
use Integrated\Common\Queue\QueueAwareInterface;
use Integrated\Common\Queue\QueueInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class QueueListener implements EventSubscriber, QueueAwareInterface
{
    /**
     * @var QueueInterface
     */
    private $queue;

    /**
     * @var array
     */
    private $identities = [];

    public function __construct(QueueInterface $queue)
    {
        $this->setQueue($queue);
    }

    public function setQueue(QueueInterface $queue)
    {
        $this->queue = $queue;
    }

    /**
     * @return QueueInterface
     */
    public function getQueue()
    {
        return $this->queue;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postUpdate,
        ];
    }

    public function postPersist(LifecycleEventArgs $event)
    {
        $this->process($event);
    }

    public function postUpdate(LifecycleEventArgs $event)
    {
        $this->process($event);
    }

    /**
     * Queue a workflow index.
     */
    protected function process(LifecycleEventArgs $event)
    {
        if ($id = $this->getId($event->getObject())) {
            if (\array_key_exists($id, $this->identities)) {
                return; // only add a workflow index ones for the selected id.
            }

            $this->getQueue()->push([
                'command' => 'index',
                'args' => [$id],
            ]);

            $this->identities[$id] = true;
        }
    }

    /**
     * Try to extract a Definition id from the $data.
     */
    protected function getId($data)
    {
        if ($data instanceof State) {
            $data = $data->getWorkflow();
        }

        if ($data instanceof Definition) {
            return $data->getId();
        }

        return null;
    }
}
