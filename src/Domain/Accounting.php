<?php

namespace App\Domain;

use App\Domain\Access\User;
use App\Domain\Accounting\Account;
use App\Domain\Accounting\AccountRepository;
use App\Domain\Accounting\Transaction;
use App\Domain\Accounting\TransferMoney;

class Accounting
{
    public function __construct(
        private readonly AccountRepository $accountRepository,
        private readonly TransferMoney $transferMoney
    ) {
    }

    /**
     * Fetches all accounts for a user
     * 
     * @return Account[] Returns an array of Account objects
     */
    public function listAccounts(User $user): array
    {
        return $this->accountRepository->listForUser($user);
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