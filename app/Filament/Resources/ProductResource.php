<?php

namespace App\Filament\Resources;

use App\Domains\Catalog\Models\Product;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Data Produk')->schema([
                TextInput::make('sku')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(100),
                TextInput::make('barcode')
                    ->unique(ignoreRecord: true)
                    ->maxLength(100),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('cost_price')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                TextInput::make('sell_price')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                Toggle::make('is_active')
                    ->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('sku')->searchable()->sortable(),
            TextColumn::make('barcode')->searchable()->toggleable(),
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('cost_price')->money('IDR')->label('HPP'),
            TextColumn::make('sell_price')->money('IDR')->label('Harga Jual'),
            IconColumn::make('is_active')->boolean()->label('Aktif'),
        ])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
