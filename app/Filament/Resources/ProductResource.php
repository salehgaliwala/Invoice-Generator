<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Product / Service Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('sku')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('hsn_sac_code')
                            ->label('HSN / SAC Code')
                            ->required()
                            ->maxLength(10),
                        Forms\Components\TextInput::make('uom')
                            ->label('Unit of Measurement (UOM)')
                            ->default('PCS')
                            ->required(),
                        Forms\Components\TextInput::make('unit_price')
                            ->numeric()
                            ->prefix('₹')
                            ->default(0.00)
                            ->required(),
                        Forms\Components\TextInput::make('gst_rate')
                            ->label('GST Rate (%)')
                            ->numeric()
                            ->suffix('%')
                            ->default(18.00)
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('hsn_sac_code')->label('HSN/SAC')->searchable(),
                Tables\Columns\TextColumn::make('unit_price')->money('INR')->sortable(),
                Tables\Columns\TextColumn::make('gst_rate')->suffix('%')->sortable(),
                Tables\Columns\TextColumn::make('uom'),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
