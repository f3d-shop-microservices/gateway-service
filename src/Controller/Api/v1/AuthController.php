<?php

namespace App\Controller\Api\v1;

use App\Service\AuthClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AuthController extends AbstractController
{
    public function __construct(
        private readonly AuthClient $authClient,
    ) {
    }

    #[Route('/register', name: 'app_api_gateway_register', methods: ['POST'])]
    public function proxyRegister(Request $request): Response
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        $response = $this->authClient->register($email, $password);
        return $this->json($response);
    }

    #[Route('/login', name: 'app_api_gateway_login', methods: ['POST'])]
    public function proxyLogin(Request $request): Response
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        $token = $this->authClient->login($email, $password);
        return $this->json($token);
    }
}
