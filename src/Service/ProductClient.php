<?php declare(strict_types=1);

namespace App\Service;

use RuntimeException;
use Shop\Common\ServiceDiscovery\ServiceLocatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ProductClient {
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ServiceLocatorInterface $locator,
    ) {}

    public function getAll(): array
    {
        $productService = $this->locator->getInstance('product');
        if (!$productService) {
            throw new \RuntimeException('Product service not available');
        }
        $url = $productService->getBaseUri() . '/products/all';
        return $this->httpClient->request('GET', $url)->toArray();
    }

    public function findById(string $id): array {
        $productService = $this->locator->getInstance('product');
        if (!$productService) {
            throw new RuntimeException('Product service not available');
        }
        $url = $productService->getBaseUri() . '/product/' . urlencode($id);
        return $this->httpClient->request('GET', $url, [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ])->toArray();
    }

}