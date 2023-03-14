<?php

namespace App\Filament\Resources\PedidoPiedraResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\Material;
use Filament\Resources\Form;
use Filament\Resources\Table;
use App\Models\MaterialListado;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class PiedrasSelectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'piedras_selections';

    protected static ?string $recordTitleAttribute = 'pedido_id';

    protected static ?string $pluralModelLabel = 'Pedido';
    protected static ?string $modelLabel = 'piedra en el pedido';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('material_id')
                    ->required()
                    ->options(Material::all()->pluck('tipo', 'id')->toArray())
                    ->label('Tipo de piedra')
                    ->afterStateUpdated(fn (callable $set) => $set('material_listado_id', null))
                    ->reactive(),

                Select::make('material_listado_id')
                    ->required()
                    ->label('Piedra seleccionada')
                    ->options(function (callable $get) {
                        $material = Material::find($get('material_id'));

                        if (!$material) {
                            return MaterialListado::all()->pluck('material', 'id');
                        }

                        $value = $material->materialesStock->pluck('material', 'id');

                        return $value;
                    })
                    ->afterStateUpdated(function ($set, $get) {
                        $id = MaterialListado::find($get('material_listado_id'));
                        $material = $id?->material;
                        $stock = $id->stock;

                        $set('material', $material);
                        $set('stock', $stock);
                    })
                    ->reactive()
                    ->searchable(),

                TextInput::make('cantidad')
                    ->required()
                    ->label('Cantidad')
                    ->saveRelationshipsUsing(function ($set, $get) {
                        $material = MaterialListado::find($get('material_listado_id'));
                        $m2 = $get('cantidad');
                        $stock = $material->stock;

                        $material->stock = intval($stock) - intval($m2);

                        $material->save();
                    })
                    ->numeric()
                    ->suffix('m²'),

                TextInput::make('stock')
                    ->label('Validación de stock del material')
                    ->numeric()
                    ->suffix('m²'),

                TextInput::make('material')
                    ->extraAttributes(['style' => 'display: none'])
                    ->label('')
                    ->lazy()
                    ->columnSpan('full')
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('')
                    ->label('Pedido')
                    ->getStateUsing(function ($record) {
                        $material = $record->material;
                        $cantidad = $record->cantidad . ' m²';

                        return $cantidad . ' de ' . $material;
                    }),
                TextColumn::make('entregado'),
                TextColumn::make('pendiente')
                    ->label('Pendiente de entrega'),
                TextColumn::make('estado')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Agregar piedras al pedido'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
