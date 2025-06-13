<?php 

namespace App\Domain;

use App\Domain\CurrencyExchange\RateRepository;

class CurrencyExchange 
{
    public function __construct(
        private readonly RateRepository $rateRepository
    ) {
    }

    /**
     * Convert an amount from one currency to another
     * 
     * @param int $amount Amount to convert
     * @param string $fromCurrency Source currency code
     * @param string $toCurrency Target currency code
     * @return int Converted amount
     * @throws \Exception If the conversion fails
     */
    public function convert(int $amount, string $fromCurrency, string $toCurrency): int
    {
        $rate = $this->rateRepository->findRate($fromCurrency, $toCurrency);
        
        if (!$rate) {
            throw new \InvalidArgumentException(sprintf('Exchange rate not available for %s->%s route', $fromCurrency, $toCurrency));
        }

        return intval($amount * $rate);
    }
}