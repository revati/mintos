<?php

namespace App\Domain\Accounting;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use App\Domain\Access\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ORM\Entity(repositoryClass: AccountRepository::class)]
#[ORM\Table(name: '`accounts`')]
class Account
{
    #[ORM\Id, ORM\Column(type: "uuid")]
    #[Groups(['account:read'])]
    private Uuid $id;

    #[ORM\Column(length: 100)]
    #[Groups(['account:read'])]
    private string $name;

    #[ORM\Column(type: "bigint")]
    #[Groups(['account:read'])]
    private int $balance;

    #[ORM\Column(length: 3)]
    #[Groups(['account:read'])]
    private string $currency;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    public function __construct(User $user, string $name, string $currency, int $initialBalance = 0)
    {
        $this->id       = Uuid::v4();
        $this->user     = $user;
        $this->name     = $name;
        $this->currency = $currency;
        $this->balance  = $initialBalance;
    }

    public function getId(): Uuid { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getBalance(): int { return $this->balance; }
    public function getCurrency(): string { return $this->currency; }
    #[Groups(['account:read'])]
    #[SerializedName('userId')]
    public function getUserId(): string { return $this->getUser()->getId(); }

    public function getUser(): User { return $this->user; }

    public function applyTransactionEntry(TransactionEntry $entry): static
    {
        $this->validateTransactionEntry($entry);
        $this->updateBalance($entry);
        return $this;
    }

    private function validateTransactionEntry(TransactionEntry $entry): void
    {
        if ($entry->getAccount() !== $this) {
            throw new \InvalidArgumentException('Transaction entry does not belong to this account');
        }

        if ($entry->getCurrency() !== $this->currency) {
            throw new \InvalidArgumentException('Transaction entry currency does not match account currency');
        }
    }

    private function updateBalance(TransactionEntry $entry): static
    {
        $amount = $entry->getRelativeAmount();
        $newBalacne = $this->balance + $amount;

        if ($newBalacne < 0) {
            throw new \InvalidArgumentException(sprintf(
                'Insufficient funds in account "%s". Current balance: %d %s, attempted debit: %d %s',
                $this->name,
                $this->balance,
                $this->currency,
                $entry->getAbsoluteAmount(),
                $this->currency
            ));
        }

        $this->balance = $newBalacne;

        return $this;
    }
}
