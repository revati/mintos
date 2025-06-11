<?php

namespace App\Tests\Domain;

use App\Domain\Accounting;
use App\Domain\Access\User;
use App\Domain\Accounting\Account;
use App\Domain\Accounting\AccountRepository;
use App\Domain\Accounting\Transaction;
use App\Domain\Accounting\TransferMoney;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AccountingTest extends KernelTestCase
{
    private Accounting $accounting;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->accounting = self::getContainer()->get(Accounting::class);

        // Create schema
        $schemaTool = new SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool->createSchema($metadata);
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        $this->em->clear();
    }

    public function testListAccounts(): void
    {
        // Arrange
        $user = new User('test@example.com');
        $this->em->persist($user);
        $this->em->flush();

        $accounts = [
            new Account($user, 'EUR Account', 'EUR'),
            new Account($user, 'USD Account', 'USD')
        ];

        foreach ($accounts as $account) {
            $this->em->persist($account);
        }
        $this->em->flush();

        // Act
        $result = $this->accounting->listAccounts($user);

        // Assert
        $this->assertCount(2, $result);
        $this->assertSame('EUR', $result[0]->getCurrency());
        $this->assertSame('USD', $result[1]->getCurrency());
    }

    public function testTransferMoney(): void
    {
        // Arrange
        $user = new User('test@example.com');
        $this->em->persist($user);

        $fromAccount = new Account($user, 'Source Account', 'USD', 1000);
        $toAccount = new Account($user, 'Destination Account', 'USD');
        $this->em->persist($fromAccount);
        $this->em->persist($toAccount);
        $this->em->flush();

        $amount = 100;
        $currency = 'USD';
        $description = 'Test transfer';

        // Act
        $transaction = $this->accounting->transferMoney($fromAccount, $toAccount, $amount, $currency, $description);

        // Assert
        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertSame($description, $transaction->getDescription());
        $this->assertSame($amount, $transaction->getAmount());
        $this->assertSame($currency, $transaction->getCurrency());
        $this->assertCount(2, $transaction->getEntries());

        // Verify account balances
        $this->em->refresh($fromAccount);
        $this->em->refresh($toAccount);
        $this->assertSame(900, $fromAccount->getBalance());
        $this->assertSame(100, $toAccount->getBalance());
    }

    // public function testListAccountsReturnsEmptyArrayWhenNoAccounts(): void
    // {
    //     // Arrange
    //     $user = new User('test@example.com');
    //     $this->em->persist($user);
    //     $this->em->flush();

    //     // Act
    //     $result = $this->accounting->listAccounts($user);

    //     // Assert
    //     $this->assertEmpty($result);
    // }
} 