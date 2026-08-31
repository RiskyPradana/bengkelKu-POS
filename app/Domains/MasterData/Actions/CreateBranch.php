<?php

namespace App\Domains\MasterData\Actions;

use App\Domains\MasterData\Models\Branch;

class CreateBranch
{
    public function handle(array $data): Branch
    {
        return Branch::create($data);
    }
}
