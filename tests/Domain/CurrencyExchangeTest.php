<?php

namespace App\Tests\Domain;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use App\Domain\CurrencyExchange;
use App\Domain\CurrencyExchange\Rate;
use DateTime;

class CurrencyExchangeTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private CurrencyExchange $exchange;

    protected function setUp(): void
    {
        self::bootKernel();
        
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->exchange = self::getContainer()->get(CurrencyExchange::class);

        // Create schema
        $schemaTool = new SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool->createSchema($metadata);
    }

    public function testConvert(): void
    {
        $expiresAt = (new DateTime())->modify('+5 minutes');
        $this->em->persist(new Rate('USD', 'EUR', 1.5, $expiresAt));
        $this->em->persist(new Rate('USD', 'GBP', 2, $expiresAt));
        $this->em->flush();

        $this->assertEquals(150, $this->exchange->convert(100, 'USD', 'EUR'));
        $this->assertEquals(66, $this->exchange->convert(100, 'EUR', 'USD'));
        $this->assertEquals(133, $this->exchange->convert(100, 'EUR', 'GBP'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Exchange rate not available for AED->GBP route');

        $this->exchange->convert(100, 'AED', 'GBP');
    }

    public function testConvertExpired(): void
    {
        $expiresAt = (new DateTime())->modify('+5 minutes');
        $this->em->persist(new Rate('USD', 'EUR', 1.5, new DateTime()));
        $this->em->persist(new Rate('USD', 'GBP', 2, new DateTime()));
        $this->em->flush();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Exchange rate not available for USD->EUR route');

        $this->assertEquals(150, $this->exchange->convert(100, 'USD', 'EUR'));
    }
} 