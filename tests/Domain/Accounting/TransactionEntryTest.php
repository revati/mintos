<?php

namespace App\Tests\Domain\Accounting;

use App\Domain\Accounting\Transaction;
use App\Domain\Accounting\TransactionEntry;
use App\Domain\Accounting\TransactionEntry\Type;
use App\Domain\Accounting\Account;
use App\Domain\Access\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class TransactionEntryTest extends TestCase
{
    private Transaction $transaction;
    private Account $account;
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User('test@example.com');
        $this->account = new Account($this->user, 'Test Account', 'USD', 1000);
        $this->transaction = new Transaction('Test transaction', 100, 'USD');
    }

    public function testTransactionEntryCreation(): void
    {
        // Arrange
        $entry = new TransactionEntry(
            $this->transaction,
            $this->account,
            Type::DEBIT,
            100,
            'USD'
        );

        // Assert
        $this->assertInstanceOf(Uuid::class, $entry->getId());
        $this->assertSame($this->transaction, $entry->getTransaction());
        $this->assertSame($this->account, $entry->getAccount());
        $this->assertSame(Type::DEBIT, $entry->getType());
        $this->assertSame('Test transaction', $entry->getDescription());
        $this->assertSame(100, $entry->getAbsoluteAmount());
        $this->assertSame('USD', $entry->getCurrency());
    }

    public function testTransactionEntryWithCustomDescription(): void
    {
        // Arrange
        $entry = new TransactionEntry(
            $this->transaction,
            $this->account,
            Type::DEBIT,
            100,
            'USD',
            'Custom description'
        );

        // Assert
        $this->assertSame('Custom description', $entry->getDescription());
    }

    public function testGetRelativeAmountForDebit(): void
    {
        // Arrange
        $entry = new TransactionEntry(
            $this->transaction,
            $this->account,
            Type::DEBIT,
            100,
            'USD'
        );

        // Assert
        $this->assertSame(100, $entry->getRelativeAmount());
    }

    public function testGetRelativeAmountForCredit(): void
    {
        // Arrange
        $entry = new TransactionEntry(
            $this->transaction,
            $this->account,
            Type::CREDIT,
            100,
            'USD'
        );

        // Assert
        $this->assertSame(-100, $entry->getRelativeAmount());
    }

    public function testTransactionEntryIsAddedToTransaction(): void
    {
        // Arrange
        $entry = new TransactionEntry(
            $this->transaction,
            $this->account,
            Type::DEBIT,
            100,
            'USD'
        );

        // Assert
        $this->assertCount(1, $this->transaction->getEntries());
        $this->assertSame($entry, $this->transaction->getEntries()->first());
    }

    public function testTransactionEntryWithDifferentCurrency(): void
    {
        // Arrange
        $entry = new TransactionEntry(
            $this->transaction,
            $this->account,
            Type::DEBIT,
            100,
            'EUR'
        );

        // Assert
        $this->assertSame('EUR', $entry->getCurrency());
    }

    public function testTransactionEntryWithZeroAmount(): void
    {
        // Arrange
        $entry = new TransactionEntry(
            $this->transaction,
            $this->account,
            Type::DEBIT,
            0,
            'USD'
        );

        // Assert
        $this->assertSame(0, $entry->getAbsoluteAmount());
        $this->assertSame(0, $entry->getRelativeAmount());
    }

    public function testTransactionEntryWithLargeAmount(): void
    {
        // Arrange
        $entry = new TransactionEntry(
            $this->transaction,
            $this->account,
            Type::DEBIT,
            PHP_INT_MAX,
            'USD'
        );

        // Assert
        $this->assertSame(PHP_INT_MAX, $entry->getAbsoluteAmount());
        $this->assertSame(PHP_INT_MAX, $entry->getRelativeAmount());
    }
} 