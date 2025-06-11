<?php

namespace App\Domain\Accounting;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * Stores a transaction and its entries
     * 
     * @throws \Doctrine\ORM\ORMException
     */
    public function storeTransaction(Transaction $transaction): void
    {
        $em = $this->getEntityManager();
        $em->persist($transaction);
            
        // Persist all accounts that were modified
        foreach ($transaction->getEntries() as $entry) {
            $em->persist($entry->getAccount());
        }

        $em->flush();
    }
}
