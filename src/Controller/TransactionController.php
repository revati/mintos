<?php

namespace App\Controller;

use App\Domain\Accounting;
use App\DTO\InitializeTransactionRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TransactionController extends AbstractController
{
    public function __construct(
        private readonly Accounting $accounting,
        private readonly ValidatorInterface $validator
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

    #[Route('/api/transactions/initialize', name: 'api_transactions_initialize', methods: ['POST'])]
    public function initializeTransaction(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            throw new BadRequestHttpException('Invalid JSON');
        }

        $dto = new InitializeTransactionRequest();
        $dto->debitAccount = $data['debit_account_id'] ?? '';
        $dto->creditAccount = $data['credit_account_id'] ?? '';
        $dto->amount = $data['amount'] ?? 0;
        $dto->currency = $data['currency'] ?? '';
        $dto->description = $data['description'] ?? '';

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            throw new BadRequestHttpException((string) $errors);
        }

        $debitAccount = $this->accounting->getAccount($dto->debitAccount);
        if (!$debitAccount) {
            throw new NotFoundHttpException(sprintf('Account with ID %s not found', $dto->debitAccount));
        }

        $creditAccount = $this->accounting->getAccount($dto->creditAccount);
        if (!$creditAccount) {
            throw new NotFoundHttpException(sprintf('Counterparty account with ID %s not found', $dto->creditAccount));
        }

        try {
            $transaction = $this->accounting->transferMoney(
                $debitAccount,
                $creditAccount,
                $dto->amount,
                $dto->currency,
                $dto->description
            );

            return $this->json([
                'transaction' => $transaction
            ], 201, [], ['groups' => ['transactionEntry:read']]);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }
} 