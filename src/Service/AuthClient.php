<?php declare(strict_types=1);

namespace App\Service;

use Shop\Common\ServiceDiscovery\ServiceLocatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class AuthClient {
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ServiceLocatorInterface $locator,
    ) {}

    public function login(string $email, string $password): array
    {
        $authService = $this->locator->getInstance('auth');
        if (!$authService) {
            throw new \RuntimeException('Auth service not available');
        }
        $url = $authService->getBaseUri() . '/api/login';
        return $this->httpClient->request('POST', $url, [
            'body' => json_encode([
                'email' => $email,
                'password' => $password,
            ]),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ])->toArray();
    }

    public function register(string $email, string $password): array {
        $authService = $this->locator->getInstance('auth');
        if (!$authService) {
            throw new \RuntimeException('Auth service not available');
        }
        $url = $authService->getBaseUri() . '/register';
        return $this->httpClient->request('POST', $url, [
            'body' => json_encode([
                'email' => $email,
                'password' => $password,
            ]),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ])->toArray();
    }

    public function validateToken(string $token): array {
        $authService = $this->locator->getInstance('auth');
        if (!$authService) {
            throw new \RuntimeException('Auth service not available');
        }
        $url = $authService->getBaseUri() . '/api/validate-token';
        return $this->httpClient->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,

            ],
        ])->toArray();
    }

}