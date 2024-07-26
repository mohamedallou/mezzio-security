<?php

declare(strict_types=1);

namespace MezzioSecurity\Command;

use Doctrine\ORM\EntityManagerInterface;
use MezzioSecurity\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Run as follow: php bin/console.php app:generate-user --email="admin_restaurant@tonne.to" --password="ssssss"
 * --firstName="admin1" --permissions="admin"
 */
#[AsCommand(name: 'app:generate-user')]
class GenerateUser extends Command
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this->addOption('email', null, InputOption::VALUE_REQUIRED);
        $this->addOption('password', null, InputOption::VALUE_REQUIRED);
        $this->addOption('firstName', null, InputOption::VALUE_OPTIONAL);
        $this->addOption('lastName', null, InputOption::VALUE_OPTIONAL);
        $this->addOption('permissions', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY);

        $this
            // the command help shown when running the command with the "--help" option
            ->setHelp('This command allows create a user')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {

            $user = new User();
            $user->setPermissions( $input->getOption('permissions') ?? []);
            $user->setEmail($input->getOption('email') ?? '');
            $user->setPassword($input->getOption('password') ?? '');
            $user->setFirstName($input->getOption('firstName') ?? '');
            $user->setLastname($input->getOption('lastName') ?? '');
            $this->entityManager->persist($user);
            $this->entityManager->flush();

        } catch (\Throwable $t) {
            $this->logger->error($t->getMessage(), ['exception' => $t]);
        }

        return Command::SUCCESS;
    }
}
