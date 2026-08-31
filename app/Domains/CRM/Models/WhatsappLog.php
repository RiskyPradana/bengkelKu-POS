<?php

namespace App\Domains\CRM\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class WhatsappLog extends Model
{
    use HasUuid;

    protected $fillable = [
        'customer_id', 'phone_number', 'type', 'message',
        'status', 'reference_id', 'error_message', 'sent_at',
    ];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }
}
