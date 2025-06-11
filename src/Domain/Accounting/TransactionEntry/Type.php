<?php

namespace App\Domain\Accounting\TransactionEntry;

enum Type: string
{
    case DEBIT = 'debit';
    case CREDIT = 'credit';
} 