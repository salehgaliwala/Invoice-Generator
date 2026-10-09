<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Settings';

    protected static bool $isScopedToTenant = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Company Identity')
                    ->schema([
                        Forms\Components\TextInput::make('legal_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('trade_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('gstin')
                            ->label('GSTIN (15 Chars)')
                            ->maxLength(15)
                            ->placeholder('e.g. 27AAAAA0000A1Z5'),
                        Forms\Components\TextInput::make('pan')
                            ->label('PAN')
                            ->maxLength(10)
                            ->placeholder('e.g. AAAAA0000A'),
                        Forms\Components\TextInput::make('iec')
                            ->label('IEC (Import Export Code)')
                            ->maxLength(20)
                            ->placeholder('e.g. 0123456789'),
                    ])->columns(2),

                Forms\Components\Section::make('Registered Address')
                    ->schema([
                        Forms\Components\Textarea::make('registered_address')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('state')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('state_code')
                            ->required()
                            ->length(2)
                            ->placeholder('e.g. 27'),
                    ])->columns(2),

                Forms\Components\Section::make('Authorized Signatory & Bank Details')
                    ->schema([
                        Forms\Components\TextInput::make('authorized_signatory_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('authorized_signatory_designation')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bank_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bank_branch')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bank_account_number')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bank_ifsc')
                            ->label('IFSC Code')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bank_ad_code')
                            ->label('AD Code (Authorized Dealer Code for Forex)')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Branding & Invoicing Settings')
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('logo')
                            ->collection('logo')
                            ->image(),
                        Forms\Components\SpatieMediaLibraryFileUpload::make('signature')
                            ->collection('signature')
                            ->image(),
                        Forms\Components\TextInput::make('invoice_prefix')
                            ->default('INV')
                            ->required()
                            ->maxLength(10),
                        Forms\Components\TextInput::make('invoice_suffix')
                            ->maxLength(10),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('legal_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('gstin')->searchable(),
                Tables\Columns\TextColumn::make('iec')->searchable(),
                Tables\Columns\TextColumn::make('state'),
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
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
