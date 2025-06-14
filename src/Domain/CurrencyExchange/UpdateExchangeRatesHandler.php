<?php

namespace App\Domain\CurrencyExchange;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

#[AsMessageHandler]
class UpdateExchangeRatesHandler
{
    public function __construct(
        private readonly Application $application
    ) {
    }

    public function __invoke(UpdateExchangeRatesMessage $message): void
    {
        $input = new ArrayInput(['command' => 'app:update-exchange-rates']);
        $output = new BufferedOutput();
        
        $this->application->run($input, $output);
    }
} 