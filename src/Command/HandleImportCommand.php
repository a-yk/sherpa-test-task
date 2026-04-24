<?php

namespace App\Command;

use App\Repository\ImportRepository;
use App\Handler\ImportHandler\ImportHandlerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:handle-import',
    description: 'Handle import file',
)]
class HandleImportCommand extends Command
{
    public function __construct(
        private ImportRepository $importRepository,
        private ImportHandlerInterface $importHandler
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED, 'ID of import')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $id = $input->getArgument('id');

        if (!$id) {
            $io->error('You must provide an id.');

            return Command::FAILURE;
        }

        $io->info('Start handling.');

        $import = $this->importRepository->find((int) $id);
        if (!$import) {
            $io->error('Import not found.');

            return Command::FAILURE;
        }

        ($this->importHandler)($import);

        $io->info('Stop handling.');

        return Command::SUCCESS;
    }
}
