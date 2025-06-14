<?php

namespace App\Controller;

use App\Domain\Access;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    public function __construct(
        private readonly Access $access
    ) {
    }

    #[Route('/api/users', name: 'api_users_list', methods: ['GET'])]
    public function listUsers(): JsonResponse
    {
        $users = $this->access->listUsers();
        
        return $this->json([
            'users' => $users
        ]);
    }
} 