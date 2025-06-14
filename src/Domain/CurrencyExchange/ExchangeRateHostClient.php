<?php

namespace App\Domain\CurrencyExchange;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ExchangeRateHostClient 
{

    public function __construct(
        private readonly string $currencies,
        private readonly string $baseCurrency,
        private readonly string $baseUrl,
        private readonly string $apiKey,
        private readonly HttpClientInterface $httpClient
    ) {
    }

    /**
     * Get exchange rates for a specific base currency
     * 
     * @return array<string, float> Array of currency codes and their rates
     * @throws \Exception If the API request fails
     */
    public function getRates(): array
    {
        var_dump($this->currencies);
        $response = $this->httpClient->request('GET', $this->baseUrl, [
            'query' => [
                'access_key' => $this->apiKey,
                'currencies' => $this->baseCurrency . ',' . $this->currencies
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Failed to fetch exchange rates');
        }

        $data = $response->toArray();
        
        if (!isset($data['success']) || $data['success'] !== true) {
            throw new \Exception('API request was not successful');
        }

        if (!isset($data['quotes']) || !is_array($data['quotes'])) {
            throw new \Exception('Invalid response format from exchange rate API');
        }

        // Transform quotes format from "USDAUD" => 1.542375 to "AUD" => 1.542375
        $rates = [];
        foreach ($data['quotes'] as $pair => $rate) {
            $targetCurrency = substr($pair, 3); // Remove the base currency prefix (e.g., "USD" from "USDAUD")
            $rates[$targetCurrency] = $rate;
        }

        return $rates;
    }
}