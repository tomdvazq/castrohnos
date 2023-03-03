<?php

namespace App\Filament\Resources\ListaClienteResource\Pages;

use App\Filament\Resources\ListaClienteResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListListaClientes extends ListRecords
{
    protected static string $resource = ListaClienteResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
