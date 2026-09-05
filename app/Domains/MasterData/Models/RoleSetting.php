<?php

namespace App\Domains\MasterData\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Definisi role & hak akses menu, bisa diatur lewat halaman
 * Pengaturan > Role & Hak Akses (/pengaturan/role).
 *
 * @see \App\Domains\MasterData\Services\RoleRegistry
 */
class RoleSetting extends Model
{
    protected $table = 'role_settings';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'label',
        'description',
        'color',
        'level',
        'access',
        'is_default',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'access'     => 'array',
            'level'      => 'integer',
            'is_default' => 'boolean',
            'is_system'  => 'boolean',
        ];
    }
}
