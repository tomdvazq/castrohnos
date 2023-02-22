<?php

namespace App\Filament\Resources\ClienteResource\Pages;

use Filament\Pages\Actions;
use App\Filament\Resources\PedidoResource;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ClienteResource;
use App\Filament\Resources\ClienteResource\RelationManagers;
use App\Filament\Resources\ClienteResource\Pages\EditCliente;

class CreateCliente extends CreateRecord
{
    protected static string $resource = ClienteResource::class;

    // protected function getRedirectUrl(): string
    // {
    //     return ClienteResource::getUrl(EditCliente::route('/{record}/edit'));
    // }

}
