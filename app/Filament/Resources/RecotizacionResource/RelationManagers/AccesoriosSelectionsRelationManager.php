<?php

namespace App\Filament\Resources\RecotizacionResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\Accesorios;
use Filament\Resources\Form;
use Filament\Resources\Table;
use App\Models\AccesorioListado;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class AccesoriosSelectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'accesorios_selections';

    protected static ?string $pluralModelLabel = 'Accesorios';
    protected static ?string $modelLabel = 'accesorio';

    protected static ?string $recordTitleAttribute = 'pedido_id';

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
                        } else if (!$value){
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }    
}
