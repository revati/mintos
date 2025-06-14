<?php

namespace App\Domain\Accounting;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TransactionEntry>
 */
class TransactionEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransactionEntry::class);
    }

    /**
     * Find transactions for an account with pagination
     * 
     * @param Account $account The account to find transactions for
     * @param int $limit Maximum number of transactions to return
     * @param int $offset Number of transactions to skip
     * @return array<Transaction> Array of transactions
     */
    public function findByAccount(Account $account, int $limit = 10, int $offset = 0): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.account = :account')
            ->setParameter('account', $account->getId())
            ->orderBy('e.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count total number of transactions for an account
     * 
     * @param Account $account The account to count transactions for
     * @return int Total number of transactions
     */
    public function countByAccount(Account $account): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(DISTINCT e.transaction)')
            ->where('e.account = :account')
            ->setParameter('account', $account->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }
}
