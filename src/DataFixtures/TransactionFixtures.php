<?php

namespace App\DataFixtures;

use App\Domain\Accounting\Account;
use App\Domain\Accounting\Transaction;
use App\Domain\Accounting\TransactionEntry;
use App\Domain\Accounting\TransactionEntry\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TransactionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $a1 = $this->getReference('acc-1', Account::class);
        $a2 = $this->getReference('acc-2', Account::class);

        $t1 = new Transaction('Transfer');
        $t1->addEntry(new TransactionEntry($t1, $a1, Type::DEBIT, 100, 'USD'));
        $t1->addEntry(new TransactionEntry($t1, $a2, Type::CREDIT, 100, 'USD'));

        $manager->persist($t1);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AccountFixtures::class,
        ];
    }
}
