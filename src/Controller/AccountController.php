<?php

namespace App\Controller;

use App\Domain\Accounting;
use App\Domain\Access;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AccountController extends AbstractController
{
    public function __construct(
        private readonly Accounting $accounting,
        private readonly Access $access
    ) {
    }

    #[Route('/api/accounts', name: 'api_accounts_list', methods: ['GET'])]
    public function listAccounts(Request $request): JsonResponse
    {
        $filters = $request->query->all('filter');
        $userId = $filters['user_id'] ?? null;

        if (!$userId) {
            throw new NotFoundHttpException('User ID is required');
        }

        $user = $this->access->getUser($userId);
        
        if (!$user) {
            throw new NotFoundHttpException(sprintf('User with ID %s not found', $userId));
        }

        $accounts = $this->accounting->listAccounts($user);
        
        return $this->json([
            'accounts' => $accounts
        ]);
    }
} 