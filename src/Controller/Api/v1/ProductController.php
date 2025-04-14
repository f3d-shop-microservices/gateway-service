<?php

namespace App\Controller\Api\v1;

use App\Service\ProductClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductClient $productClient,
    ) {
    }

    #[Route('/products', name: 'app_api_gateway_products', methods: ['GET'])]
    public function proxyProducts(): Response
    {
        $products = $this->productClient->getAll();
        return $this->json($products);
    }

    #[Route('/product/{id}', name: 'app_api_gateway_product', methods: ['GET'], priority: 1)]
    public function proxyProduct(string $id): Response
    {
        try {
            $product = $this->productClient->findById($id);
            return $this->json($product);
        } catch (ClientExceptionInterface $e) {
            return new JsonResponse(['error' => 'Product not found'], 404);
        } catch (TransportExceptionInterface | ServerExceptionInterface | RedirectionExceptionInterface $e) {
            return new JsonResponse(['error' => 'Internal error while accessing product service'], 500);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Unexpected error'], 500);
        }
    }
}
