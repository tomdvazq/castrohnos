<?php

namespace App\Filament\Resources\PedidoResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\Material;
use Filament\Resources\Form;
use Filament\Resources\Table;
use App\Models\MaterialListado;
use App\Models\MaterialesSelection;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class MaterialesSelectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'materiales_selections';

    protected static ?string $recordTitleAttribute = 'material_listado_id';

    protected static ?string $pluralModelLabel = 'Materiales';
    protected static ?string $modelLabel = 'material';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('material_id')
                    ->options(Material::all()->pluck('tipo', 'id')->toArray())
                    ->label('Tipo de material')
                    ->afterStateUpdated(fn (callable $set) => $set('material_listado_id', null))
                    ->reactive(),

                Select::make('material_listado_id')
                    ->label('Material')
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

                        $set('material', $material);
                    })
                    ->searchable(),

                TextInput::make('cantidad')
                    ->label('Cantidad')
                    ->afterStateUpdated(function ($set, $get) {
                        $material = MaterialListado::find($get('material_listado_id'));
                        $stock = $material?->stock ?? 0;
                        $m2 = $get('cantidad');

                        $set($stock, intval($stock) - intval($m2));
                    })
                    ->numeric()
                    ->suffix('m²'),

                TextInput::make('material')
                    ->label('Renderización de materiales')
                    ->lazy()
                    ->columnSpan('full')
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('material_id')
                    ->label('Tipo de material')
                    ->getStateUsing(function ($record) {
                        $id = $record->material_id;
                        $material_id = collect(Material::find($id, 'tipo'));
                        $resultado = "";

                        foreach ($material_id as $key => $value) {
                            if ($value) {
                                $resultado .= $value;
                            }
                        }

                        return $resultado;
                    }),

                TextColumn::make('material_listado_id')
                    ->label('Material')
                    ->getStateUsing(function ($record) {
                        $id = $record->material_listado_id;
                        $material_listado_id = collect(MaterialListado::find($id, 'material'));
                        $resultado = "";

                        foreach ($material_listado_id as $key => $value) {
                            if ($value) {
                                $resultado .= $value;
                            }
                        }

                        return $resultado;
                    }),
                
                TextColumn::make('cantidad')
                    ->label('Cantidad en m²')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
