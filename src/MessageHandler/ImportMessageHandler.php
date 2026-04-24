<?php

namespace App\MessageHandler;

use App\Handler\ImportHandler\ImportHandlerInterface;
use App\Message\ImportMessage;
use App\Repository\ImportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;


#[AsMessageHandler]
class ImportMessageHandler
{
    public function __construct(
        private ImportRepository $importRepository,
        private EntityManagerInterface $entityManager,
        private ImportHandlerInterface $importHandler,
        private Filesystem $fileSystem,
        #[Autowire('%import_dir%')] private string $importDir,
    ) {}

    public function __invoke(ImportMessage $message) {
        $import = $this->importRepository->find((int) $message->getId());

        if ($import) {
            ($this->importHandler)($import);
            $import->setImportDir($this->importDir);
            $import->setFileSystem($this->fileSystem);
            $this->entityManager->remove($import);
            $this->entityManager->flush();
        }
    }
}
