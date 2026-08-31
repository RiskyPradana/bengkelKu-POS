<?php

namespace App\Domains\POS\Enums;

enum PaymentMethod: string
{
    case Cash = 'Cash';
    case QRIS = 'QRIS';
    case Transfer = 'Transfer';
    case Debit = 'Debit';
}
