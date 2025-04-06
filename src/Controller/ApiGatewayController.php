<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ApiGatewayController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient) {
        $this->httpClient = $httpClient;
    }

    #[Route('/api/register', name: 'app_api_gateway_register', methods: ['POST'])]
    public function proxyRegister(Request $request): Response
    {
        $response = $this->httpClient->request('POST', $_ENV['AUTH_SVC_HOST'] . '/register', [
            'body' => $request->getContent(),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        return new Response(
            $response->getContent(),
            $response->getStatusCode(),
            ['Content-Type' => $response->getHeaders()['content-type'][0]]
        );
    }

    #[Route('/api/login', name: 'app_api_gateway_login', methods: ['POST'])]
    public function proxyLogin(Request $request): Response
    {
        $response = $this->httpClient->request('POST', $_ENV['AUTH_SVC_HOST'] . '/api/login', [
            'body' => $request->getContent(),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        return new Response(
            $response->getContent(),
            $response->getStatusCode(),
            ['Content-Type' => $response->getHeaders()['content-type'][0]]
        );
    }

    #[Route('/api/products', name: 'app_api_gateway_products', methods: ['GET'])]
    public function proxyProducts(): Response
    {
        $response = $this->httpClient->request('GET', $_ENV['PRODUCT_SVC_HOST'] . '/products/all');

        return new Response(
            $response->getContent(),
            $response->getStatusCode(),
            ['Content-Type' => $response->getHeaders()['content-type'][0]]
        );
    }

    #[Route('/api/product/{id}', name: 'app_api_gateway_product', methods: ['GET'], priority: 1)]
    public function proxyProduct(string $id): Response
    {
        $url = $_ENV['PRODUCT_SVC_HOST'] . '/product/' . urlencode($id);

        try {
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            return new Response(
                $response->getContent(),
                $response->getStatusCode(),
                ['Content-Type' => $response->getHeaders()['content-type'][0]]
            );
        } catch (ClientExceptionInterface $e) {
            return new JsonResponse(['error' => 'Product not found'], 404);
        } catch (TransportExceptionInterface | ServerExceptionInterface | RedirectionExceptionInterface $e) {
            return new JsonResponse(['error' => 'Internal error while accessing product service'], 500);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Unexpected error'], 500);
        }
    }

    #[Route('/api/product/health', name: 'app_api_gateway_product_health', methods: ['GET'], priority: 2)]
    public function proxyProductHealth(): Response
    {
        $url = $_ENV['PRODUCT_SVC_HOST'] . '/health';

        try {
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            return new Response(
                $response->getContent(),
                $response->getStatusCode(),
                ['Content-Type' => $response->getHeaders()['content-type'][0]]
            );
        } catch (ClientExceptionInterface $e) {
            return new JsonResponse(['error' => 'Product not found'], 404);
        } catch (TransportExceptionInterface | ServerExceptionInterface | RedirectionExceptionInterface $e) {
            return new JsonResponse(['error' => 'Internal error while accessing product service'], 500);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Unexpected error'], 500);
        }
    }

    #[Route('/api/auth/health', name: 'app_api_gateway_auth_health', methods: ['GET'])]
    public function proxyAuthHealth(): Response
    {
        $url = $_ENV['AUTH_SVC_HOST'] . '/health';

        try {
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            return new Response(
                $response->getContent(),
                $response->getStatusCode(),
                ['Content-Type' => $response->getHeaders()['content-type'][0]]
            );
        } catch (ClientExceptionInterface $e) {
            return new JsonResponse(['error' => 'Product not found'], 404);
        } catch (TransportExceptionInterface | ServerExceptionInterface | RedirectionExceptionInterface $e) {
            return new JsonResponse(['error' => 'Internal error while accessing product service'], 500);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Unexpected error'], 500);
        }
    }
}
