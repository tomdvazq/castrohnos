<?php

namespace App\Filament\Resources\NuevoResource\Pages;

use App\Filament\Resources\NuevoResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNuevos extends ListRecords
{
    protected static string $resource = NuevoResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
