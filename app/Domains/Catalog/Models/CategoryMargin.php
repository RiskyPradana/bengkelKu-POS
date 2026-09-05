<?php

namespace App\Domains\Catalog\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class CategoryMargin extends Model
{
    use HasUuid;

    protected $fillable = [
        'category',
        'margin_percent',
    ];

    protected function casts(): array
    {
        return [
            'margin_percent' => 'decimal:2',
        ];
    }
}
