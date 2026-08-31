<?php

namespace App\Domains\POS\Enums;

enum InvoiceStatus: string
{
    case Draft = 'Draft';
    case Unpaid = 'Unpaid';
    case Paid = 'Paid';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Unpaid => 'Unpaid',
            self::Paid => 'Paid',
        };
    }
}
