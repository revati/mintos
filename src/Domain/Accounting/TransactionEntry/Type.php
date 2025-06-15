<?php

namespace App\Domain\Accounting\TransactionEntry;

enum Type: string
{
    case DEBIT = 'DEBIT';
    case CREDIT = 'CREDIT';
} 