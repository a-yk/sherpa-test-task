<?php

namespace App\Repository;

use App\Entity\ProductImage;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductImage::class);
    }

    public function findByFileNameAndProduct(string $fileName, Product $product): ?ProductImage
    {
        return $this->findOneBy(['file_name' => $fileName, 'product' => $product]);
    }
}
