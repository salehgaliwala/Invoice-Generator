<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    protected function afterCreate(): void
    {
        /** @var \App\Models\User $user */
        if ($user = auth()->user()) {
            $user->companies()->syncWithoutDetaching([
                $this->record->id => ['role' => 'admin'],
            ]);
        }
    }
}
