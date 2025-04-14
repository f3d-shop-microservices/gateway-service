<?php

namespace App\EventListener;

use App\Service\AuthClient;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class AuthTokenListener
{
    private HttpClientInterface $httpClient;
    public function __construct(
        HttpClientInterface $httpClient,
        private readonly AuthClient $authClient,

        #[Autowire('%env(string:API_VERSION_PREFIX)%')]
        private readonly string $apiVersionPrefix
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

        $publicPaths = [
            $this->apiVersionPrefix . '/login',
            $this->apiVersionPrefix . '/register'
        ];
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
            $data = $this->authClient->validateToken($token);

            if (!($data['valid'] ?? false)) {
                $event->setResponse(new JsonResponse(['error' => 'Invalid token'], 401));
            }

        } catch (\Exception $e) {
            $event->setResponse(new JsonResponse(['error' => 'Token validation failed'], 401));
        }
    }
}
