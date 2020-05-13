<?php

namespace Integrated\Bundle\WebsiteBundle\Security;

use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\UserBundle\Model\User;
use Doctrine\ORM\EntityManagerInterface;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserManager
{
    const STATUS_USERNAME_NEW = 1;
    const STATUS_USERNAME_EXISTS = 2;
    const STATUS_USERNAME_INVALID = 3;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var ChannelContextInterface
     */
    private $channelContext;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * Authenticator constructor.
     *
     * @param EntityManagerInterface       $entityManager
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ChannelContextInterface      $channelContext
     * @param ValidatorInterface           $validator
     */
    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, ChannelContextInterface $channelContext, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->channelContext = $channelContext;
        $this->validator = $validator;
    }

    /**
     * @return bool
     */
    public function isLoginEnabled()
    {
        if (!$channel = $this->channelContext->getChannel()) {
            return false;
        }

        return $channel instanceof Channel && $channel->getScope() !== null;
    }

    /**
     * @return bool
     */
    public function isRegistrationEnabled()
    {
        if (!$channel = $this->channelContext->getChannel()) {
            return false;
        }

        return $channel instanceof Channel && $channel->getRegistrationAllowed();
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function getUsernameStatus(?string $username)
    {
        if (!$this->isLoginEnabled()) {
            return $this::STATUS_USERNAME_INVALID;
        }

        $emailConstraint = new Email();
        if (\count($this->validator->validate($username, $emailConstraint)) > 0) {
            return $this::STATUS_USERNAME_INVALID;
        }

        $scope = $this->channelContext->getChannel()->getScope();

        $user = $this->entityManager->getRepository(User::class)->findOneBy(
            ['username' => $username, 'scope' => $scope->getId()]
        );

        if ($user === null) {
            return $this::STATUS_USERNAME_NEW;
        }

        return $this::STATUS_USERNAME_EXISTS;
    }

    /**
     * @return void
     */
    public function register(string $username, string $password)
    {
        if (!$this->isRegistrationEnabled()
            || $this->getUsernameStatus($username) !== $this::STATUS_USERNAME_NEW
            || !($channel = $this->channelContext->getChannel())
            || $password == ''
        ) {
            throw new \Exception('User registration not allowed');
        }

        $salt = base64_encode(random_bytes(72));

        $user = new User();
        $user->setUsername($username);
        $user->setSalt($salt);
        $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
        $user->setScope($channel->getScope());

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
