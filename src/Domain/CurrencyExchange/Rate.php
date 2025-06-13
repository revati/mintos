<?php

namespace App\Domain\CurrencyExchange;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: RateRepository::class)]
#[ORM\Table(name: '`rates`')]
class Rate
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid")]
    private Uuid $id;

    #[ORM\Column(name: '`from_currency`', length: 3)]
    private string $from;

    #[ORM\Column(name: '`to_currency`', length: 3)]
    private string $to;

    #[ORM\Column(type: "decimal", precision: 10, scale: 6)]
    private float $rate;

    #[ORM\Column(type: "datetime")]
    private DateTime $expiresAt;

    public function __construct(string $from, string $to, float $rate, DateTime $expiresAt)
    {
        $this->id = Uuid::v4();
        $this->from = $from;
        $this->to = $to;
        $this->rate = $rate;
        $this->expiresAt = $expiresAt;
    }

    public function getId(): Uuid { return $this->id; }
    public function getFrom(): string { return $this->from; }
    public function getTo(): string { return $this->to; }
    public function getRate(): float { return $this->rate; }
    public function getExpiresAt(): DateTime {  return $this->expiresAt; }
}
