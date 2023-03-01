<?php

namespace App\Filament\Resources\NuevoPiedraResource\Pages;

use App\Filament\Resources\NuevoPiedraResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNuevoPiedra extends EditRecord
{
    protected static string $resource = NuevoPiedraResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
