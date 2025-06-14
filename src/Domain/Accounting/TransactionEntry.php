<?php

namespace App\Domain\Accounting;

use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Domain\Accounting\TransactionEntry;
use App\Domain\Accounting\TransactionEntry\Type;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ORM\Entity]
#[ORM\Table(name: '`transaction_entries`')]
class TransactionEntry
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid")]
    #[Groups(['transactionEntry:read'])]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: Transaction::class, inversedBy: "entries")]
    #[ORM\JoinColumn(nullable: false)]
    private Transaction $transaction;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Account $account;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Account $counterparty;

    #[ORM\Column(type: "string", enumType: Type::class)]
    #[Groups(['transactionEntry:read'])]
    private Type $type;

    #[ORM\Column(type: "string")]
    #[Groups(['transactionEntry:read'])]
    private string $description;

    #[ORM\Column(type: "bigint")]
    #[Groups(['transactionEntry:read'])]
    private int $amount;

    #[ORM\Column(length: 3)]
    #[Groups(['transactionEntry:read'])]
    private string $currency;

    #[ORM\Column]
    #[Groups(['transactionEntry:read'])]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        Transaction $tx, 
        Account $account, 
        Account $counterparty, 
        Type $type, 
        int $amount, 
        string $currency, 
        ?string $description = null
    ) {
        $this->id           = Uuid::v4();
        $this->transaction  = $tx;
        $this->account      = $account;
        $this->counterparty = $counterparty;
        $this->type         = $type;
        $this->description  = $description ?? $tx->getDescription();
        $this->amount       = $amount;
        $this->currency     = $currency;
        $this->createdAt    = new \DateTimeImmutable();
        $tx->addEntry($this);
    }

    public function getId(): Uuid { return $this->id; }
    public function getTransaction(): Transaction { return $this->transaction; }
    public function getAccount(): Account { return $this->account; }
    public function getCounterparty(): Account { return $this->counterparty; }
    public function getType(): Type { return $this->type; }
    public function getDescription(): string { return $this->description; }
    public function getAbsoluteAmount(): int { return $this->amount; }
    public function getCurrency(): string { return $this->currency; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    #[Groups(['transactionEntry:read'])]
    #[SerializedName('transactionId')]
    public function getTransactionId(): Uuid { return $this->transaction->getId(); }

    #[Groups(['transactionEntry:read'])]
    #[SerializedName('accountId')]
    public function getAccountId(): Uuid { return $this->account->getId(); }

    #[Groups(['transactionEntry:read'])]
    #[SerializedName('counterpartyId')]
    public function getCounterpartyId(): Uuid { return $this->counterparty->getId(); }

    public function getRelativeAmount(): int
    {
        return match($this->type) {
            Type::DEBIT => -$this->amount,
            Type::CREDIT => $this->amount,
            default => throw new \InvalidArgumentException('Invalid transaction type. Must be either "debit" or "credit".')
        };
    }
}
