<?php

namespace App\Filament\Resources;

use Exception;
use Filament\Forms;
use Filament\Tables;
use App\Models\Corte;
use App\Models\Pedido;
use App\Models\Cliente;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use App\Models\MaterialesSelection;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\CorteResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CorteResource\RelationManagers;

class CorteResource extends Resource
{
    protected static ?string $model = Corte::class;

    protected static ?string $navigationGroup = 'Corte';
    protected static ?string $navigationIcon = 'heroicon-o-scissors';
    protected static ?string $navigationLabel = 'Corte';
    protected static ?string $pluralModelLabel = 'Corte';
    protected static ?string $modelLabel = 'Corte';
    protected static ?string $slug = 'corte';
    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Esta mesada')
                    ->schema([
                        Select::make('cliente_id')
                            ->label('Pertenece a')
                            ->disabled()
                            ->options(Cliente::all()->pluck('nombre', 'id')->toArray()),

                        DatePicker::make('created_at')
                            ->label('Fue ordenada el')
                            ->timezone('America/Argentina/Buenos_Aires')
                            ->displayFormat('d/m/Y')
                            ->disabled(),

                        TextInput::make('identificacion')
                            ->label('Identificaci??n de la mesada')
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(2),
                Fieldset::make('Estado')
                    ->schema([
                        Select::make('estado')
                            ->label('Actualmente en')
                            ->options([
                                // 'Medir' => 'Medir',
                                // 'Avisa para medir' => 'Avisa para medir',
                                // 'Remedir' => 'Remedir',
                                // 'Reclama medici??n' => 'Reclama medici??n',
                                'Corte' => '???? Corte',
                                'En taller' => '??????????? En taller',
                                'Cortado' => '???? Cortado',
                                'Entregas' => '???? Entregas'
                            ])
                            ->columnSpan('full'),

                        DatePicker::make('entrega')
                            ->label('A entregar el')
                            ->timezone('America/Argentina/Buenos_Aires')
                            ->displayFormat('d/m/Y')
                            ->columnSpanFull()
                    ])
                    ->columnSpan(1),

                Fieldset::make('Herramientas')
                    ->schema([
                        Section::make('??? Confirmaci??n de la orden')
                            ->schema([
                                Select::make('confirmacion')
                                    ->label('Confirmaci??n del pedido')
                                    ->helperText('S?? la orden no ha recibido una se??a a??n, marcarla como "No confirmado". Autom??ticamente el pedido ser?? redireccionado a la solapa "A confirmar".')
                                    ->options([
                                        "No seleccionado" => '???? No seleccionado',
                                        "No confirmado" => '??? No confirmado',
                                        "Confirmado" => '???? Confirmado'
                                    ])
                                    ->default('No seleccionado'),

                                TextInput::make('se??a')
                                    ->label('Valor de la se??a')
                                    ->helperText('En caso de que el pedido haya sido marcado como "Confirmado" aclarar cuanto dinero dej?? de se??a. Tenga en cuenta que este campo es un tipo de dato num??rico y no permite letras ni signos especiales.')
                                    ->mask(fn (TextInput\Mask $mask) => $mask->money(prefix: '$ ', thousandsSeparator: ',', decimalPlaces: 2, isSigned: false))
                            ])
                            ->columns(1)
                            ->collapsed()
                            ->columnSpan(1),
                        Section::make(function ($record) {
                            $res = '???? Finanzas del pedido de ' . $record->clientes->nombre;

                            return new HtmlString($res);
                        })
                            ->schema([
                                TextInput::make('se??a')
                                    ->label(function ($record) {
                                        $res = 'Se??a de <b>' . $record->identificacion . '</b>';

                                        return new HtmlString($res);
                                    })
                                    ->disabled()
                                    ->helperText('')
                                    ->mask(fn (TextInput\Mask $mask) => $mask->money(prefix: '$ ', thousandsSeparator: ',', decimalPlaces: 2, isSigned: false)),
                                TextInput::make('total')
                                    ->label(function ($record) {
                                        $res = 'Total actual de <b>' . $record->identificacion . '</b>';

                                        return new HtmlString($res);
                                    })
                                    ->afterStateHydrated(function ($set, $get) {
                                        $id = Pedido::find($get('id'));
                                        $se??a = $id?->se??a;

                                        $set('total', $se??a);
                                    })
                                    ->mask(fn (TextInput\Mask $mask) => $mask->money(prefix: '$ ', thousandsSeparator: ',', decimalPlaces: 2, isSigned: false)),
                            ])
                            ->columns(1)
                            ->collapsed()
                            ->columnSpan(1),
                    ])
                    ->columnSpan(3)
                    ->columns(2)
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('entrega')
                    ->label('Entrega')
                    ->sortable()
                    ->since()
                    ->getStateUsing(function ($record): ?string {
                        try {
                            $hoy = strtotime('now');
                            $delivery = strtotime($record->entrega);
                            $segundos = $hoy - $delivery;
                            $dias = $segundos / 86400;
                            $result = "";

                            if ($dias > -7 and $dias < 0) {
                                $result = "???? En ";
                            } elseif ($dias < -7) {
                                $result = "???? En ";
                            } else {
                                $result = "???? Hace ";
                            }

                            if ($record->entrega === null) {
                                return '??? No definido';
                            }


                            return $result . " " . $record->entrega->diffInDays() . " d??as";
                        } catch (Exception $e) {
                            return $record->entrega;
                        }
                    })
                    ->formatStateUsing(function (string $state) {
                        return new HtmlString($state);
                    }),
                TextColumn::make('clientes.nombre')
                    ->label('Cliente')
                    ->searchable(),
                TextColumn::make('clientes.contacto')
                    ->label('Contacto')
                    ->searchable(),
                TextColumn::make('identificacion')
                    ->label('Identificaci??n del pedido'),
                TextColumn::make('materiales_selections.material')
                    ->label('Material')
                    ->searchable(),
                TextColumn::make('bacha'),
                TextColumn::make('bacha_modelo')
                    ->label('Modelo de bacha'),
                TextColumn::make('accesorio'),
                TextColumn::make('estado')
                    ->label('Estado')
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'Corte' => 'Corte',
                        'En taller' => 'En taller',
                        'Cortado' => 'Cortado',
                    ])
                    ->multiple()
                    ->default((['Corte', 'En taller', 'Cortado'])),

                SelectFilter::make('confirmacion')
                    ->label('Confirmaci??n')
                    ->options([
                        'Confirmado' => 'Confirmado'
                    ])
                    ->default('Confirmado'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MaterialesSelectionsRelationManager::class,
            RelationManagers\ClientesRelationManager::class,
            RelationManagers\ArchivosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCortes::route('/'),
            'create' => Pages\CreateCorte::route('/create'),
            'edit' => Pages\EditCorte::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}

