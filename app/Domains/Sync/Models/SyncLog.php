<?php

namespace App\Domains\Sync\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    use HasUuid;

    protected $fillable = [
        'table_name', 'record_id', 'direction', 'status',
        'payload', 'error_message', 'attempt_count', 'last_attempt_at',
    ];

    protected function casts(): array
    {
        return [
            'payload'         => 'array',
            'attempt_count'   => 'integer',
            'last_attempt_at' => 'datetime',
        ];
    }

    public function scopePending($query)  { return $query->where('status', 'pending'); }
    public function scopeFailed($query)   { return $query->where('status', 'failed'); }
}
