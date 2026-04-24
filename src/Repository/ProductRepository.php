<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;


class ProductRepository extends ServiceEntityRepository
{
    const PRODUCTS_PER_PAGE = 3;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findByExternalCode(string $externalCode): ?Product
    {
        return $this->findOneBy(['externalCode' => $externalCode]);
    }

    public function findProductByPage(int $page = 1, int $itemPerPage = 16): Paginator
    {
        $query = $this->createQueryBuilder('p')
            ->andWhere('1 = 1')
            ->orderBy('p.name', 'ASC')
            ->setMaxResults($itemPerPage)
            ->setFirstResult(($page - 1) * $itemPerPage)
            ->getQuery();

        return new Paginator($query);
    }
}
