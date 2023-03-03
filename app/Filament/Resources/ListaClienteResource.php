<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\ListaCliente;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ListaClienteResource\Pages;
use App\Filament\Resources\ListaClienteResource\RelationManagers;

class ListaClienteResource extends Resource
{
    protected static ?string $model = ListaCliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationLabel = 'Cliente';
    protected static ?string $pluralModelLabel = 'Clientes';
    protected static ?string $modelLabel = 'Cliente';
    protected static ?string $slug = 'clientes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nombre')
                    ->required(),
                TextInput::make('direccion')
                    ->label('Dirección'),
                TextInput::make('localidad'),
                TextInput::make('contacto')
                    ->required(),
                Fieldset::make('adicional')
                    ->label('Información adicional')
                    ->schema([
                        TextInput::make('documento'),
                        TextInput::make('cuit_cuil')
                            ->label('CUIT/CUIL'),
                        TextInput::make('razon_social')
                            ->label('Razón Social'),
                    ])
                    ->columns(3)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre'),
                TextColumn::make('direccion')
                    ->label('Dirección'),
                TextColumn::make('localidad'),
                TextColumn::make('contacto'),
                TextColumn::make('documento'),
                TextColumn::make('cuit_cuil')
                    ->label('CUIT/CUIL'),
                TextColumn::make('razon_social')
                    ->label('Razón Social'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            RelationManagers\PedidosRelationManager::class,
            RelationManagers\PedidoPiedrasRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListListaClientes::route('/'),
            'create' => Pages\CreateListaCliente::route('/create'),
            'edit' => Pages\EditListaCliente::route('/{record}/edit'),
        ];
    }    

    public static function canCreate(): bool
    {
        return false;
    }
}
