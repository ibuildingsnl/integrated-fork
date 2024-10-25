<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\UserBundle\Model;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class Role implements RoleInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $role;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var bool
     */
    protected $hidden = false;

    /**
     * Create a new role.
     *
     * @param string $role
     * @param string $label
     * @param string $description
     */
    public function __construct($role, $label = null, $description = null)
    {
        $this->role = strtoupper($role);

        $this->setLabel($label);
        $this->setDescription($description);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setLabel($label)
    {
        $this->label = $label !== null ? (string) $label : null;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setDescription($description)
    {
        $this->description = $description !== null ? (string) $description : null;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param bool $hidden
     */
    public function setHidden($hidden = true)
    {
        $this->hidden = (bool) $hidden;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * Get the string representation of the role object.
     *
     * This can be use full for debugging
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            "ID: %s\nRole: %s\nHidden: %s\nLabel: %s\nDescription: %s",
            $this->getId(),
            $this->getRole(),
            $this->isHidden() ? 'TRUE' : 'FALSE',
            $this->getLabel(),
            $this->getDescription()
        );
    }
}
