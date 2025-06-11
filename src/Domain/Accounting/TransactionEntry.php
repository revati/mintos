<?php

namespace App\Domain\Accounting;

use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Domain\Accounting\TransactionEntry;
use App\Domain\Accounting\TransactionEntry\Type;

#[ORM\Entity]
#[ORM\Table(name: '`transaction_entries`')]
class TransactionEntry
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid")]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: Transaction::class, inversedBy: "entries")]
    #[ORM\JoinColumn(nullable: false)]
    private Transaction $transaction;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Account $account;

    #[ORM\Column(type: "string", enumType: Type::class)]
    private Type $type;

    #[ORM\Column(type: "string")]
    private string $description;

    #[ORM\Column(type: "bigint")]
    private int $amount;

    #[ORM\Column(length: 3)]
    private string $currency;

    public function __construct(Transaction $tx, Account $account, Type $type, int $amount, string $currency, ?string $description = null)
    {
        $this->id          = Uuid::v4();
        $this->transaction = $tx;
        $this->account     = $account;
        $this->type        = $type;
        $this->description = $description ?? $tx->getDescription();
        $this->amount      = $amount;
        $this->currency    = $currency;
        $tx->addEntry($this);
    }

    public function getId(): Uuid { return $this->id; }
    public function getTransaction(): Transaction { return $this->transaction; }
    public function getAccount(): Account { return $this->account; }
    public function getType(): Type { return $this->type; }
    public function getDescription(): string { return $this->description; }
    public function getAbsoluteAmount(): int { return $this->amount; }
    public function getCurrency(): string { return $this->currency; }

    public function getRelativeAmount(): int
    {
        return match($this->type) {
            Type::DEBIT => -$this->amount,
            Type::CREDIT => $this->amount,
            default => throw new \InvalidArgumentException('Invalid transaction type. Must be either "debit" or "credit".')
        };
    }
}
