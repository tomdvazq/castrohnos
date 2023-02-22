<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Nuevo;
use App\Models\Cliente;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\NuevoResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\NuevoResource\RelationManagers;

class NuevoResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationGroup = 'Pedidos';
    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';
    protected static ?string $navigationLabel = 'Nuevo';
    protected static ?string $pluralModelLabel = 'Nuevo';
    protected static ?string $modelLabel = 'pedido';
    protected static ?string $slug = 'nuevo';
    protected static ?int $navigationSort = 2;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nombre'),
                TextInput::make('direccion')
                    ->label('Dirección'),
                TextInput::make('localidad'),
                TextInput::make('contacto'),
                TextInput::make('documento'),
                TextInput::make('cuit_cuil')
                    ->label('CUIT/CUIL'),
                TextInput::make('razon_social')
                    ->label('Razón Social'),
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
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\CreateNuevo::route('/create'),
            'edit' => Pages\EditNuevo::route('/{record}/edit'),
        ];
    }    
}
