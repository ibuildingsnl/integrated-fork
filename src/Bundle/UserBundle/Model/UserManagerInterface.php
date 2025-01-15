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
interface UserManagerInterface extends ManagerInterface
{
    /**
     * Create a user object.
     *
     * @return UserInterface
     */
    public function create();

    /**
     * Change or add the user to the manager.
     */
    public function persist(UserInterface $user);

    /**
     * Remove the user from the manager.
     */
    public function remove(UserInterface $user);

    /**
     * Delete all the managed users.
     */
    public function clear();

    /**
     * Finds the user by its identifier.
     *
     * @return UserInterface|null
     */
    public function find($id);

    /**
     * Finds all the managed users.
     *
     * @return UserInterface[]
     */
    public function findAll();

    /**
     * Finds the user by its username.
     *
     * @param string $criteria
     *
     * @return UserInterface|null
     */
    public function findByUsername($criteria);

    /**
     * Finds the user by its email address.
     *
     * @param string $criteria
     *
     * @return UserInterface|null
     */
    public function findByEmail($criteria);

    /**
     * Finds the user by its username or email address.
     *
     * @param string $criteria
     *
     * @return UserInterface|null
     */
    public function findByUsernameOrEmail($criteria);

    /**
     * Finds the users by a set of criteria.
     *
     * @return UserInterface[]
     */
    public function findBy(array $criteria);

    /**
     * Finds an user by a set of criteria.
     *
     * @return UserInterface|null
     */
    public function findOneBy(array $criteria);

    /**
     * Returns the class name of the user object.
     *
     * @return string
     */
    public function getClassName();

    /**
     * Finds the user by its username and scope.
     *
     * @return UserInterface|null
     */
    public function findEnabledByUsernameAndScope($username, ?ScopeInterface $scope = null);
}
