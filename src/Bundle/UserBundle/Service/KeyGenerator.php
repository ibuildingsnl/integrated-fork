<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\UserBundle\Service;

use Integrated\Bundle\UserBundle\Doctrine\UserManager;
use Integrated\Bundle\UserBundle\Model\UserInterface;

class KeyGenerator
{
    /**
     * @var UserManager
     */
    private $userManager;
    private string $secret;

    public function __construct(UserManager $userManager, string $secret)
    {
        $this->userManager = $userManager;
        $this->secret = $secret;
    }

    public function generateKey(int $timestamp, UserInterface $user): string
    {
        return $this->buildSignature($timestamp, $user);
    }

    public function isValidKey(int $id, int $timestamp, string $key): bool
    {
        if ($timestamp > time() || $timestamp < (time() - 24 * 3600)) {
            return false;
        }

        if (!$user = $this->userManager->find($id)) {
            return false;
        }

        return hash_equals($this->buildSignature($timestamp, $user), $key);
    }

    private function buildSignature(int $timestamp, UserInterface $user): string
    {
        return hash_hmac(
            'sha256',
            $timestamp.'|'.$user->getPassword().'|'.$user->getId(),
            $this->secret
        );
    }
}
