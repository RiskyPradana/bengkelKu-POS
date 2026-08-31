<?php

namespace Tests\Feature;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ServiceItem;
use App\Domains\CustomerVehicle\Models\Customer;
use App\Domains\CustomerVehicle\Models\Vehicle;
use App\Domains\MasterData\Models\Branch;
use App\Domains\POS\Enums\InvoiceStatus;
use App\Domains\POS\Enums\PaymentMethod;
use App\Domains\POS\Models\Invoice;
use App\Domains\POS\Models\Payment;
use App\Domains\WorkOrder\Enums\WorkOrderStatus;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Livewire\Pos\Cashier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CashierTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_page_renders(): void
    {
        $response = $this->get('/kasir');

        $response->assertOk();
        $response->assertSee('Kasir POS');
    }

    public function test_cashier_can_select_work_order_and_add_items(): void
    {
        $workOrder = $this->makeCompletedWorkOrder();
        $product = Product::create([
            'sku' => 'SKU-001',
            'barcode' => '1234567890123',
            'name' => 'Oli Mesin 10W-40',
            'cost_price' => 100000,
            'sell_price' => 145000,
            'is_active' => true,
        ]);

        $serviceItem = ServiceItem::create([
            'code' => 'SRV-001',
            'name' => 'Ganti Oli',
            'price' => 65000,
            'is_active' => true,
        ]);

        Livewire::test(Cashier::class)
            ->call('selectWorkOrder', $workOrder->id)
            ->call('addCatalogItem', 'product', $product->id)
            ->call('addCatalogItem', 'service', $serviceItem->id)
            ->assertSet('selectedWorkOrderId', $workOrder->id);

        $workOrder->refresh();

        $this->assertCount(2, $workOrder->items);
        $this->assertSame(210000.0, (float) $workOrder->price_snapshot['total_amount']);
    }

    public function test_cashier_can_create_invoice_and_record_payment(): void
    {
        $workOrder = $this->makeCompletedWorkOrder();
        $product = Product::create([
            'sku' => 'SKU-002',
            'barcode' => '9876543210987',
            'name' => 'Filter Udara',
            'cost_price' => 30000,
            'sell_price' => 58000,
            'is_active' => true,
        ]);

        Livewire::test(Cashier::class)
            ->call('selectWorkOrder', $workOrder->id)
            ->call('addCatalogItem', 'product', $product->id)
            ->set('discount', '8000')
            ->set('tax', '5000')
            ->call('createInvoice')
            ->assertSet('selectedWorkOrderId', $workOrder->id)
            ->call('recordPayment');

        $invoice = Invoice::query()->where('work_order_id', $workOrder->id)->first();

        $this->assertNotNull($invoice);
        $this->assertSame(InvoiceStatus::Paid, $invoice->status);
        $this->assertSame(0.0, (float) $invoice->outstanding_amount);
        $this->assertSame(1, $invoice->payments()->count());

        $payment = Payment::query()->first();
        $this->assertNotNull($payment);
        $this->assertSame(PaymentMethod::Cash, $payment->method);
        $this->assertSame(WorkOrderStatus::Paid, $workOrder->fresh()->status);
    }

    private function makeCompletedWorkOrder(): WorkOrder
    {
        $branch = Branch::create([
            'name' => 'Cabang Utama',
            'code' => 'CBG-001',
            'address' => 'Jl. Utama No. 1',
            'phone' => '08123456789',
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jakarta',
            'email' => 'budi@example.com',
        ]);

        $vehicle = Vehicle::create([
            'customer_id' => $customer->id,
            'plate_number' => 'B 1234 KSP',
            'brand' => 'Toyota',
            'type' => 'Avanza',
            'year' => 2020,
            'last_mileage' => 45000,
        ]);

        return WorkOrder::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'assigned_mechanic_id' => null,
            'status' => WorkOrderStatus::Completed,
            'odometer' => 45000,
            'complaint' => 'Servis rutin',
            'price_snapshot' => ['items' => [], 'total_items' => 0, 'total_amount' => 0],
        ]);
    }
}
