<?php

namespace App\Filament\Resources\PedidoPiedraResource\Pages;

use App\Filament\Resources\PedidoPiedraResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPedidoPiedras extends ListRecords
{
    protected static string $resource = PedidoPiedraResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
