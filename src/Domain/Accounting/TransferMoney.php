<?php

namespace App\Domain\Accounting;

use App\Domain\Accounting\TransactionEntry\Type;
use Doctrine\Persistence\ManagerRegistry;
use App\Domain\CurrencyExchange;

class TransferMoney
{
    private TransactionRepository $transactionRepository;
    private CurrencyExchange $exchange;

    public function __construct(
        ManagerRegistry $registry,
        CurrencyExchange $exchange
    ) {
        $this->transactionRepository = new TransactionRepository($registry);
        $this->exchange = $exchange;
    }

    /**
     * Creates a transfer transaction between two accounts
     * 
     * @throws \InvalidArgumentException if currencies don't match or insufficient funds
     */
    public function execute(Account $fromAccount, Account $toAccount, int $amount, string $currency, string $description): Transaction
    {
        $transaction = new Transaction($description, $amount, $currency);

        $creditAmount = $this->exchange->convert(
            $amount,
            $toAccount->getCurrency(),
            $fromAccount->getCurrency(),
        );
        
        // Create debit entry for source account
        $credit = new TransactionEntry(
            $transaction,
            $fromAccount,
            Type::CREDIT,
            $creditAmount,
            $fromAccount->getCurrency()
        );

        $fromAccount->applyTransactionEntry($credit);

        // Create credit entry for destination account
        $debit = new TransactionEntry(
            $transaction,
            $toAccount,
            Type::DEBIT,
            $amount,
            $currency
        );

        $toAccount->applyTransactionEntry($debit);

        // Additional entries for fees etc can be added here

        $this->transactionRepository->storeTransaction($transaction);

        return $transaction;
    }
} 