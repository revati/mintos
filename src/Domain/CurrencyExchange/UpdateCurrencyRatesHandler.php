<?php

namespace App\Domain\CurrencyExchange;

use App\Domain\CurrencyExchange\ExchangeRateHostClient;
use App\Domain\CurrencyExchange\Rate;
use App\Domain\CurrencyExchange\RateRepository;
use DateTime;

class UpdateCurrencyRatesHandler
{
    public function __construct(
        private readonly ExchangeRateHostClient $client,
        private readonly RateRepository $repository,
        private readonly string $baseCurrency,
        private readonly string $expirationDelay,

    ) {
    }

    public function handle(): void
    {
        $rates = $this->client->getRates();
        $expiresAt = new DateTime($this->expirationDelay);

        foreach ($rates as $currency => $rate) {
            $rateEntity = new Rate($this->baseCurrency, $currency, $rate, $expiresAt);
            $this->repository->save($rateEntity);
        }

        $this->repository->removeExpiredRates();
    }
} 