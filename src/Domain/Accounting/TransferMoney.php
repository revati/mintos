<?php

namespace App\Domain\Accounting;

use App\Domain\Accounting\TransactionEntry\Type;
use Doctrine\Persistence\ManagerRegistry;

class TransferMoney
{
    private TransactionRepository $transactionRepository;

    public function __construct(
        private readonly ManagerRegistry $registry
    ) {
        $this->transactionRepository = new TransactionRepository($registry);
    }

    /**
     * Creates a transfer transaction between two accounts
     * 
     * @throws \InvalidArgumentException if currencies don't match or insufficient funds
     */
    public function execute(Account $fromAccount, Account $toAccount, int $amount, string $currency, string $description): Transaction
    {
        $transaction = new Transaction($description, $amount, $currency);
        
        // Create debit entry for source account
        $debit = new TransactionEntry(
            $transaction,
            $fromAccount,
            Type::DEBIT,
            $amount,
            $currency
        );

        $fromAccount->applyTransactionEntry($debit);

        // Create credit entry for destination account
        $credit = new TransactionEntry(
            $transaction,
            $toAccount,
            Type::CREDIT,
            $amount,
            $currency
        );

        $toAccount->applyTransactionEntry($credit);

        // Additional entries for fees etc can be added here

        $this->transactionRepository->storeTransaction($transaction);

        return $transaction;
    }
} 