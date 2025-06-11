<?php

namespace App\Domain\Accounting;

use App\Domain\Access\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Account>
 */
class AccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }

    /**
     * @return Account[] Returns an array of Account objects
     */
    public function listForUser(User $user): array
    {
        return $this->findBy(
            ['user' => $user],
            ['name' => 'ASC']
        );
    }
}
