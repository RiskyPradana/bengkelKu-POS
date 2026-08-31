<?php

namespace App\Domains\POS\Services;

use App\Domains\POS\Enums\InvoiceStatus;
use App\Domains\POS\Models\Invoice;
use App\Domains\POS\Models\Payment;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Domains\WorkOrder\Services\WorkOrderService;
use Illuminate\Support\Facades\DB;

class POSService
{
    public function __construct(private readonly WorkOrderService $workOrderService)
    {
    }

    public function buildInvoiceNumber(): string
    {
        return 'INV-' . now()->format('Ymd-His');
    }

    public function createInvoiceFromWorkOrder(WorkOrder $workOrder, array $overrides = []): Invoice
    {
        return DB::transaction(function () use ($workOrder, $overrides): Invoice {
            $snapshot = $workOrder->price_snapshot ?: $this->workOrderService->buildPriceSnapshot($workOrder);
            $subtotal = (float) ($snapshot['total_amount'] ?? 0);
            $discount = (float) ($overrides['discount'] ?? 0);
            $tax = (float) ($overrides['tax'] ?? 0);
            $grandTotal = max(0, $subtotal - $discount + $tax);

            return Invoice::create([
                'branch_id' => $workOrder->branch_id,
                'work_order_id' => $workOrder->id,
                'invoice_number' => $this->buildInvoiceNumber(),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'grand_total' => $grandTotal,
                'status' => InvoiceStatus::Unpaid,
            ]);
        });
    }

    public function recordPayment(Invoice $invoice, array $data): Payment
    {
        return DB::transaction(function () use ($invoice, $data): Payment {
            $payment = $invoice->payments()->create([
                'method' => $data['method'],
                'amount' => $data['amount'],
                'reference_number' => $data['reference_number'] ?? null,
                'paid_at' => $data['paid_at'] ?? now(),
            ]);

            if ((float) $invoice->outstanding_amount <= 0) {
                $invoice->forceFill([
                    'status' => InvoiceStatus::Paid,
                ])->save();

                $this->workOrderService->markPaid($invoice->workOrder);
            }

            return $payment;
        });
    }
}
