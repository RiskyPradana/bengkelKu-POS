<?php

namespace App\Domains\MasterData\Services;

class MasterDataService
{
    public function normalizeBranchCode(string $code): string
    {
        return strtoupper(trim($code));
    }
}
