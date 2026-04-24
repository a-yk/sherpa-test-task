<?php

namespace App\Handler\ImportHandler;

use App\Entity\Import;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\FileDownloader;
use App\Handler\ImportHandler\ImportHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use PhpOffice\PhpSpreadsheet\IOFactory;

class XmlImportHandler implements ImportHandlerInterface
{
    const COLUMN_EXTERNAL_CODE = 5;
    const COLUMN_NAME = 4;
    const COLUMN_DESCRIPTION = 10;
    const COLUMN_PRICE = 8;
    const COLUMN_PURCHASING_PRICE = 11;
    const COLUMN_PACK_IMAGE = 36;
    const COLUMN_IMAGES = 37;

    const COLUMN_FEATURE_PREFIX = 'Доп. поле: ';
    const COLUMN_FEATURE_EXCEPTION = [
        'Доп. поле: Ссылка на упаковку',
        'Доп. поле: Ссылки на фото',
        'Доп. поле: seo title',
        'Доп. поле: seo h1',
        'Доп. поле: seo description'
    ];

    public function __construct(
        private ProductRepository $productRepository,
        private EntityManagerInterface $entityManager,
        private Filesystem $fileSystem,
        private FileDownloader $fileDownloader,
        #[Autowire('%import_dir%')] private string $importDir,
        #[Autowire('%photo_dir%')] private string $photoDir,
    ) {
        if (!$this->fileSystem->exists($this->photoDir)) {
            $this->fileSystem->mkdir($this->photoDir, 0755);
        }
    }

    public function __invoke(Import $import): void
    {
        $filePath = $this->importDir . '/' . $import->getFilename();

        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        $features_column_map = [];
        $headers = array_shift($rows);

        foreach ($headers as $key => $header) {
            if (mb_strpos($header, self::COLUMN_FEATURE_PREFIX) === 0
                && !in_array($header, self::COLUMN_FEATURE_EXCEPTION)
            ) {
                $features_column_map[$key] = mb_substr($header, mb_strlen(self::COLUMN_FEATURE_PREFIX));
            }
        }

        foreach ($rows as $row) {
            $product = $this->productRepository->findByExternalCode($row[self::COLUMN_EXTERNAL_CODE]);
            if (!$product) {
                $product = new Product();
                $product->setExternalCode($row[self::COLUMN_EXTERNAL_CODE]);
            }

            $product->setName($row[self::COLUMN_NAME]);
            $product->setDescription($row[self::COLUMN_DESCRIPTION]);
            $product->setPrice((float) $row[self::COLUMN_PRICE]);
            $product->setDiscount(($product->getPrice() - (float)$row[self::COLUMN_PURCHASING_PRICE]) / (float)$row[self::COLUMN_PURCHASING_PRICE] * 100);

            foreach ($features_column_map as $column => $feature) {
                $product->updateFeature($feature, (string) $row[$column]);
            }

            $imageUrls = explode(',', $row[self::COLUMN_IMAGES]);
            if (!empty($row[self::COLUMN_PACK_IMAGE])) {
                $imageUrls[] = $row[self::COLUMN_PACK_IMAGE];
            }

            $product->updateImages($imageUrls, $this->fileSystem, $this->photoDir, $this->fileDownloader);

            $this->entityManager->persist($product);
            $this->entityManager->flush();
        }
    }
}
