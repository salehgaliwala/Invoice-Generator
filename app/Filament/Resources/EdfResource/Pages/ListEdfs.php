<?php

namespace App\Filament\Resources\EdfResource\Pages;

use App\Filament\Resources\EdfResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEdfs extends ListRecords
{
    protected static string $resource = EdfResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
