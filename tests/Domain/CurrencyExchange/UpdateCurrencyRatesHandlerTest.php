<?php

namespace App\Tests\Domain\CurrencyExchange;

use App\Domain\CurrencyExchange\ExchangeRateHostClient;
use App\Domain\CurrencyExchange\Rate;
use App\Domain\CurrencyExchange\RateRepository;
use App\Domain\CurrencyExchange\UpdateCurrencyRatesHandler;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UpdateCurrencyRatesHandlerTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private ExchangeRateHostClient $client;
    private RateRepository $repository;
    private UpdateCurrencyRatesHandler $handler;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->client = self::getContainer()->get(ExchangeRateHostClient::class);
        $this->repository = self::getContainer()->get(RateRepository::class);
        $this->handler = new UpdateCurrencyRatesHandler(
            $this->client,
            $this->repository,
            'USD',
            '+1 hour'
        );

        // Create schema
        $schemaTool = new SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool->createSchema($metadata);
    }

    protected function tearDown(): void
    {
        $this->em->clear();
    }

    public function testHandleUpdatesRates(): void
    {
        $this->client = $this->createMock(ExchangeRateHostClient::class);
        $this->client->method('getRates')->willReturn([
            'EUR' => 5,
            'DKK' => 9,
        ]);
        $this->handler = new UpdateCurrencyRatesHandler(
            $this->client,
            $this->repository,
            'USD',
            '+1 hour'
        );

        // Arrange
        $expiresAt = new DateTime('+1 hour');

        // Act
        $this->handler->handle();

        // Assert
        $rates = $this->repository->findAll();
        $this->assertCount(2, $rates);

        $eurRate = $this->repository->findOneBy(['to' => 'EUR']);
        $this->assertNotNull($eurRate);
        $this->assertEquals('USD', $eurRate->getFrom());
        $this->assertEquals(5, $eurRate->getRate());

        $dkkRate = $this->repository->findOneBy(['to' => 'DKK']);
        $this->assertNotNull($dkkRate);
        $this->assertEquals('USD', $dkkRate->getFrom());
        $this->assertEquals(9, $dkkRate->getRate());
    }

    public function testHandleWithEmptyRates(): void
    {
        // Arrange
        $this->client = $this->createMock(ExchangeRateHostClient::class);
        $this->client->method('getRates')->willReturn([]);
        $this->handler = new UpdateCurrencyRatesHandler(
            $this->client,
            $this->repository,
            'USD',
            '+1 hour'
        );

        // Act
        $this->handler->handle();

        // Assert
        $rates = $this->repository->findAll();
        $this->assertEmpty($rates);
    }
} 