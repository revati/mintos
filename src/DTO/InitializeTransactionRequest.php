<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class InitializeTransactionRequest
{
    #[Assert\NotBlank]
    #[Assert\Uuid]
    public string $debitAccount;

    #[Assert\NotBlank]
    #[Assert\Uuid]
    public string $creditAccount;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\Positive]
    public int $amount;

    #[Assert\NotBlank]
    #[Assert\Length(exactly: 3)]
    public string $currency;

    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 255)]
    public string $description;
} 