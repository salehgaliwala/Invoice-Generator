<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Customer;
use App\Models\Edf;
use App\Models\Invoice;
use App\Models\Product;
use App\Services\GstTaxCalculatorService;
use App\Services\InvoiceNumberGeneratorService;
use App\Services\NumberToWordsService;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Invoice Header Details')
                        ->schema([
                            Forms\Components\Select::make('customer_id')
                                ->label('Customer / Buyer')
                                ->relationship('customer', 'name')
                                ->options(fn () => Customer::where('company_id', Filament::getTenant()?->id)->pluck('name', 'id'))
                                ->required()
                                ->searchable()
                                ->preload()
                                ->reactive()
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    if ($customer = Customer::find($state)) {
                                        $set('place_of_supply_state', $customer->billing_state);
                                        $set('place_of_supply_state_code', $customer->billing_state_code);
                                        if ($customer->is_export) {
                                            $set('transaction_type', 'export_lut');
                                        } else {
                                            $set('transaction_type', 'domestic');
                                        }
                                        self::updateFormTotals($set, $get);
                                    }
                                }),

                            Forms\Components\TextInput::make('invoice_number')
                                ->label('Invoice Serial Number (Max 16 chars)')
                                ->required()
                                ->maxLength(16)
                                ->default(function () {
                                    $company = Filament::getTenant();
                                    if ($company) {
                                        return (new InvoiceNumberGeneratorService())->generateNextNumber($company);
                                    }
                                    return null;
                                })
                                ->helperText('Auto-generated per GST Rule 46. You may manually edit if required.'),

                            Forms\Components\DatePicker::make('invoice_date')
                                ->required()
                                ->default(now())
                                ->reactive()
                                ->afterStateUpdated(function ($state, Set $set) {
                                    if ($state) {
                                        $fy = (new InvoiceNumberGeneratorService())->getFinancialYear($state);
                                        $set('financial_year', $fy);
                                    }
                                }),

                            Forms\Components\TextInput::make('financial_year')
                                ->required()
                                ->default(function () {
                                    return (new InvoiceNumberGeneratorService())->getFinancialYear(now());
                                })
                                ->readOnly(),

                            Forms\Components\DatePicker::make('due_date'),

                            Forms\Components\Select::make('transaction_type')
                                ->label('Transaction Classification')
                                ->options([
                                    'domestic' => 'Domestic (CGST+SGST or IGST)',
                                    'export_lut' => 'Export under LUT/Bond without payment of tax',
                                    'export_igst' => 'Export on payment of IGST',
                                ])
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(fn (Set $set, Get $get) => self::updateFormTotals($set, $get)),

                            Forms\Components\TextInput::make('place_of_supply_state')
                                ->label('Place of Supply (State)')
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(fn (Set $set, Get $get) => self::updateFormTotals($set, $get)),

                            Forms\Components\TextInput::make('place_of_supply_state_code')
                                ->label('POS State Code')
                                ->required()
                                ->length(2)
                                ->reactive()
                                ->afterStateUpdated(fn (Set $set, Get $get) => self::updateFormTotals($set, $get)),
                        ])->columns(2),

                    Forms\Components\Section::make('Export & LUT Declaration')
                        ->schema([
                            Forms\Components\TextInput::make('lut_number')
                                ->label('LUT / Bond Number')
                                ->placeholder('e.g. LUT/2024-25/001'),
                            Forms\Components\DatePicker::make('lut_date')
                                ->label('LUT / Bond Date'),
                            Forms\Components\TextInput::make('currency')
                                ->default('INR')
                                ->required()
                                ->maxLength(3),
                            Forms\Components\TextInput::make('exchange_rate')
                                ->numeric()
                                ->default(1.0000)
                                ->required(),
                        ])
                        ->columns(2)
                        ->visible(fn (Get $get) => in_array($get('transaction_type'), ['export_lut', 'export_igst'])),

                    Forms\Components\Section::make('Invoice Line Items')
                        ->schema([
                            Forms\Components\Repeater::make('items')
                                ->relationship('items')
                                ->schema([
                                    Forms\Components\Select::make('product_id')
                                        ->label('Select Product / Catalog Item')
                                        ->options(fn () => Product::where('company_id', Filament::getTenant()?->id)->pluck('name', 'id'))
                                        ->searchable()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                            if ($product = Product::find($state)) {
                                                $set('item_description', $product->name);
                                                $set('hsn_sac_code', $product->hsn_sac_code);
                                                $set('uom', $product->uom);
                                                $set('unit_price', $product->unit_price);
                                                $set('gst_rate', $product->gst_rate);
                                                self::updateItemAndFormTotals($set, $get);
                                            }
                                        }),

                                    Forms\Components\TextInput::make('item_description')
                                        ->required()
                                        ->columnSpan(2),

                                    Forms\Components\TextInput::make('hsn_sac_code')
                                        ->label('HSN / SAC Code')
                                        ->required(),

                                    Forms\Components\TextInput::make('quantity')
                                        ->numeric()
                                        ->default(1)
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(fn (Set $set, Get $get) => self::updateItemAndFormTotals($set, $get)),

                                    Forms\Components\TextInput::make('uom')
                                        ->default('PCS')
                                        ->required(),

                                    Forms\Components\TextInput::make('unit_price')
                                        ->numeric()
                                        ->prefix('₹')
                                        ->default(0.00)
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(fn (Set $set, Get $get) => self::updateItemAndFormTotals($set, $get)),

                                    Forms\Components\TextInput::make('discount_amount')
                                        ->numeric()
                                        ->prefix('₹')
                                        ->default(0.00)
                                        ->reactive()
                                        ->afterStateUpdated(fn (Set $set, Get $get) => self::updateItemAndFormTotals($set, $get)),

                                    Forms\Components\TextInput::make('gst_rate')
                                        ->label('GST Rate (%)')
                                        ->numeric()
                                        ->suffix('%')
                                        ->default(18.00)
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(fn (Set $set, Get $get) => self::updateItemAndFormTotals($set, $get)),

                                    Forms\Components\Hidden::make('cgst_rate')->default(0.00),
                                    Forms\Components\Hidden::make('sgst_rate')->default(0.00),
                                    Forms\Components\Hidden::make('igst_rate')->default(0.00),

                                    Forms\Components\TextInput::make('taxable_amount')
                                        ->numeric()
                                        ->readOnly(),

                                    Forms\Components\TextInput::make('cgst_amount')
                                        ->label('CGST')
                                        ->numeric()
                                        ->readOnly(),

                                    Forms\Components\TextInput::make('sgst_amount')
                                        ->label('SGST')
                                        ->numeric()
                                        ->readOnly(),

                                    Forms\Components\TextInput::make('igst_amount')
                                        ->label('IGST')
                                        ->numeric()
                                        ->readOnly(),

                                    Forms\Components\TextInput::make('total_amount')
                                        ->label('Line Total')
                                        ->numeric()
                                        ->readOnly(),

                                    Forms\Components\SpatieMediaLibraryFileUpload::make('attachments')
                                        ->collection('item_attachments')
                                        ->multiple()
                                        ->columnSpanFull(),
                                ])
                                ->columns(4)
                                ->reactive()
                                ->afterStateUpdated(fn (Set $set, Get $get) => self::updateFormTotals($set, $get)),
                        ]),

                    Forms\Components\Section::make('Supporting Header Attachments')
                        ->schema([
                            Forms\Components\SpatieMediaLibraryFileUpload::make('invoice_attachments')
                                ->collection('invoice_attachments')
                                ->multiple()
                                ->acceptedFileTypes(['application/pdf', 'image/png', 'image/jpeg', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']),
                        ]),
                ])->columnSpan(2),

                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Summary & Totals')
                        ->schema([
                            Forms\Components\TextInput::make('subtotal')
                                ->numeric()
                                ->prefix('₹')
                                ->readOnly(),
                            Forms\Components\TextInput::make('discount_amount')
                                ->label('Total Discount')
                                ->numeric()
                                ->prefix('₹')
                                ->readOnly(),
                            Forms\Components\TextInput::make('taxable_amount')
                                ->label('Taxable Subtotal')
                                ->numeric()
                                ->prefix('₹')
                                ->readOnly(),
                            Forms\Components\TextInput::make('cgst_amount')
                                ->label('Total CGST')
                                ->numeric()
                                ->prefix('₹')
                                ->readOnly(),
                            Forms\Components\TextInput::make('sgst_amount')
                                ->label('Total SGST')
                                ->numeric()
                                ->prefix('₹')
                                ->readOnly(),
                            Forms\Components\TextInput::make('igst_amount')
                                ->label('Total IGST')
                                ->numeric()
                                ->prefix('₹')
                                ->readOnly(),
                            Forms\Components\TextInput::make('total_tax_amount')
                                ->label('Total Tax')
                                ->numeric()
                                ->prefix('₹')
                                ->readOnly(),
                            Forms\Components\TextInput::make('round_off')
                                ->numeric()
                                ->prefix('₹')
                                ->readOnly(),
                            Forms\Components\TextInput::make('total_amount')
                                ->label('Grand Total')
                                ->numeric()
                                ->prefix('₹')
                                ->readOnly(),
                            Forms\Components\Textarea::make('amount_in_words')
                                ->readOnly()
                                ->rows(2),
                        ]),

                    Forms\Components\Section::make('Notes & Terms')
                        ->schema([
                            Forms\Components\Textarea::make('notes'),
                            Forms\Components\Textarea::make('terms_and_conditions'),
                        ]),
                ])->columnSpan(1),
            ])->columns(3);
    }

    public static function updateItemAndFormTotals(Set $set, Get $get): void
    {
        $company = Filament::getTenant();
        if (! $company) {
            return;
        }

        $posStateCode = $get('../../place_of_supply_state_code') ?? $get('place_of_supply_state_code') ?? '27';
        $transactionType = $get('../../transaction_type') ?? $get('transaction_type') ?? 'domestic';
        $customerGstin = null;
        if ($customerId = ($get('../../customer_id') ?? $get('customer_id'))) {
            $customerGstin = Customer::find($customerId)?->gstin;
        }

        $qty = (float) ($get('quantity') ?? 1);
        $unitPrice = (float) ($get('unit_price') ?? 0);
        $discount = (float) ($get('discount_amount') ?? 0);
        $gstRate = (float) ($get('gst_rate') ?? 0);

        $calculation = GstTaxCalculatorService::calculate(
            $company,
            $customerGstin,
            $posStateCode,
            $transactionType,
            [[
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'discount_amount' => $discount,
                'gst_rate' => $gstRate,
            ]]
        );

        if (! empty($calculation['items'][0])) {
            $item = $calculation['items'][0];
            $set('cgst_rate', sprintf('%.2f', $item['cgst_rate']));
            $set('sgst_rate', sprintf('%.2f', $item['sgst_rate']));
            $set('igst_rate', sprintf('%.2f', $item['igst_rate']));
            $set('taxable_amount', sprintf('%.2f', $item['taxable_amount']));
            $set('cgst_amount', sprintf('%.2f', $item['cgst_amount']));
            $set('sgst_amount', sprintf('%.2f', $item['sgst_amount']));
            $set('igst_amount', sprintf('%.2f', $item['igst_amount']));
            $set('total_amount', sprintf('%.2f', $item['total_amount']));
        }

        self::updateFormTotals($set, $get, true);
    }

    public static function updateFormTotals(Set $set, Get $get, bool $fromRepeater = false): void
    {
        $company = Filament::getTenant();
        if (! $company) {
            return;
        }

        $prefix = $fromRepeater ? '../../' : '';

        $posStateCode = $get($prefix . 'place_of_supply_state_code') ?? '27';
        $transactionType = $get($prefix . 'transaction_type') ?? 'domestic';
        $customerGstin = null;
        if ($customerId = $get($prefix . 'customer_id')) {
            $customerGstin = Customer::find($customerId)?->gstin;
        }

        $items = $get($prefix . 'items') ?? [];

        $calculation = GstTaxCalculatorService::calculate(
            $company,
            $customerGstin,
            $posStateCode,
            $transactionType,
            $items
        );

        $set($prefix . 'subtotal', sprintf('%.2f', $calculation['subtotal']));
        $set($prefix . 'discount_amount', sprintf('%.2f', $calculation['discount_amount']));
        $set($prefix . 'taxable_amount', sprintf('%.2f', $calculation['taxable_amount']));
        $set($prefix . 'cgst_amount', sprintf('%.2f', $calculation['cgst_amount']));
        $set($prefix . 'sgst_amount', sprintf('%.2f', $calculation['sgst_amount']));
        $set($prefix . 'igst_amount', sprintf('%.2f', $calculation['igst_amount']));
        $set($prefix . 'total_tax_amount', sprintf('%.2f', $calculation['total_tax_amount']));
        $set($prefix . 'round_off', sprintf('%.2f', $calculation['round_off']));
        $set($prefix . 'total_amount', sprintf('%.2f', $calculation['total_amount']));

        $currency = $get($prefix . 'currency') ?? 'INR';
        $amountInWords = NumberToWordsService::convert($calculation['total_amount'], $currency);
        $set($prefix . 'amount_in_words', $amountInWords);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('customer.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('invoice_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('transaction_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'domestic' => 'gray',
                        'export_lut' => 'info',
                        'export_igst' => 'success',
                        default => 'primary',
                    }),
                Tables\Columns\TextColumn::make('total_amount')->money('INR')->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('download_pdf')
                    ->label('Invoice PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (Invoice $record) => route('invoices.pdf', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('generate_edf')
                    ->label('Generate EDF')
                    ->icon('heroicon-o-globe-alt')
                    ->color('success')
                    ->visible(fn (Invoice $record) => $record->isExport() && ! $record->edf)
                    ->action(function (Invoice $record) {
                        $company = $record->company;

                        $edf = Edf::create([
                            'company_id' => $company->id,
                            'invoice_id' => $record->id,
                            'edf_number' => 'EDF-' . $record->invoice_number,
                            'iec_number' => $company->iec ?? '',
                            'ad_code' => $company->bank_ad_code ?? '',
                            'port_of_export' => 'Nava Sheva (INNSA1)',
                            'nature_of_contract' => 'FOB',
                            'currency_of_realization' => $record->currency ?: 'USD',
                            'exchange_rate' => $record->exchange_rate ?: 1.0000,
                            'invoice_value_fcy' => $record->total_amount,
                            'total_realizable_value_fcy' => $record->total_amount,
                            'total_realizable_value_inr' => round($record->total_amount * ($record->exchange_rate ?: 1.0000), 2),
                        ]);

                        Notification::make()
                            ->title('Export Declaration Form (EDF) generated successfully!')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('view_edf')
                    ->label('View/Edit EDF')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->visible(fn (Invoice $record) => $record->edf !== null)
                    ->url(fn (Invoice $record) => EdfResource::getUrl('edit', ['record' => $record->edf])),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
