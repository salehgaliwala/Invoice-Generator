<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EdfResource\Pages;
use App\Models\Edf;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EdfResource extends Resource
{
    protected static ?string $model = Edf::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationLabel = 'Export Declarations (EDF)';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Associated Export Invoice')
                    ->schema([
                        Forms\Components\Select::make('invoice_id')
                            ->label('Export Invoice')
                            ->options(fn () => Invoice::whereIn('transaction_type', ['export_lut', 'export_igst'])->pluck('invoice_number', 'id'))
                            ->required()
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($invoice = Invoice::find($state)) {
                                    $company = $invoice->company;
                                    $set('iec_number', $company->iec ?? '');
                                    $set('ad_code', $company->bank_ad_code ?? '');
                                    $set('currency_of_realization', $invoice->currency ?: 'USD');
                                    $set('exchange_rate', $invoice->exchange_rate ?: 1.0000);
                                    $set('invoice_value_fcy', $invoice->total_amount);
                                    $set('total_realizable_value_fcy', $invoice->total_amount);
                                    $set('total_realizable_value_inr', round($invoice->total_amount * ($invoice->exchange_rate ?: 1.0000), 2));
                                }
                            }),
                        Forms\Components\TextInput::make('edf_number')
                            ->label('EDF Reference Number')
                            ->placeholder('e.g. EDF-FY24-25/INV/0001'),
                    ])->columns(2),

                Forms\Components\Section::make('RBI & Customs Identifiers')
                    ->schema([
                        Forms\Components\TextInput::make('iec_number')
                            ->label('Exporter IEC Number')
                            ->required(),
                        Forms\Components\TextInput::make('ad_code')
                            ->label('Authorized Dealer (AD) Code')
                            ->required(),
                        Forms\Components\TextInput::make('port_of_export')
                            ->label('Port of Export')
                            ->required()
                            ->placeholder('e.g. Nava Sheva (INNSA1)'),
                    ])->columns(3),

                Forms\Components\Section::make('Shipping Bill & CHA Details')
                    ->schema([
                        Forms\Components\TextInput::make('shipping_bill_number')
                            ->label('Shipping Bill Number'),
                        Forms\Components\DatePicker::make('shipping_bill_date')
                            ->label('Shipping Bill Date'),
                        Forms\Components\TextInput::make('cha_name')
                            ->label('Custom House Agent (CHA) Name'),
                        Forms\Components\TextInput::make('cha_license_number')
                            ->label('CHA License Number'),
                    ])->columns(2),

                Forms\Components\Section::make('Vessel & Freight Details')
                    ->schema([
                        Forms\Components\TextInput::make('vessel_flight_no')
                            ->label('Vessel / Flight Number'),
                        Forms\Components\TextInput::make('port_of_loading')
                            ->label('Port of Loading'),
                        Forms\Components\TextInput::make('port_of_discharge')
                            ->label('Port of Discharge'),
                    ])->columns(3),

                Forms\Components\Section::make('Valuation & Realization Details')
                    ->schema([
                        Forms\Components\Select::make('nature_of_contract')
                            ->options([
                                'FOB' => 'FOB (Free on Board)',
                                'CIF' => 'CIF (Cost, Insurance & Freight)',
                                'CFR' => 'CFR (Cost & Freight)',
                                'C&F' => 'C&F',
                            ])
                            ->default('FOB')
                            ->required(),

                        Forms\Components\TextInput::make('currency_of_realization')
                            ->default('USD')
                            ->required()
                            ->maxLength(3),

                        Forms\Components\TextInput::make('exchange_rate')
                            ->numeric()
                            ->default(1.0000)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                $rate = (float) ($get('exchange_rate') ?? 1);
                                $fcy = (float) ($get('total_realizable_value_fcy') ?? 0);
                                $set('total_realizable_value_inr', round($fcy * $rate, 2));
                            }),

                        Forms\Components\TextInput::make('invoice_value_fcy')
                            ->label('Invoice Value (FCY)')
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('total_realizable_value_fcy')
                            ->label('Total Realizable Value (FCY)')
                            ->numeric()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $rate = (float) ($get('exchange_rate') ?? 1);
                                $set('total_realizable_value_inr', round((float) $state * $rate, 2));
                            }),

                        Forms\Components\TextInput::make('total_realizable_value_inr')
                            ->label('Total Realizable Value (INR)')
                            ->numeric()
                            ->prefix('₹')
                            ->required(),

                        Forms\Components\Textarea::make('remarks')
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('edf_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('invoice.invoice_number')->label('Invoice No')->searchable(),
                Tables\Columns\TextColumn::make('iec_number')->label('IEC')->searchable(),
                Tables\Columns\TextColumn::make('port_of_export')->searchable(),
                Tables\Columns\TextColumn::make('total_realizable_value_fcy')->label('Realizable FCY')->sortable(),
                Tables\Columns\TextColumn::make('currency_of_realization')->label('Currency'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('download_edf_pdf')
                    ->label('EDF PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->url(fn (Edf $record) => route('edfs.pdf', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEdfs::route('/'),
            'create' => Pages\CreateEdf::route('/create'),
            'edit' => Pages\EditEdf::route('/{record}/edit'),
        ];
    }
}
