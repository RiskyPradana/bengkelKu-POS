<?php

namespace App\Filament\Resources;

use App\Domains\WorkOrder\Enums\WorkOrderStatus;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Filament\Resources\WorkOrderResource\Pages;
use App\Filament\Resources\WorkOrderResource\RelationManagers\ItemsRelationManager;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Operasional';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Data SPK')->schema([
                Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('vehicle_id')
                    ->relationship('vehicle', 'plate_number')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('assigned_mechanic_id')
                    ->label('Mekanik')
                    ->relationship('mechanic', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Select::make('status')
                    ->options(collect(WorkOrderStatus::cases())->mapWithKeys(fn (WorkOrderStatus $status) => [
                        $status->value => $status->label(),
                    ]))
                    ->default(WorkOrderStatus::Pending->value)
                    ->required(),
                TextInput::make('odometer')
                    ->numeric()
                    ->minValue(0),
                Textarea::make('complaint')
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('branch.name')->label('Cabang')->sortable()->toggleable(),
            TextColumn::make('customer.name')->label('Pelanggan')->searchable()->sortable(),
            TextColumn::make('vehicle.plate_number')->label('Plat')->searchable()->sortable(),
            TextColumn::make('mechanic.name')->label('Mekanik')->toggleable(),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('odometer')->label('KM')->sortable(),
            TextColumn::make('created_at')->dateTime('d M Y H:i')->sortable(),
        ])->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkOrders::route('/'),
            'create' => Pages\CreateWorkOrder::route('/create'),
            'edit' => Pages\EditWorkOrder::route('/{record}/edit'),
        ];
    }
}
