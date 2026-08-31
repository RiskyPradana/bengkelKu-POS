<?php

namespace App\Filament\Resources;

use App\Domains\CustomerVehicle\Models\Vehicle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Data Kendaraan')->schema([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('plate_number')
                    ->required()
                    ->maxLength(20),
                TextInput::make('brand')
                    ->maxLength(100),
                TextInput::make('type')
                    ->maxLength(100),
                TextInput::make('year')
                    ->numeric()
                    ->minValue(1900)
                    ->maxValue((int) now()->format('Y')),
                TextInput::make('last_mileage')
                    ->numeric()
                    ->minValue(0),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('plate_number')->searchable()->sortable(),
            TextColumn::make('customer.name')->label('Pelanggan')->searchable()->sortable(),
            TextColumn::make('brand')->searchable()->toggleable(),
            TextColumn::make('type')->searchable()->toggleable(),
            TextColumn::make('last_mileage')->label('KM Terakhir')->sortable(),
        ])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
