<?php

namespace App\Filament\Resources\EdfResource\Pages;

use App\Filament\Resources\EdfResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEdf extends EditRecord
{
    protected static string $resource = EdfResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
