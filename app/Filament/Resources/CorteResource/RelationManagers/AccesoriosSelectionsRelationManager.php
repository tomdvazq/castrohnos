<?php

namespace App\Filament\Resources\CorteResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\Accesorios;
use Filament\Resources\Form;
use Filament\Resources\Table;
use App\Models\AccesorioListado;
use Illuminate\Support\HtmlString;
use App\Models\AccesoriosSelection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class AccesoriosSelectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'accesorios_selections';

    protected static ?string $pluralModelLabel = 'Accesorios';
    protected static ?string $modelLabel = 'accesorio';

    protected static ?string $recordTitleAttribute = 'material';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Selección de bacha')
                    ->schema([
                        Select::make('accesorio_id')
                            ->options(Accesorios::all()->pluck('marca', 'id')->toArray())
                            ->label('Marca')
                            ->afterStateUpdated(fn (callable $set, $get) => $set('accesorio_listado_id', null))
                            ->reactive(),

                        Select::make('accesorio_listado_id')
                            ->label('Línea')
                            ->options(function (callable $get) {
                                $accesorio = Accesorios::find($get('accesorio_id'));

                                if (!$accesorio) {
                                    return AccesorioListado::all()->pluck('tipo', 'id');
                                }

                                $value = $accesorio->accesoriosStock->pluck('tipo', 'id');

                                return $value;
                            })
                            ->afterStateUpdated(function ($set, $get) {
                                $id = AccesorioListado::find($get('accesorio_listado_id'));
                                $accesorio = $id?->tipo;
                                $stock = $id->stock;

                                $set('material', $accesorio);
                                $set('stock', $stock);
                            })
                            ->reactive()
                            ->searchable(),
                    ]),

                Fieldset::make('Cantidad y stock')
                    ->schema([
                        TextInput::make('cantidad')
                            ->label('Cantidad')
                            ->saveRelationshipsUsing(function ($set, $get) {
                                $accesorio = AccesorioListado::find($get('accesorio_listado_id'));
                                $m2 = $get('cantidad');
                                $stock = $accesorio->stock;

                                $accesorio->stock = intval($stock) - intval($m2);

                                $accesorio->save();
                            })
                            ->numeric()
                            ->suffix('U'),

                        TextInput::make('stock')
                            ->label('Stock de esta bacha')
                            ->numeric()
                            ->suffix('U'),
                    ]),

                TextInput::make('material')
                    ->label('')
                    ->lazy()
                    ->columnSpan('full')
                    ->extraAttributes(['style' => 'display: none'])
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('accesorio_id')
                    ->label('Marca')
                    ->getStateUsing(function ($record) {
                        $id = $record->accesorio_id;
                        $accesorio_id = collect(Accesorios::find($id, 'marca'));
                        $resultado = "";

                        foreach ($accesorio_id as $key => $value) {
                            if ($value) {
                                $resultado .= $value;
                            }
                        }

                        return $resultado;
                    }),

                TextColumn::make('accesorio_listado_id')
                    ->label('Línea')
                    ->getStateUsing(function ($record) {
                        $id = $record->accesorio_listado_id;
                        $accesorio_listado_id = collect(AccesorioListado::find($id, 'tipo'));
                        $resultado = "";

                        foreach ($accesorio_listado_id as $key => $value) {
                            if ($value) {
                                $resultado .= $value;
                            }
                        }

                        return $resultado;
                    }),

                TextColumn::make('modelo')
                    ->label('Modelo')
                    ->getStateUsing(function ($record) {
                        $id = $record->accesorio_listado_id;
                        $accesorio_listado_id = collect(AccesorioListado::find($id, 'modelo'));
                        $resultado = "";

                        foreach ($accesorio_listado_id as $key => $value) {
                            if ($value) {
                                $resultado .= $value;
                            } else if (!$value) {
                                $resultado .= 'N/M';
                            }
                        }

                        return $resultado;
                    }),

                TextColumn::make('cantidad')
                    ->label('Cantidad en unidades')
                    ->formatStateUsing(function ($record) {
                        return $record->cantidad . ' U';
                    })
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        Section::make(function ($record) {
                            $res = '🎯 ' . $record->material;

                            return new HtmlString($res);
                        })
                            ->schema([
                                Select::make('accesorio_id')
                                    ->options(Accesorios::all()->pluck('marca', 'id')->toArray())
                                    ->label('Marca')
                                    ->disabled()
                                    ->afterStateUpdated(fn (callable $set, $get) => $set('accesorio_listado_id', null))
                                    ->reactive(),

                                Select::make('accesorio_listado_id')
                                    ->label('Línea')
                                    ->disabled()
                                    ->options(function (callable $get) {
                                        $material = Accesorios::find($get('accesorio_id'));

                                        if (!$material) {
                                            return AccesorioListado::all()->pluck('tipo', 'id');
                                        }

                                        $value = $material->accesoriosStock->pluck('tipo', 'id');

                                        return $value;
                                    })
                                    ->afterStateUpdated(function ($set, $get) {
                                        $id = AccesorioListado::find($get('accesorio_listado_id'));
                                        $material = $id?->material;
                                        $stock = $id->stock;

                                        $set('material', $material);
                                        $set('stock', $stock);
                                    })
                                    ->reactive()
                                    ->searchable(),
                            ])
                            ->columns(2)
                            ->collapsed(),

                        Section::make(function ($record) {

                            $estadoStock = "";

                            $res = '👌 Esta mesada utiliza ' . $record->cantidad . ' U del material.' . '<br> <span style="font-size: 16px; font-weight: 100;">El stock actualizado de <span style="color: #5A5100; font-weight: 500">' . $record->material . '</span> es de <span style="font-weight: 500">' . AccesorioListado::find($record->accesorio_listado_id)->stock . ' U</span> ' . $estadoStock . '</span>';

                            return new HtmlString($res);
                        })
                            ->schema([
                                TextInput::make('cantidad')
                                    ->label('Cantidad')
                                    ->disabled()
                                    ->numeric()
                                    ->suffix('U'),

                                TextInput::make('stock')
                                    ->label('Stock actual del material')
                                    ->placeholder(function ($record) {
                                        $material = AccesorioListado::find($record->accesorio_listado_id);

                                        return $material->stock;
                                    })
                                    ->disabled()
                                    ->numeric()
                                    ->suffix('U'),
                            ])
                            ->collapsed()
                            ->columns(2),

                        Section::make(function ($record) {
                            $res = '<span style="font-size: 18px">Agregar o restar unidades de ' . $record->material . ' en el pedido ' . $record->pedidos->identificacion . '</span>';

                            return new HtmlString($res);
                        })
                            ->description('Tenga en cuenta que está manipulando el stock')
                            ->schema([
                                TextInput::make('quantity')
                                    ->label(function ($record) {
                                        $res = "<span style='color: #20BF42;'>(+) </span> Agregar U";

                                        return new HtmlString($res);
                                    })
                                    ->numeric()
                                    ->suffix('U')
                                    ->saveRelationshipsUsing(function ($get, $record) {
                                        // Actualizar stock
                                        $material = AccesorioListado::find($get('accesorio_listado_id'));
                                        $m2 = $get('quantity');
                                        $stock = $material->stock;

                                        $material->stock = intval($stock) - intval($m2);

                                        $material->save();
                                        // Sumar U al pedido
                                        $seleccion = AccesoriosSelection::find($get('id'));
                                        $actual = $seleccion->cantidad;

                                        $seleccion->cantidad = intval($actual) + intval($m2);

                                        $seleccion->save();
                                    }),

                                TextInput::make('quantityRes')
                                    ->label(function ($record) {
                                        $res = "<span style='color: red;'>(-) </span> Restar U";

                                        return new HtmlString($res);
                                    })
                                    ->numeric()
                                    ->suffix('U')
                                    ->saveRelationshipsUsing(function ($get, $record) {
                                        // Actualizar stock
                                        $material = AccesorioListado::find($get('accesorio_listado_id'));
                                        $m2 = $get('quantityRes');
                                        $stock = $material->stock;

                                        $material->stock = intval($stock) + intval($m2);

                                        $material->save();
                                        // Sumar U al pedido
                                        $seleccion = AccesoriosSelection::find($get('id'));
                                        $actual = $seleccion->cantidad;

                                        $seleccion->cantidad = intval($actual) - intval($m2);

                                        $seleccion->save();
                                    })
                            ])
                            ->columns(2)
                    ]),
                Tables\Actions\DeleteAction::make()
                    ->action(function ($record) {
                        //Sumar y guardar el stock con la cantidad de material eliminado
                        $seleccion = $record->accesorio_id;
                        $cantidad = $record->cantidad;
                        $material = AccesorioListado::find($seleccion);
                        $material->stock = intval($material->stock) + $cantidad;
                        $material->save();

                        // Eliminar este material de la mesada
                        $materialPedido = AccesoriosSelection::find($record->id);
                        $materialPedido->delete();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
