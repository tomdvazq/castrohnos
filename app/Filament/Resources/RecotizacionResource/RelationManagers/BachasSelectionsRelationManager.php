<?php

namespace App\Filament\Resources\RecotizacionResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\Bacha;
use App\Models\BachaListado;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class BachasSelectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'bachas_selections';

    protected static ?string $pluralModelLabel = 'Bachas';
    protected static ?string $modelLabel = 'bacha';

    protected static ?string $recordTitleAttribute = 'pedido_id';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Fieldset::make('Selección de bacha')
                ->schema([
                    Select::make('bacha_id')
                        ->options(Bacha::all()->pluck('marca', 'id')->toArray())
                        ->label('Tipo de bacha')
                        ->afterStateUpdated(fn (callable $set, $get) => $set('bacha_listados_id', null))
                        ->reactive(),

                    Select::make('bacha_listados_id')
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
                            $id = BachaListado::find($get('bacha_listados_id'));
                            $bacha = $id?->linea;
                            $stock = $id->stock;

                            $set('material', $bacha);
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
                            $bacha = BachaListado::find($get('bacha_listados_id'));
                            $m2 = $get('cantidad');
                            $stock = $bacha->stock;

                            $bacha->stock = intval($stock) - intval($m2);

                            $bacha->save();
                        })
                        ->numeric()
                        ->suffix('m²'),

                    TextInput::make('stock')
                        ->label('Stock de esta bacha')
                        ->numeric()
                        ->suffix('m²'),
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

            TextColumn::make('bacha_listados_id')
                ->label('Línea')
                ->getStateUsing(function ($record) {
                    $id = $record->bacha_listados_id;
                    $bacha_listados_id = collect(BachaListado::find($id, 'linea'));
                    $resultado = "";

                    foreach ($bacha_listados_id as $key => $value) {
                        if ($value) {
                            $resultado .= $value;
                        }
                    }

                    return $resultado;
                }),

            TextColumn::make('modelo')
                ->label('Modelo')
                ->getStateUsing(function ($record) {
                    $id = $record->bacha_listados_id;
                    $bacha_listados_id = collect(BachaListado::find($id, 'modelo'));
                    $resultado = "";

                    foreach ($bacha_listados_id as $key => $value) {
                        if ($value) {
                            $resultado .= $value;
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
