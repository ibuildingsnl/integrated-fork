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

use Integrated\Bundle\UserBundle\Doctrine\RoleManager;
use Integrated\Bundle\UserBundle\Doctrine\ScopeManager;
use Integrated\Bundle\UserBundle\Model\Scope;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\LegacyPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'user:create',
    description: 'Create a user',
)]
class CreateUserCommand extends Command
{
    private UserManagerInterface $userManager;
    private ScopeManager $scopeManager;
    private RoleManager $roleManager;
    private ValidatorInterface $validator;
    private PasswordHasherFactoryInterface $hasherFactory;

    public function __construct(
        UserManagerInterface $userManager,
        ScopeManager $scopeManager,
        RoleManager $roleManager,
        ValidatorInterface $validator,
        PasswordHasherFactoryInterface $hasherFactory,
    ) {
        $this->scopeManager = $scopeManager;
        $this->roleManager = $roleManager;
        $this->userManager = $userManager;
        $this->validator = $validator;
        $this->hasherFactory = $hasherFactory;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'The username')
            ->addArgument('password', InputArgument::REQUIRED, 'The password')
            ->addArgument('scope', InputArgument::OPTIONAL, 'The scope')
            ->addArgument('roles', InputArgument::OPTIONAL, 'Roles')
            ->addArgument('email', InputArgument::OPTIONAL, 'The email address')
            ->setHelp('
The <info>%command.name%</info> command creates a new user

<info>php %command.full_name%</info>
');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');

        $email = null;

        if ($input->hasArgument('email')) {
            $email = $input->getArgument('email');
        }

        $roles = null;
        if ($input->hasArgument('roles')) {
            $roles = array_filter(explode(',', $input->getArgument('roles')));
        }

        $user = $this->userManager->create();

        $hasher = $this->hasherFactory->getPasswordHasher($user);

        $user->setUsername($username);
        $user->setEmail($email);

        if (!$hasher instanceof LegacyPasswordHasherInterface) {
            $user->setPassword($hasher->hash($password));
        } else {
            $salt = base64_encode(random_bytes(72));

            $user->setPassword($hasher->hash($password, $salt));
            $user->setSalt($salt);
        }

        $scopeName = 'Integrated';
        if ($input->hasArgument('scope')) {
            $scopeName = $input->getArgument('scope') ?: $scopeName;
        }

        if (!$scope = $this->scopeManager->findByName($scopeName)) {
            $scope = new Scope();
            $scope
                ->setName($scopeName)
                ->setAdmin($scopeName == 'Integrated')
            ;

            $this->scopeManager->persist($scope, true);
        }

        $user->setScope($scope);

        $errors = $this->validator->validate($user);

        if (\count($errors) > 0) {
            $output->writeln('Aborting: user model not valid:');

            foreach ($errors as $error) {
                $output->writeln($error->getMessage());
            }

            return self::FAILURE;
        }

        if ($roles) {
            $roleRepository = $this->roleManager->getRepository();
            $allRoles = $this->roleManager->getRolesFromSources();

            foreach ($roles as $role) {
                if ($objectRole = $roleRepository->findOneBy(['role' => $role])) {
                    $user->addRole($objectRole);
                } elseif (isset($allRoles[$role])) {
                    $objectRole = $this->roleManager->create($role);
                    $this->roleManager->persist($objectRole);
                    $user->addRole($objectRole);
                } else {
                    $output->writeln(sprintf('The role %s not found ', $role));
                }
            }
        }

        try {
            $this->userManager->persist($user);
        } catch (\Exception $e) {
            $output->writeln(sprintf('Aborting: %s', $e->getMessage()));

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
