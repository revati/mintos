<?php

namespace App\Command;

use App\Domain\CurrencyExchange\UpdateCurrencyRatesHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Scheduler\Attribute\AsScheduledTask;

#[AsCommand(
    name: 'update-rates',
    description: 'Update currency exchange rates from ExchangeRateHost'
)]
#[AsScheduledTask(
    expression: '* * * * *',
    description: 'Update currency exchange rates every minute'
)]
class UpdateCurrencyRatesCommand extends Command
{
    public function __construct(
        private readonly UpdateCurrencyRatesHandler $handler
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Updating currency exchange rates');

        try {
            $this->handler->handle();
            
            $io->success('Currency exchange rates updated successfully');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to update currency exchange rates: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 