<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use App\Service\FileDownloader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Filesystem\Filesystem;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name:'external_code', length: 32, unique: true)]
    private ?string $externalCode = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?float $price = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?float $discount = null;

    /**
     * @var Collection<int, ProductFeature>
     */
    #[ORM\OneToMany(targetEntity: ProductFeature::class, mappedBy: 'product', orphanRemoval: true, cascade: ['persist', 'remove', 'refresh'])]
    private Collection $features;

    /**
     * @var Collection<int, ProductImage>
     */
    #[ORM\OneToMany(targetEntity: ProductImage::class, mappedBy: 'product', orphanRemoval: true, cascade: ['persist', 'remove', 'refresh'])]
    private Collection $images;

    public function __construct()
    {
        $this->features = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getExternalCode(): ?string
    {
        return $this->externalCode;
    }

    public function setExternalCode(string $externalCode): static
    {
        $this->externalCode = $externalCode;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getDiscount(): ?string
    {
        return $this->discount;
    }

    public function setDiscount(float $discount): static
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * @return Collection<int, ProductFeature>
     */
    public function getFeatures(): Collection
    {
        return $this->features;
    }

    public function addFeature(ProductFeature $feature): static
    {
        if (!$this->features->contains($feature)) {
            $this->features->add($feature);
            $feature->setProduct($this);
        }

        return $this;
    }

    public function removeFeature(ProductFeature $feature): static
    {
        if ($this->features->removeElement($feature)) {
            // set the owning side to null (unless already changed)
            if ($feature->getProduct() === $this) {
                $feature->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(ProductImage $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setProduct($this);
        }

        return $this;
    }

    public function removeImage(ProductImage $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getProduct() === $this) {
                $image->setProduct(null);
            }
        }

        return $this;
    }

    public function updateFeature(string $feature, string $value): static
    {
        $productFeature = $this->findFeatureByName($feature);

        if (!$productFeature) {
            $productFeature = new ProductFeature(
                $feature
            );
        }

        $productFeature->setValue($value);
        $this->addFeature($productFeature);

        return $this;
    }

    private function findFeatureByName(string $name): ?ProductFeature
    {
        foreach ($this->getFeatures() as $feature) {
            if ($name === $feature->getName()) {
                return $feature;
            }
        }

        return null;
    }

    public function updateImages(array $imageUrls, Filesystem $fileSystem, string $photoDir, FileDownloader $fileDownloader): ?static
    {
        $deleteProductImages = [];
        foreach ($this->getImages() as $image) {
            $image->setFileSystem($fileSystem);
            $image->setPhotoDir($photoDir);
            $deleteProductImages[$image->getId()] = $image;
        };

        foreach ($imageUrls as $imageUrl) {
            $imageUrl = trim($imageUrl);
            $extension = pathinfo($imageUrl, PATHINFO_EXTENSION);
            if ($extension) {
                $fileName = md5($imageUrl . $this->getExternalCode()) . '.' . $extension;
                $productImage = $this->findImageByFileName($fileName);

                if (!empty($productImage)) {
                    unset($deleteProductImages[$productImage->getId()]);
                } else {
                    $filePath = $photoDir . '/' . $fileName;

                    if ($fileSystem->exists($filePath) || $fileDownloader->downloadFile($imageUrl, $filePath)) {
                        $productImage = new ProductImage($fileName);
                        $this->addImage($productImage);
                    }
                }
            }
        }

        foreach ($deleteProductImages as $image) {
            $this->removeImage($image);
        }

        return $this;
    }

    private function findImageByFileName(string $fileName): ?ProductImage
    {
        foreach ($this->getImages() as $fileImage) {
            if ($fileName === $fileImage->getFileName()) {
                return $fileImage;
            }
        }

        return null;
    }
}
