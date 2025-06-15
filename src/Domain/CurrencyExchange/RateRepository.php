<?php

namespace App\Domain\CurrencyExchange;

use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RateRepository extends ServiceEntityRepository
{
    private readonly string $baseCurrency;

    public function __construct(ManagerRegistry $registry, string $baseCurrency)
    {
        parent::__construct($registry, Rate::class);
        $this->baseCurrency = $baseCurrency;
    }

    public function findRate(string $from, string $to): ?float
    {
        // If currencies are the same, return 1
        if ($from === $to) {
            return 1.0;
        }

        // If one of the currencies is USD, we can get the rate directly
        if ($from === $this->baseCurrency) {
            return $this->findDirectRate($to);
        }

        if ($to === $this->baseCurrency) {
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
            ->setParameter('from', $this->baseCurrency)
            ->setParameter('to', $currency)
            ->setParameter('now', new DateTime())
            ->orderBy('r.expiresAt', 'DESC')
            ->setMaxResults(1);
        
        $rate = $qb->getQuery()->getOneOrNullResult();
        return $rate ? $rate['rate'] : null;
    }

    public function save(Rate $rate): void
    {
        $em = $this->getEntityManager();

        // Ensure we only store rates from USD to other currencies
        if ($rate->getFrom() !== $this->baseCurrency) {
            throw new \InvalidArgumentException('Only rates from USD to other currencies can be stored');
        }

        $em->persist($rate);
        $em->flush();
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
