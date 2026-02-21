<?php

namespace MartenaSoft\UserBundle\Command;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use MartenaSoft\CommonLibrary\Dictionary\DictionaryUser;
use MartenaSoft\UserBundle\Entity\Role;
use MartenaSoft\UserBundle\Repository\RoleRepository;
use MartenaSoft\UserBundle\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsCommand(
    name: 'mrs:change-user-role',
    description: 'Add a short description for your command',
)]
class MrsChangeUserRoleCommand extends Command
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ParameterBagInterface $parameterBag,
        private readonly UserRepository $userRepository,
        private readonly RoleRepository $roleRepository
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('user-email', 'ue', InputOption::VALUE_REQUIRED, 'User email');
        $this->addOption('superadmin-password', 'spw', InputOption::VALUE_REQUIRED, 'Password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $userEmail = $input->getOption('user-email');
        $password = $input->getOption('superadmin-password');

        if (empty($password)) {
            $io->error("Please enter a superadmin password. (mrs:change-user-role --user-email='changed-user@email.com' --superadmin-password='--YOUR PASSWORD--')");
            return Command::FAILURE;
        }

        if (empty($userEmail)) {
            $io->error("Please enter an user email. (mrs:change-user-role  --user-email='changed-user@email.com' --superadmin-password='--YOUR PASSWORD--')");
            return Command::FAILURE;
        }

        if (sha1($password) != $this->parameterBag->get('superadmin_password')) {
            $io->error("Password is wrong!");
            return Command::FAILURE;
        }

        $user = $this->userRepository->findOneByEmail($userEmail);

        if (empty($user)) {
            $io->error("User not found by email: $userEmail");
            return Command::FAILURE;
        }

        $roles = $this->roleRepository->getAllByIndex('name');
        $rolesArray = array_keys($roles);
        array_unshift($rolesArray,DictionaryUser::ADMIN_ROLE);
        $rolesArray = array_unique($rolesArray);

        $rolesChoices = new ChoiceQuestion('Choose role', $rolesArray);
        $rolesChoices->setMultiselect(true);
        $rolesChoices->setMultiline(true);
        $selectedRoles = $this->getHelper('question')->ask($input, $output, $rolesChoices);

        $token = new UsernamePasswordToken($user, 'cli', [DictionaryUser::ADMIN_ROLE]);
        $this->tokenStorage->setToken($token);

        foreach ($user->getRoles() as $role) {
            $user->removeRole($role);
        }

        $this->userRepository->save($user);

        foreach ($selectedRoles as $choice) {
            if (!isset($roles[$choice])) {
                $role = new Role();
                $role->setName($choice);
                $roles[$choice] = $role;
            }

            $this->roleRepository->save($roles[$choice], false);
            $user->addRole($roles[$choice]);
        }


        $this->userRepository->save($user);
        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
