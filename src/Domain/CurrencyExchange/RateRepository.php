<?php

namespace App\Domain\CurrencyExchange;

use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RateRepository extends ServiceEntityRepository
{
    private const BASE_CURRENCY = 'USD';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rate::class);
    }

    public function findRate(string $from, string $to): ?float
    {
        // If currencies are the same, return 1
        if ($from === $to) {
            return 1.0;
        }

        // If one of the currencies is USD, we can get the rate directly
        if ($from === self::BASE_CURRENCY) {
            return $this->findDirectRate($to);
        }

        if ($to === self::BASE_CURRENCY) {
            $rate = $this->findDirectRate($from);
            return $rate ? 1 / $rate : null;
        }

        $fromToUsd = $this->findDirectRate($from);

        if (!$fromToUsd) {
            return null;
        }

        $usdToTo = $this->findDirectRate($to);

        if (!$usdToTo) {
            return null;
        }

        return (1 / $fromToUsd) * $usdToTo;
    }

    private function findDirectRate(string $currency): ?float
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r.rate')
            ->where('r.from = :from')
            ->andWhere('r.to = :to')
            ->andWhere('r.expiresAt > :now')
            ->setParameter('from', self::BASE_CURRENCY)
            ->setParameter('to', $currency)
            ->setParameter('now', new DateTime())
            ->orderBy('r.expiresAt', 'DESC')
            ->setMaxResults(1);
        
        $rate = $qb->getQuery()->getOneOrNullResult();
        return $rate ? $rate['rate'] : null;
    }

    public function save(Rate $rate): void
    {
        // Ensure we only store rates from USD to other currencies
        if ($rate->getFrom() !== self::BASE_CURRENCY) {
            throw new \InvalidArgumentException('Only rates from USD to other currencies can be stored');
        }

        $this->_em->persist($rate);
        $this->_em->flush();
    }

    public function removeExpiredRates(): void
    {
        $this->createQueryBuilder('r')
            ->delete()
            ->where('r.expiresAt <= :now')
            ->setParameter('now', new DateTime())
            ->getQuery()
            ->execute();
    }
}
