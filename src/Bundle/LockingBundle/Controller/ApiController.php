<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\LockingBundle\Controller;

use Integrated\Common\Locks\ManagerInterface;
use Integrated\Common\Locks\Resource;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class ApiController extends AbstractController
{
    private ?ManagerInterface $manager;

    public function __construct(?ManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function refresh(Request $request): Response
    {
        $manager = $this->manager;
        if (!$manager instanceof ManagerInterface) {
            return $this->respond(403, 'Locking is not enabled');
        }

        if (!$owner = $this->getOwner()) {
            return $this->respond(401, 'Valid user is required');
        }

        if (!$lockId = $this->getLockId($request)) {
            return $this->respond(400, 'Missing lock identifier');
        }

        $lock = $manager->find($lockId);
        if ($lock === null) {
            return $this->respond(404, 'The lock could not be found');
        }

        $lockOwner = $lock->getRequest()->getOwner();
        if (!$lockOwner || !$owner->equals($lockOwner)) {
            return $this->respond(423, 'The lock belongs to another user', ['lock' => null]);
        }

        $lock = $manager->refresh($lock);
        if ($lock === null) {
            return $this->respond(500, 'The lock could not be extended', ['lock' => null]);
        }

        return $this->respond(200, 'The lock is extended', ['lock' => $lock->getId()]);
    }

    public function release(Request $request): Response
    {
        $manager = $this->manager;
        if (!$manager instanceof ManagerInterface) {
            return $this->respond(403, 'Locking is not enabled');
        }

        if (!$owner = $this->getOwner()) {
            return $this->respond(401, 'Valid user is required');
        }

        if (!$lockId = $this->getLockId($request)) {
            return $this->respond(400, 'Missing lock identifier');
        }

        $lock = $manager->find($lockId);
        if ($lock === null) {
            return $this->respond(404, 'The lock could not be found');
        }

        $lockOwner = $lock->getRequest()->getOwner();
        if (!$lockOwner || !$owner->equals($lockOwner)) {
            return $this->respond(423, 'The lock belongs to another user', ['lock' => null]);
        }

        $manager->release($lock);

        return $this->respond(200, 'The lock is released', ['lock' => null]);
    }

    private function getLockId(Request $request): ?string
    {
        $lock = $request->request->get('lock', $request->query->get('lock'));
        if (!\is_string($lock) || '' === trim($lock)) {
            return null;
        }

        return $lock;
    }

    private function getOwner(): ?Resource
    {
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            return null;
        }

        return Resource::fromAccount($user);
    }

    /**
     * @param array<string,mixed> $extra
     */
    private function respond(int $code, string $message, array $extra = []): JsonResponse
    {
        return new JsonResponse(array_merge([
            'code' => $code,
            'message' => $message,
        ], $extra), $code);
    }
}
