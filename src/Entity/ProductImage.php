<?php

namespace App\Entity;

use App\Repository\ProductImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Filesystem\Filesystem;

#[ORM\Entity(repositoryClass: ProductImageRepository::class)]
#[ORM\Index(name: 'idx_file_name', columns: ['file_name'])]
#[ORM\HasLifecycleCallbacks]
class ProductImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    public function __construct(
        #[ORM\Column(name:'file_name', length: 255)]
        private ?string $fileName = null,

        private ?Filesystem $fileSystem = null,

        private ?string $photoDir = null,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function setPhotoDir(?string $photoDir): static
    {
        $this->photoDir = $photoDir;
        return $this;
    }

    public function setFileSystem(?Filesystem $fileSystem): static
    {
        $this->fileSystem = $fileSystem;
        return $this;
    }

    #[ORM\PreRemove]
    public function deleteImageFile()
    {
        if (!$this->fileSystem || !$this->photoDir) {
            return;
        }

        $imagePath = $this->photoDir . '/' . $this->getFileName();
        if ($this->fileSystem->exists($imagePath)) {
            $this->fileSystem->remove($imagePath);
        }
    }
}
