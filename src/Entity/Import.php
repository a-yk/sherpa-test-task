<?php

namespace App\Entity;

use App\Repository\ImportRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Filesystem\Filesystem;

#[ORM\Entity(repositoryClass: ImportRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Import
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(length: 255)]
    private ?string $file_name = null;

    private ?Filesystem $fileSystem = null;

    private ?string $importDir = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->file_name;
    }

    public function setFileName(string $file_name): static
    {
        $this->file_name = $file_name;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->created_at = new \DateTimeImmutable();
    }

    public function setImportDir(?string $importDir): static
    {
        $this->importDir = $importDir;
        return $this;
    }

    public function setFileSystem(?Filesystem $fileSystem): static
    {
        $this->fileSystem = $fileSystem;
        return $this;
    }

    #[ORM\PreRemove]
    public function deleteFile()
    {
        if (!$this->fileSystem || !$this->importDir) {
            return;
        }

        $imagePath = $this->importDir . '/' . $this->getFileName();
        if ($this->fileSystem->exists($imagePath)) {
            $this->fileSystem->remove($imagePath);
        }
    }
}
