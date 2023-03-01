<?php

namespace App\Filament\Resources\CorteResource\Pages;

use App\Filament\Resources\CorteResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCortes extends ListRecords
{
    protected static string $resource = CorteResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
