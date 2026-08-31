<?php

namespace App\Filament\Resources;

use App\Domains\POS\Enums\PaymentMethod;
use App\Domains\POS\Models\Invoice;
use App\Domains\POS\Models\Payment;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Operasional';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Pembayaran')->schema([
                Select::make('invoice_id')
                    ->relationship('invoice', 'invoice_number')
                    ->searchable()
                    ->preload()
                    ->required(),
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
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('invoice.invoice_number')->label('Invoice')->searchable()->sortable(),
            TextColumn::make('method')->badge()->sortable(),
            TextColumn::make('amount')->money('IDR')->sortable(),
            TextColumn::make('reference_number')->toggleable(),
            TextColumn::make('paid_at')->dateTime('d M Y H:i')->sortable(),
        ])->defaultSort('paid_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
