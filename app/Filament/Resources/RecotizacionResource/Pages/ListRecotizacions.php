<?php

namespace App\Filament\Resources\RecotizacionResource\Pages;

use App\Filament\Resources\RecotizacionResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRecotizacions extends ListRecords
{
    protected static string $resource = RecotizacionResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
