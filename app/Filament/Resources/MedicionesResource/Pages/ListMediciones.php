<?php

namespace App\Filament\Resources\MedicionesResource\Pages;

use App\Filament\Resources\MedicionesResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMediciones extends ListRecords
{
    protected static string $resource = MedicionesResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
