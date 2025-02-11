<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WorkflowBundle\Form\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use Integrated\Bundle\WorkflowBundle\Entity\Definition\State;
use Integrated\Bundle\WorkflowBundle\Form\Model;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class ExtractTransitionsFromCollectionListener implements EventSubscriberInterface
{
    /** @var array<State> */
    private array $states;

    /**
     * @var PropertyAccessorInterface
     */
    private $accessor;

    /**
     * @var Model\State[]
     */
    private array $choices;

    /**
     * Creates a new transition from collection extractor listener.
     *
     * @param $states array<State>
     */
    public function __construct(?array $states)
    {
        if (!$states) {
            $states = [];
        }

        $this->states = $states;
        $this->choices = $this->getChoices($states);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPrepare',
            FormEvents::PRE_SUBMIT => 'onPrepare',

            FormEvents::POST_SET_DATA => 'onSetData',
            FormEvents::POST_SUBMIT => 'onGetData',
        ];
    }

    /**
     * Add the transitions field to children of the collection.
     */
    public function onPrepare(FormEvent $event)
    {
        $form = $event->getForm();

        if ($form->has('transactions')) {
            $form->remove('transactions');
        }

        $form->add('transitions', ChoiceType::class, [
            'required' => false,

            // The transitions will be "manually" mapped because potential new States that are
            // created in the SUBMIT events will not be available, as a State object, until the
            // POST_SUBMIT event. So it is not possible to create a complete and correct list
            // of States in the execution of the PRE_* events to feed to a view transformer.
            'label' => 'Transitions to',
            'mapped' => false,

            'choices' => $this->getChoicesFiltered($this->choices, $form->getName()),
            'choice_label' => 'label',
            'choice_value' => 'value',

            'multiple' => true,
            'expanded' => false,
        ]);
    }

    /**
     * Set the view data based on the current state transitions.
     *
     * This will convert the transitions states to a list of numbers that represent the
     * index of the state in the collection. This will be done for all the children in
     * the collection.
     */
    public function onSetData(FormEvent $event)
    {
        $form = $event->getForm();

        $data = $form->getData();
        if (!$data instanceof State) {
            return;
        }

        if (!$form->has('transitions')) {
            return;
        }

        // the index got the child keys where every State resides in the collection. So now we
        // convert the States in the transitions to the child index with in the collection. That
        // way we can also keep track of new States since those don't have a id yet.

        $selection = [];

        foreach ($data->getTransitions() as $data) {
            $hash = spl_object_hash($data);

            if (isset($this->choices[$hash])) {
                $selection[] = $this->choices[$hash];
            }
        }

        $form->get('transitions')->setData($selection);
    }

    /**
     * Set the state transitions in based on the view data.
     *
     * This will convert the view data to a set of states to set as the transitions. This
     * will be done for all the children in the collection.
     */
    public function onGetData(FormEvent $event)
    {
        // Build a index with all the State data. This could also be done in one foreach
        // loop but to slim down on the method calls and instanceof check it is done ones
        // before converting the view data.

        $index = [];

        foreach ($this->states as $state) {
            $index[$state->getName()] = $state;
        }

        $form = $event->getForm();
        $data = $form->getData();

        if (!$data instanceof State) {
            return;
        }

        if (!$form->has('transitions')) {
            return;
        }

        $data->setTransitions(new ArrayCollection()); // clear the current transitions

        // The values in the view represent the index numbers of the State in de index, which
        // correspond directly to the index of the child in the collection.

        foreach ($form->get('transitions')->getData() as $value) {
            if (!$value instanceof Model\State) {
                continue;
            }

            if (isset($index[$value->getLabel()])) {
                $data->addTransition($index[$value->getLabel()]);
            }
        }
    }

    /**
     * Get a array with choices based on the given data.
     *
     * The values of the choices are the same as the array keys from the data array and
     * the labels is the name field extracted from the data.
     *
     * @param $data array<State>
     *
     * @return Model\State[]
     */
    protected function getChoices(array $data)
    {
        $choices = [];

        foreach ($data as $index => $state) {
            $choices[spl_object_hash($state)] = new Model\State($index, trim($state->getName()));
        }

        return $choices;
    }

    /**
     * Build a choice list based on the given choice but filter out the current state.
     *
     * @param int $current
     *
     * @return Model\State[]
     */
    protected function getChoicesFiltered(array $choices, $current)
    {
        if (isset($choices[$current])) {
            unset($choices[$current]);
        }

        return $choices;
    }
}
