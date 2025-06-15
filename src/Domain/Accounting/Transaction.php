<?php

namespace App\Domain\Accounting;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\Table(name: '`transactions`')]
class Transaction
{
    #[ORM\Id, ORM\Column(type: "uuid")]
    #[Groups(['transaction:read'])]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    #[Groups(['transaction:read'])]
    private string $description;

    #[ORM\Column(type: "bigint")]
    #[Groups(['transaction:read'])]
    private int $amount;

    #[ORM\Column(length: 3)]
    #[Groups(['transaction:read'])]
    private string $currency;

    #[ORM\Column]
    #[Groups(['transaction:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(mappedBy: "transaction", targetEntity: TransactionEntry::class, cascade: ["persist"])]
    #[Groups(['transaction:read'])]
    private $entries;

    public function __construct(string $description, int $amount, string $currency)
    {
        $this->id         = Uuid::v4();
        $this->description= $description;
        $this->amount     = $amount;
        $this->currency   = $currency;
        $this->createdAt  = new \DateTimeImmutable();
        $this->entries    = new ArrayCollection();
    }

    public function getId(): Uuid { return $this->id; }
    public function getDescription(): string { return $this->description; }
    public function getAmount(): int { return $this->amount; }
    public function getCurrency(): string { return $this->currency; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getEntries() { return $this->entries; }

    public function addEntry(TransactionEntry $e): static
    {
        $this->entries->add($e);
        return $this;
    }
}
