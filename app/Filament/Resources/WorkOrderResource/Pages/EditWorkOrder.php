<?php

namespace App\Filament\Resources\WorkOrderResource\Pages;

use App\Domains\POS\Enums\PaymentMethod;
use App\Domains\POS\Models\Invoice;
use App\Domains\POS\Services\POSService;
use App\Domains\WorkOrder\Enums\WorkOrderStatus;
use App\Filament\Resources\WorkOrderResource;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditWorkOrder extends EditRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateInvoice')
                ->label('Buat Invoice')
                ->icon('heroicon-o-receipt-percent')
                ->visible(fn (): bool => $this->record->invoice()->doesntExist())
                ->action(function (POSService $posService): void {
                    $invoice = $posService->createInvoiceFromWorkOrder($this->record);

                    Notification::make()
                        ->title('Invoice berhasil dibuat')
                        ->body($invoice->invoice_number)
                        ->success()
                        ->send();

                    $this->redirect(WorkOrderResource::getUrl('edit', ['record' => $this->record]));
                }),
            Action::make('recordPayment')
                ->label('Catat Pembayaran')
                ->icon('heroicon-o-banknotes')
                ->visible(fn (): bool => $this->record->invoice()->exists())
                ->form([
                    Placeholder::make('summary')
                        ->content(fn (): string => $this->paymentSummary()),
                    Select::make('method')
                        ->options(collect(PaymentMethod::cases())->mapWithKeys(fn (PaymentMethod $method) => [
                            $method->value => $method->label(),
                        ]))
                        ->required(),
                    TextInput::make('amount')
                        ->numeric()
                        ->prefix('Rp')
                        ->required(),
                    TextInput::make('reference_number')
                        ->maxLength(255),
                    DateTimePicker::make('paid_at')
                        ->default(now()),
                ])
                ->action(function (array $data, POSService $posService): void {
                    $invoice = $this->record->invoice;

                    if (! $invoice instanceof Invoice) {
                        Notification::make()
                            ->title('Invoice belum tersedia')
                            ->danger()
                            ->send();

                        return;
                    }

                    $payment = $posService->recordPayment($invoice, $data);

                    Notification::make()
                        ->title('Pembayaran tersimpan')
                        ->body('Nominal: Rp ' . number_format((float) $payment->amount, 0, ',', '.'))
                        ->success()
                        ->send();

                    $this->redirect(WorkOrderResource::getUrl('edit', ['record' => $this->record]));
                }),
        ];
    }

    protected function paymentSummary(): string
    {
        $invoice = $this->record->invoice;

        if (! $invoice instanceof Invoice) {
            return 'Invoice belum dibuat.';
        }

        $paid = (float) $invoice->paid_amount;
        $outstanding = (float) $invoice->outstanding_amount;

        return implode(PHP_EOL, [
            'Invoice: ' . $invoice->invoice_number,
            'Subtotal: Rp ' . number_format((float) $invoice->subtotal, 0, ',', '.'),
            'Diskon: Rp ' . number_format((float) $invoice->discount, 0, ',', '.'),
            'PPN: Rp ' . number_format((float) $invoice->tax, 0, ',', '.'),
            'Total: Rp ' . number_format((float) $invoice->grand_total, 0, ',', '.'),
            'Sudah Dibayar: Rp ' . number_format($paid, 0, ',', '.'),
            'Sisa Tagihan: Rp ' . number_format($outstanding, 0, ',', '.'),
            'Status: ' . $invoice->status->value,
        ]);
    }
}
