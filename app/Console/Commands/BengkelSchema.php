<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class BengkelSchema extends Command
{
    protected $signature = 'bengkel:schema {table? : Nama tabel tertentu}';

    protected $description = 'Menampilkan kolom asli tiap tabel penting, untuk mencocokkan kode dengan database';

    public function handle(): int
    {
        $tables = $this->argument('table')
            ? [$this->argument('table')]
            : [
                'invoices', 'invoice_items', 'work_orders', 'products',
                'customers', 'vehicles', 'branches', 'users',
                'branch_stocks', 'stock_movements', 'mechanic_commissions',
                'service_reminders', 'whatsapp_logs',
            ];

        foreach ($tables as $t) {
            $this->newLine();

            if (! Schema::hasTable($t)) {
                $this->line('[TIDAK ADA] ' . $t);
                continue;
            }

            $cols = Schema::getColumnListing($t);
            $this->line('[' . $t . '] ' . count($cols) . ' kolom');
            $this->line('  ' . implode(', ', $cols));
        }

        $this->newLine();
        $this->line('Salin dan kirimkan hasil di atas supaya kode bisa dicocokkan dengan database.');
        $this->newLine();

        return self::SUCCESS;
    }
}
