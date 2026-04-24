<?php

namespace App\Controller\Api;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use OpenApi\Attributes as OA;

final class ProductsController extends AbstractController
{
    #[Route('/api/products', name: 'api_products', methods:['GET'])]
    #[OA\Tag('Products')]
    #[OA\Get(
        path: '/api/products',
        summary: 'Returns list of products',
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                description: 'Page',
                required: false,
            ),
            new OA\Parameter(
                name: 'items_per_page',
                in: 'query',
                description: 'Products per page',
                required: false,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of products'
            )
        ]
    )]
    public function index(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $itemsPerPage = $request->query->getInt('items_per_page', 16);

        $paginator = $productRepository->findProductByPage($page);
        $products = $paginator->getQuery()->getResult();
        $totalItems = count($paginator);
        $totalPages = ceil($totalItems / $itemsPerPage);

        return $this->json([
            'products' => $products,
            'page' => $page,
            'total_pages' => $totalPages,
        ], context:[AbstractNormalizer::IGNORED_ATTRIBUTES => ['product']]);
    }

    #[Route('/api/products/{id}', name: 'product', methods:['GET'])]
    #[OA\Tag('Products')]
    #[OA\Get(
        path: '/api/products/{id}',
        summary: 'Return information about product',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID of product',
                required: true,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail information about product'
            )
        ]
    )]
    public function product(Product $product): JsonResponse
    {
        return $this->json($product, context:[AbstractNormalizer::IGNORED_ATTRIBUTES => ['product']]);
    }

}
