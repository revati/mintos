<?php

namespace App\Domain\CurrencyExchange;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DateTime;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'app:update-exchange-rates',
    description: 'Updates exchange rates from ExchangeRateHost API'
)]
class UpdateExchangeRatesCommand extends Command
{
    public function __construct(
        private readonly ExchangeRateHostClient $client,
        private readonly RateRepository $repository,
        private readonly string $currencyRateTTL,
        private readonly string $baseCurrency
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $output->writeln('Fetching latest exchange rates...');
            
            $rates = $this->client->getRates();
            $expiresAt = (new DateTime())->modify($this->currencyRateTTL);
            
            foreach ($rates as $currency => $rate) {
                $this->repository->save(new Rate($this->baseCurrency, $currency, $rate, $expiresAt));
                $output->writeln(sprintf('Saved rate for %s/%s: %f', $this->baseCurrency, $currency, $rate));
            }

            $output->writeln('Exchange rates updated successfully');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Error updating exchange rates: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }
} 