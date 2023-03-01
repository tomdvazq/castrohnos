<?php

namespace App\Filament\Resources\NuevoPiedraResource\Pages;

use App\Filament\Resources\NuevoPiedraResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNuevoPiedras extends ListRecords
{
    protected static string $resource = NuevoPiedraResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
