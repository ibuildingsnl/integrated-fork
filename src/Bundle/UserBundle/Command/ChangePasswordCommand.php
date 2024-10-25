<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\UserBundle\Command;

use Integrated\Bundle\UserBundle\Doctrine\ScopeManager;
use Integrated\Bundle\UserBundle\Doctrine\UserManager;
use Integrated\Bundle\UserBundle\Model\ScopeInterface;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\LegacyPasswordHasherInterface;

#[AsCommand(
    name: 'user:password:change',
    description: 'Change password of a user',
)]
class ChangePasswordCommand extends Command
{
    private UserManager $userManager;
    private ScopeManager $scopeManager;
    private PasswordHasherFactoryInterface $hasherFactory;

    public function __construct(
        UserManager $userManager,
        ScopeManager $scopeManager,
        PasswordHasherFactoryInterface $hasherFactory,
    ) {
        $this->userManager = $userManager;
        $this->scopeManager = $scopeManager;
        $this->hasherFactory = $hasherFactory;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'The username')
            ->addArgument('password', InputArgument::REQUIRED, 'The password')
            ->addArgument('scope', InputArgument::OPTIONAL, 'The scope')
            ->setHelp('
The <info>%command.name%</info> command replaces the password of the user

<info>php %command.full_name%</info>
');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('username'); // @todo validate input
        $password = $input->getArgument('password'); // @todo validate input

        $scopeName = 'Integrated';
        if ($input->hasArgument('scope')) {
            $scopeName = $input->getArgument('scope') ?: $scopeName;
        }

        if (!$scope = $this->scopeManager->findByName($scopeName)) {
            $output->writeln(sprintf('Aborting: scope with name "%s" does not exist', $scopeName));

            return self::FAILURE;
        }

        $user = $this->findUserByScope($username, $scope);

        if (!$user) {
            $output->writeln(sprintf('Aborting: user with username "%s" does not exist', $username));

            return self::FAILURE;
        }

        $hasher = $this->hasherFactory->getPasswordHasher($user);

        if (!$hasher instanceof LegacyPasswordHasherInterface) {
            $user->setPassword($hasher->hash($password));
            $user->setSalt(null);
        } else {
            $salt = base64_encode(random_bytes(72));

            $user->setPassword($hasher->hash($password, $salt));
            $user->setSalt($salt);
        }

        try {
            $this->userManager->persist($user);
        } catch (\Exception $e) {
            $output->writeln(sprintf('Aborting: %s', $e->getMessage()));

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @throws \Exception
     */
    protected function findUserByScope(string $username, ScopeInterface $scope): ?UserInterface
    {
        return $this->userManager->createQueryBuilder()
            ->select('User')
            ->leftJoin('User.scope', 'Scope')
            ->where('User.username = :username')
            ->andWhere('User.scope = :scope')
            ->setParameters([
                'username' => $username,
                'scope' => (int) $scope->getId(),
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }
}
