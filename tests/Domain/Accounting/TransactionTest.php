<?php

namespace App\Tests\Domain\Accounting;

use App\Domain\Accounting\Transaction;
use App\Domain\Accounting\TransactionEntry;
use App\Domain\Accounting\TransactionEntry\Type;
use App\Domain\Accounting\Account;
use App\Domain\Access\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class TransactionTest extends TestCase
{
    private Transaction $transaction;
    private Account $account1;
    private Account $account2;

    protected function setUp(): void
    {
        $user = new User('test@example.com');
        $this->account1 = new Account($user, 'Account 1', 'USD', 1000);
        $this->account2 = new Account($user, 'Account 2', 'USD', 1000);
        $this->transaction = new Transaction('Test transfer', 100, 'USD');
    }

    public function testTransactionCreation(): void
    {
        // Assert
        $this->assertInstanceOf(Uuid::class, $this->transaction->getId());
        $this->assertSame('Test transfer', $this->transaction->getDescription());
        $this->assertSame(100, $this->transaction->getAmount());
        $this->assertSame('USD', $this->transaction->getCurrency());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->transaction->getCreatedAt());
        $this->assertCount(0, $this->transaction->getEntries());
    }

    public function testAddEntry(): void
    {
        // Arrange
        $entry = new TransactionEntry($this->transaction, $this->account1, Type::DEBIT, 100, 'USD');

        // Assert
        $this->assertCount(1, $this->transaction->getEntries());
        $this->assertSame($entry, $this->transaction->getEntries()->first());
    }

    public function testAddMultipleEntries(): void
    {
        // Arrange
        $entry1 = new TransactionEntry($this->transaction, $this->account1, Type::DEBIT, 100, 'USD');
        $entry2 = new TransactionEntry($this->transaction, $this->account2, Type::CREDIT, 100, 'USD');

        // Assert
        $this->assertCount(2, $this->transaction->getEntries());
        $this->assertTrue($this->transaction->getEntries()->contains($entry1));
        $this->assertTrue($this->transaction->getEntries()->contains($entry2));
    }

    public function testTransactionWithDifferentAmounts(): void
    {
        // Arrange
        $transaction = new Transaction('Split payment', 150, 'USD');
        $entry1 = new TransactionEntry($transaction, $this->account1, Type::DEBIT, 100, 'USD');
        $entry2 = new TransactionEntry($transaction, $this->account2, Type::DEBIT, 50, 'USD');

        // Assert
        $this->assertCount(2, $transaction->getEntries());
        $this->assertTrue($transaction->getEntries()->contains($entry1));
        $this->assertTrue($transaction->getEntries()->contains($entry2));
    }

    public function testTransactionWithDifferentCurrencies(): void
    {
        // Arrange
        $transaction = new Transaction('Currency exchange', 100, 'USD');
        $entry1 = new TransactionEntry($transaction, $this->account1, Type::DEBIT, 100, 'USD');
        $entry2 = new TransactionEntry($transaction, $this->account2, Type::CREDIT, 85, 'EUR');

        // Assert
        $this->assertCount(2, $transaction->getEntries());
        $this->assertTrue($transaction->getEntries()->contains($entry1));
        $this->assertTrue($transaction->getEntries()->contains($entry2));
    }

    public function testTransactionWithCustomDescription(): void
    {
        // Arrange
        $entry = new TransactionEntry(
            $this->transaction,
            $this->account1,
            Type::DEBIT,
            100,
            'USD',
            'Custom description'
        );

        // Assert
        $this->assertSame('Custom description', $entry->getDescription());
    }

    public function testTransactionWithDefaultDescription(): void
    {
        // Arrange
        $entry = new TransactionEntry(
            $this->transaction,
            $this->account1,
            Type::DEBIT,
            100,
            'USD'
        );

        // Assert
        $this->assertSame('Test transfer', $entry->getDescription());
    }
}
