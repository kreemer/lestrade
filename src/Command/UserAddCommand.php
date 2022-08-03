<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:user-add',
    description: 'Add an user',
)]
class UserAddCommand extends Command
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher, private UserRepository $userRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = $io->ask('Please enter the username');
        $password = $io->askHidden('Please enter the password');

        $user = new User();
        $user->setUsername($username);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setToken(sha1(mt_rand(1, 90000).'SALT'));

        $this->userRepository->add($user, true);

        $io->success('User created');
        $io->note('The token is: '.$user->getToken());

        return Command::SUCCESS;
    }
}
