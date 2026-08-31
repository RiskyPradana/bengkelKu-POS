<?php

namespace App\Filament\Resources\WorkOrderResource\RelationManagers;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ServiceItem;
use App\Domains\WorkOrder\Enums\WorkOrderStatus;
use App\Domains\WorkOrder\Models\WorkOrderItem;
use App\Domains\WorkOrder\Services\WorkOrderService;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Item SPK';

    public function form(Form $form): Form
    {
        return $form->schema([
            Hidden::make('item_type'),
            Hidden::make('product_id'),
            Hidden::make('service_item_id'),
            Select::make('source_type')
                ->label('Jenis Item')
                ->options([
                    'product' => 'Sparepart',
                    'service' => 'Jasa',
                ])
                ->live()
                ->required(),
            Select::make('product_id')
                ->label('Sparepart')
                ->options(fn (): array => Product::query()->orderBy('name')->pluck('name', 'id')->all())
                ->visible(fn (callable $get): bool => $get('source_type') === 'product')
                ->searchable()
                ->preload(),
            Select::make('service_item_id')
                ->label('Jasa')
                ->options(fn (): array => ServiceItem::query()->orderBy('name')->pluck('name', 'id')->all())
                ->visible(fn (callable $get): bool => $get('source_type') === 'service')
                ->searchable()
                ->preload(),
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            TextInput::make('qty')
                ->numeric()
                ->minValue(1)
                ->default(1)
                ->required(),
            TextInput::make('unit_price')
                ->numeric()
                ->prefix('Rp')
                ->required(),
            TextInput::make('subtotal')
                ->numeric()
                ->prefix('Rp')
                ->required(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('item_type')->badge()->sortable(),
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('qty')->sortable(),
            TextColumn::make('unit_price')->money('IDR')->sortable(),
            TextColumn::make('subtotal')->money('IDR')->sortable(),
        ])
            ->defaultSort('created_at', 'desc');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $sourceType = Arr::get($data, 'source_type');
        $quantity = (int) Arr::get($data, 'qty', 1);

        if ($sourceType === 'product') {
            $product = Product::findOrFail($data['product_id']);

            $data['item_type'] = 'product';
            $data['name'] = $data['name'] ?: $product->name;
            $data['unit_price'] = $data['unit_price'] ?: $product->sell_price;
            $data['subtotal'] = (float) $data['unit_price'] * $quantity;
            $data['snapshot'] = [
                'source' => 'product',
                'name' => $data['name'],
                'sku' => $product->sku,
                'unit_price' => $data['unit_price'],
            ];

            return $data;
        }

        $serviceItem = ServiceItem::findOrFail($data['service_item_id']);

        $data['item_type'] = 'service';
        $data['name'] = $data['name'] ?: $serviceItem->name;
        $data['unit_price'] = $data['unit_price'] ?: $serviceItem->price;
        $data['subtotal'] = (float) $data['unit_price'] * $quantity;
        $data['snapshot'] = [
            'source' => 'service',
            'name' => $data['name'],
            'code' => $serviceItem->code,
            'unit_price' => $data['unit_price'],
        ];

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['subtotal'] = (float) $data['unit_price'] * (int) $data['qty'];

        return $data;
    }

    protected function beforeCreate(): void
    {
        $workOrder = $this->getOwnerRecord();

        if ($workOrder->status === WorkOrderStatus::Paid) {
            Notification::make()
                ->title('SPK sudah lunas')
                ->body('Item tidak bisa diubah setelah SPK berstatus Paid.')
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function beforeSave(Model $record): void
    {
        if ($record instanceof WorkOrderItem && $record->workOrder?->status === WorkOrderStatus::Paid) {
            Notification::make()
                ->title('SPK sudah lunas')
                ->body('Item tidak bisa diubah setelah SPK berstatus Paid.')
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
