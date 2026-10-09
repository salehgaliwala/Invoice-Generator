<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Customer Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('gstin')
                            ->label('Customer GSTIN')
                            ->maxLength(15),
                        Forms\Components\TextInput::make('pan')
                            ->label('PAN')
                            ->maxLength(10),
                        Forms\Components\Toggle::make('is_export')
                            ->label('Is Overseas / Export Customer?')
                            ->reactive(),
                    ])->columns(2),

                Forms\Components\Section::make('Billing Address (Bill To)')
                    ->schema([
                        Forms\Components\Textarea::make('billing_address')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('billing_city')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('billing_state')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('billing_state_code')
                            ->required()
                            ->length(2)
                            ->placeholder('e.g. 27 (or 96 for Foreign)'),
                        Forms\Components\TextInput::make('billing_country')
                            ->default('India')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Shipping Address (Ship To)')
                    ->schema([
                        Forms\Components\Textarea::make('shipping_address')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('shipping_city')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('shipping_state')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('shipping_state_code')
                            ->length(2),
                        Forms\Components\TextInput::make('shipping_country'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('gstin')->searchable(),
                Tables\Columns\TextColumn::make('billing_state')->sortable(),
                Tables\Columns\IconColumn::make('is_export')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
