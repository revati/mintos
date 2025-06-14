<?php

namespace App\Domain;

use App\Domain\Access\User;
use App\Domain\Accounting\Account;
use App\Domain\Accounting\AccountRepository;
use App\Domain\Accounting\Transaction;
use App\Domain\Accounting\TransferMoney;
use App\Domain\Accounting\TransactionEntryRepository;

class Accounting
{
    private AccountRepository $accountRepository;
    private TransactionEntryRepository $transactionRepository;
    private transferMoney $transferMoney;

    public function __construct(
        AccountRepository $accountRepository,
        TransactionEntryRepository $transactionRepository,
        transferMoney $transferMoney
    ) {
        $this->accountRepository = $accountRepository;
        $this->transactionRepository = $transactionRepository;
        $this->transferMoney = $transferMoney;
    }

    /**
     * Fetches account by id
     * 
     * @return Account|null Returns an Account object or null if not found
     */
    public function getAccount(string $id): ?Account
    {
        return $this->accountRepository->find($id);
    }

    /**
     * Lists all accounts for a user
     * 
     * @param User $user The user to find accounts for
     * @return array<Account> Returns an array of Account objects
     */
    public function listAccounts(User $user): array
    {
        return $this->accountRepository->listForUser($user);
    }

    /**
     * Lists transactions for an account with pagination
     * 
     * @param int $limit Maximum number of transactions to return
     * @param int $offset Number of transactions to skip
     * @return array<Transaction> Returns an array of Transaction objects
     */
    public function listTransactions(Account $account, int $limit = 10, int $offset = 0): array
    {
        return $this->transactionRepository->findByAccount($account, $limit, $offset);
    }

    /**
     * Counts total number of transactions for an account
     * 
     * @return int Total number of transactions
     */
    public function countTransactions(Account $account): int
    {
        return $this->transactionRepository->countByAccount($account);
    }

    /**
     * Creates a transfer transaction between two accounts
     * 
     * @throws \InvalidArgumentException if currencies don't match or insufficient funds
     */
    public function transferMoney(Account $fromAccount, Account $toAccount, int $amount, string $currency, string $description): Transaction
    {
        return $this->transferMoney->execute($fromAccount, $toAccount, $amount, $currency, $description);
    }
} 