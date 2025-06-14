<?php

namespace App\Controller;

use App\Domain\Accounting;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TransactionController extends AbstractController
{
    public function __construct(
        private readonly Accounting $accounting
    ) {
    }

    #[Route('/api/transactions', name: 'api_transactions_list', methods: ['GET'])]
    public function listTransactions(Request $request): JsonResponse
    {
        $filters = $request->query->all('filter');
        $accountId = $filters['account_id'] ?? null;

        if (!$accountId) {
            throw new NotFoundHttpException('Account ID is required');
        }

        $account = $this->accounting->getAccount($accountId);
        if (!$account) {
            throw new NotFoundHttpException(sprintf('Account with ID %s not found', $accountId));
        }

        $limit = (int) ($request->query->get('limit') ?? 10);
        $offset = (int) ($request->query->get('offset') ?? 0);

        $transactions = $this->accounting->listTransactions($account, $limit, $offset);
        $total = $this->accounting->countTransactions($account);
        
        return $this->json([
            'transactions' => $transactions,
            'pagination' => [
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
                'has_more' => ($offset + $limit) < $total
            ]
        ], 200, [], ['groups' => ['transactionEntry:read']]);
    }
} 