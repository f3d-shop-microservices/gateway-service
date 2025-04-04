<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class AuthTokenListener
{
    private HttpClientInterface $httpClient;
    public function __construct(
        HttpClientInterface $httpClient
    ) {
        $this->httpClient = $httpClient;
    }

    #[AsEventListener(event: KernelEvents::REQUEST)]
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $publicPaths = ['/api/login', '/api/register'];
        foreach ($publicPaths as $path) {
            if (str_starts_with($request->getPathInfo(), $path)) {
                return;
            }
        }

        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            $event->setResponse(new JsonResponse(['error' => 'Missing or invalid Authorization header'], 401));
            return;
        }

        $token = substr($authHeader, 7);

        try {
            $response = $this->httpClient->request('GET', $_ENV['AUTH_SVC_HOST'] . '/api/validate-token', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]);

            $data = $response->toArray();

            if (!($data['valid'] ?? false)) {
                $event->setResponse(new JsonResponse(['error' => 'Invalid token'], 401));
            }

        } catch (\Exception $e) {
            $event->setResponse(new JsonResponse(['error' => 'Token validation failed'], 401));
        }
    }
}
