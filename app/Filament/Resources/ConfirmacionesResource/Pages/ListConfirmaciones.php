<?php

namespace App\Filament\Resources\ConfirmacionesResource\Pages;

use App\Filament\Resources\ConfirmacionesResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConfirmaciones extends ListRecords
{
    protected static string $resource = ConfirmacionesResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
