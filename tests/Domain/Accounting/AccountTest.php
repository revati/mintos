<?php

namespace App\Tests\Domain\Accounting;

use App\Domain\Accounting\Account;
use App\Domain\Accounting\Transaction;
use App\Domain\Accounting\TransactionEntry;
use App\Domain\Accounting\TransactionEntry\Type;
use App\Domain\Access\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class AccountTest extends TestCase
{
    private User $user;
    private Account $account;
    private Transaction $transaction;

    protected function setUp(): void
    {
        $this->user = new User('test@example.com');
        $this->account = new Account($this->user, 'Test Account', 'USD', 1000);
        $this->transaction = new Transaction('Test transaction', 100, 'USD');
    }

    public function testAccountCreation(): void
    {
        // Assert
        $this->assertInstanceOf(Uuid::class, $this->account->getId());
        $this->assertSame('Test Account', $this->account->getName());
        $this->assertSame(1000, $this->account->getBalance());
        $this->assertSame('USD', $this->account->getCurrency());
        $this->assertSame($this->user, $this->account->getUser());
    }

    public function testApplyTransactionEntryDebit(): void
    {
        // Arrange
        $entry = new TransactionEntry($this->transaction, $this->account, Type::DEBIT, 100, 'USD');

        // Act
        $this->account->applyTransactionEntry($entry);

        // Assert
        $this->assertSame(1100, $this->account->getBalance());
    }

    public function testApplyTransactionEntryCredit(): void
    {
        // Arrange
        $entry = new TransactionEntry($this->transaction, $this->account, Type::CREDIT, 100, 'USD');

        // Act
        $this->account->applyTransactionEntry($entry);

        // Assert
        $this->assertSame(900, $this->account->getBalance());
    }

    public function testApplyTransactionEntryChain(): void
    {
        // Arrange
        $entry1 = new TransactionEntry($this->transaction, $this->account, Type::CREDIT, 200, 'USD');
        $entry2 = new TransactionEntry($this->transaction, $this->account, Type::DEBIT, 50, 'USD');
        $entry3 = new TransactionEntry($this->transaction, $this->account, Type::CREDIT, 100, 'USD');

        // Act
        $this->account->applyTransactionEntry($entry1)
                     ->applyTransactionEntry($entry2)
                     ->applyTransactionEntry($entry3);

        // Assert
        $this->assertSame(750, $this->account->getBalance());
    }

    public function testApplyTransactionEntryWithInsufficientFunds(): void
    {
        // Arrange
        $entry = new TransactionEntry($this->transaction, $this->account, Type::CREDIT, 1500, 'USD');

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Insufficient funds in account "Test Account". Current balance: 1000 USD, attempted debit: 1500 USD'
        );

        // Act
        $this->account->applyTransactionEntry($entry);
    }

    public function testApplyTransactionEntryWithWrongAccount(): void
    {
        // Arrange
        $otherAccount = new Account($this->user, 'Other Account', 'USD');
        $entry = new TransactionEntry($this->transaction, $otherAccount, Type::CREDIT, 100, 'USD');

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Transaction entry does not belong to this account');

        // Act
        $this->account->applyTransactionEntry($entry);
    }

    public function testApplyTransactionEntryWithWrongCurrency(): void
    {
        // Arrange
        $entry = new TransactionEntry($this->transaction, $this->account, Type::CREDIT, 100, 'EUR');

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Transaction entry currency does not match account currency');

        // Act
        $this->account->applyTransactionEntry($entry);
    }
}
