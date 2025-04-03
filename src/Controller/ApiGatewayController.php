<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ApiGatewayController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient) {
        $this->httpClient = $httpClient;
    }

    #[Route('/api/products', name: 'app_api_gateway', methods: ['GET'])]
    public function proxyProducts(): Response
    {
        $response = $this->httpClient->request('GET', $_ENV['PRODUCT_SVC_HOST'] . '/products/all');

        return new Response(
            $response->getContent(),
            $response->getStatusCode(),
            ['Content-Type' => $response->getHeaders()['content-type'][0]]
        );
    }
}
