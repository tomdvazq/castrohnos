<?php

namespace App\Filament\Resources\CorteResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\Bacha;
use App\Models\BachaListado;
use Filament\Resources\Form;
use Filament\Resources\Table;
use App\Models\BachasSelection;
use Illuminate\Support\HtmlString;
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

class BachasSelectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'bachas_selections';
    protected static ?string $pluralModelLabel = 'Bachas';
    protected static ?string $modelLabel = 'bacha';

    protected static ?string $recordTitleAttribute = 'material';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Selección de bacha')
                    ->schema([
                        Select::make('tipo_bacha')
                            ->label('Tipo de bacha')
                            ->options([
                                'Baño' => 'Baño',
                                'Cocina' => 'Cocina'
                            ])
                            ->required(),
                        Select::make('bacha_id')
                            ->options(Bacha::all()->pluck('marca', 'id')->toArray())
                            ->label('Marca')
                            ->afterStateUpdated(fn (callable $set, $get) => $set('bacha_listado_id', null))
                            ->reactive(),

                        Select::make('bacha_listado_id')
                            ->label('Línea')
                            ->options(function (callable $get) {
                                $bacha = Bacha::find($get('bacha_id'));

                                if (!$bacha) {
                                    return BachaListado::all()->pluck('linea', 'id');
                                }

                                $value = $bacha->bachasStock->pluck('linea', 'id');

                                return $value;
                            })
                            ->afterStateUpdated(function ($set, $get) {
                                $id = BachaListado::find($get('bacha_listado_id'));
                                $bacha = $id?->linea;
                                $stock = $id->stock;

                                $set('material', $bacha);
                                $set('stock', $stock);
                            })
                            ->reactive()
                            ->searchable(),
                    ])
                    ->columns(3),

                Fieldset::make('Cantidad y stock')
                    ->schema([
                        TextInput::make('cantidad')
                            ->label('Cantidad')
                            ->saveRelationshipsUsing(function ($set, $get) {
                                $bacha = BachaListado::find($get('bacha_listado_id'));
                                $m2 = $get('cantidad');
                                $stock = $bacha->stock;

                                $bacha->stock = intval($stock) - intval($m2);

                                $bacha->save();
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
                TextColumn::make('tipo_bacha')
                    ->label('Tipo de bacha'),
                TextColumn::make('bacha_id')
                    ->label('Marca')
                    ->getStateUsing(function ($record) {
                        $id = $record->bacha_id;
                        $material_id = collect(Bacha::find($id, 'marca'));
                        $resultado = "";

                        foreach ($material_id as $key => $value) {
                            if ($value) {
                                $resultado .= $value;
                            }
                        }

                        return $resultado;
                    }),

                TextColumn::make('bacha_listado_id')
                    ->label('Línea')
                    ->getStateUsing(function ($record) {
                        $id = $record->bacha_listado_id;
                        $bacha_listado_id = collect(BachaListado::find($id, 'linea'));
                        $resultado = "";

                        foreach ($bacha_listado_id as $key => $value) {
                            if ($value) {
                                $resultado .= $value;
                            }
                        }

                        return $resultado;
                    }),

                TextColumn::make('modelo')
                    ->label('Modelo')
                    ->getStateUsing(function ($record) {
                        $id = $record->bacha_listado_id;
                        $bacha_listado_id = collect(BachaListado::find($id, 'modelo'));
                        $resultado = "";

                        foreach ($bacha_listado_id as $key => $value) {
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
                                Select::make('bacha_id')
                                    ->options(Bacha::all()->pluck('marca', 'id')->toArray())
                                    ->label('Marca')
                                    ->disabled()
                                    ->afterStateUpdated(fn (callable $set, $get) => $set('bacha_listado_id', null))
                                    ->reactive(),

                                Select::make('bacha_listado_id')
                                    ->label('Línea')
                                    ->disabled()
                                    ->options(function (callable $get) {
                                        $material = Bacha::find($get('material_id'));

                                        if (!$material) {
                                            return BachaListado::all()->pluck('linea', 'id');
                                        }

                                        $value = $material->bachasStock->pluck('linea', 'id');

                                        return $value;
                                    })
                                    ->afterStateUpdated(function ($set, $get) {
                                        $id = BachaListado::find($get('bacha_listado_id'));
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

                            $res = '👌 Esta mesada utiliza ' . $record->cantidad . ' U del material.' . '<br> <span style="font-size: 16px; font-weight: 100;">El stock actualizado de <span style="color: #5A5100; font-weight: 500">' . $record->material . '</span> es de <span style="font-weight: 500">' . BachaListado::find($record->bacha_listado_id)->stock . ' U</span> ' . $estadoStock . '</span>';

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
                                        $material = BachaListado::find($record->bacha_listado_id);

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
                                        $material = BachaListado::find($get('bacha_listado_id'));
                                        $m2 = $get('quantity');
                                        $stock = $material->stock;

                                        $material->stock = intval($stock) - intval($m2);

                                        $material->save();
                                        // Sumar U al pedido
                                        $seleccion = BachasSelection::find($get('id'));
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
                                        $material = BachaListado::find($get('bacha_listado_id'));
                                        $m2 = $get('quantityRes');
                                        $stock = $material->stock;

                                        $material->stock = intval($stock) + intval($m2);

                                        $material->save();
                                        // Sumar U al pedido
                                        $seleccion = BachasSelection::find($get('id'));
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
                        $seleccion = $record->bacha_id;
                        $cantidad = $record->cantidad;
                        $material = BachaListado::find($seleccion);
                        $material->stock = intval($material->stock) + $cantidad;
                        $material->save();

                        // Eliminar este material de la mesada
                        $materialPedido = BachasSelection::find($record->id);
                        $materialPedido->delete();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}