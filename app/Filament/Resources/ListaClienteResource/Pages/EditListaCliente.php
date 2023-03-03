<?php

namespace App\Filament\Resources\ListaClienteResource\Pages;

use App\Filament\Resources\ListaClienteResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditListaCliente extends EditRecord
{
    protected static string $resource = ListaClienteResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
