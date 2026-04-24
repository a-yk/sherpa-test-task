<?php

namespace App\Command;

use \App\Entity\Import;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'app:bootstrap',
    description: 'Bootstrap application',
)]
class BootstrapCommand extends Command
{
    public function __construct(
        private Filesystem $fileSystem,
        private EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%')] private string $projectDir
    ) {
        parent::__construct();
    }

    protected function configure(): void {}

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->note('Start bootstrapping!');

        $exampleFile = $this->projectDir . '/var/data/example.xlsx';

        if ($this->fileSystem->exists($exampleFile))
        {
            $importDir = $this->projectDir . '/public/import';

            if (!$this->fileSystem->exists($importDir)) {
                $this->fileSystem->mkdir($importDir);
            }
            $this->fileSystem->copy($exampleFile, $importDir . '/example.xlsx');

            $import = new Import();
            $import->setFileName('example.xlsx');
            $this->entityManager->persist($import);
            $this->entityManager->flush();
            $process = new Process(['php', $this->projectDir . '/bin/console', 'app:handle-import', $import->getId()]);

            $process->run();

            if ($process->isSuccessful()) {
                $io->success('Application has been bootstrapped.');
            } else {
                $io->error('Application has not been bootstrapped.');
            }

            $this->fileSystem->remove($importDir . '/example.xlsx');
            $this->entityManager->remove($import);
            $this->entityManager->flush();
        } else {
            $io->error('Example file not found.');
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
