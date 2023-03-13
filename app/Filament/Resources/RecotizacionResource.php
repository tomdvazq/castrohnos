<?php

namespace App\Filament\Resources;

use Closure;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\Recotizacion;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Exists;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TernaryFilter;
use SebastianBergmann\RecursionContext\Exception;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Awcodes\FilamentBadgeableColumn\Components\Badge;
use App\Filament\Resources\RecotizacionResource\Pages;
use Awcodes\FilamentBadgeableColumn\Components\BadgeableColumn;
use App\Filament\Resources\RecotizacionResource\RelationManagers;

class RecotizacionResource extends Resource
{
    protected static ?string $model = Recotizacion::class;

    protected static ?string $navigationGroup = 'Recotización';
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Recotización';
    protected static ?string $pluralModelLabel = 'Recotización';
    protected static ?string $modelLabel = 'recotización';
    protected static ?string $slug = 'recotizacion';
    protected static ?int $navigationSort = 6;

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
                            ->label('Identificación de la mesada'),

                        DatePicker::make('medido')
                            ->label('Fue medida el')
                            ->timezone('America/Argentina/Buenos_Aires')
                            ->displayFormat('d/m/Y')
                            ->disabled(),
                    ])
                    ->columnSpan(4),
                Fieldset::make('Estado')
                    ->schema([
                        Select::make('estado')
                            ->label('Actualmente en')
                            ->options([
                                // 'Medir' => 'Medir',
                                // 'Avisa para medir' => 'Avisa para medir',
                                // 'Remedir' => 'Remedir',
                                // 'Reclama medición' => 'Reclama medición',
                                'Medido' => '✅ Medido',
                                'Medida del cliente' => '📐 Medida del cliente',
                                'Corte' => '🪓 Corte',
                                'En taller' => '👩‍🔧 En taller',
                                'Cortado' => '👍 Cortado',
                                'Entregas' => '🚚 Entregas'
                            ])
                            ->columnSpan('full'),

                        DatePicker::make('entrega')
                            ->label('A entregar el')
                            ->timezone('America/Argentina/Buenos_Aires')
                            ->displayFormat('d/m/Y')
                            ->columnSpanFull()
                    ])
                    ->columnSpan(2),
                Fieldset::make('Herramientas del medidor')
                    ->schema([
                        Section::make('📏 Devolver a mediciones')
                            ->schema([
                                Select::make('estado')
                                    ->label('Devolver pedido a mediciones')
                                    ->options([
                                        'Remedir' => '📏 Devolver a mediciones',
                                    ])
                                    ->helperText('En caso de que haya habido algún error en las medidas, puede seleccionar "📏 Devolver a mediciones". Automáticamente el pedido será redireccionado a "Mediciones".')
                                    ->placeholder('👌 No es necesario')
                                    ->columnSpan(1),

                                DatePicker::make('remedir')
                                    ->label('Fecha en la que el pedido volvió a mediciones')
                                    ->helperText('Sí existe una fecha como valor actual en este campo, será debido a que el pedido ya fue remedido alguna vez. En caso de que eso suceda, haga click sobre el campo y modifique la fecha.')
                                    ->timezone('America/Argentina/Buenos_Aires')
                                    ->displayFormat('d/m/Y')
                                    ->columnSpan(1),


                            ])
                            ->columns(1)
                            ->collapsed()
                            ->columnSpan(3),

                        Section::make('✅ Confirmación de la orden')
                            ->schema([
                                Select::make('confirmacion')
                                    ->label('Confirmación del pedido')
                                    ->helperText('Sí la orden no ha recibido una seña aún, marcarla como "No confirmado". Automáticamente el pedido será redireccionado a la solapa "A confirmar".')
                                    ->options([
                                        "No seleccionado" => '🔔 No seleccionado',
                                        "No confirmado" => '❌ No confirmado',
                                        "Confirmado" => '🤩 Confirmado'
                                    ])
                                    ->default('No seleccionado'),

                                TextInput::make('seña')
                                    ->label('Valor de la seña')
                                    ->helperText('En caso de que el pedido haya sido marcado como "Confirmado" aclarar cuanto dinero dejó de seña. Tenga en cuenta que este campo es un tipo de dato numérico y no permite letras ni signos especiales.')
                                    ->mask(fn (TextInput\Mask $mask) => $mask->money(prefix: '$ ', thousandsSeparator: ',', decimalPlaces: 2, isSigned: false))
                            ])
                            ->columns(1)
                            ->collapsed()
                            ->columnSpan(3)
                    ])
                    ->columnSpan(6)
                    ->columns(6),
                // Section::make(function($record){
                //     $res = '💹 Finanzas del pedido de ' . $record->clientes->nombre;

                //     return new HtmlString($res);
                //     })
                //     ->schema([
                //         TextInput::make('seña')
                //             ->label(function($record){
                //                 $res = 'Seña de <b>' . $record->identificacion . '</b>';

                //                 return new HtmlString($res);
                //             })
                //             ->disabled()
                //             ->helperText('')
                //             ->mask(fn (TextInput\Mask $mask) => $mask->money(prefix: '$ ', thousandsSeparator: ',', decimalPlaces: 2, isSigned: false)),
                //         TextInput::make('total')
                //             ->label(function($record){
                //                 $res = 'Total actual de <b>' . $record->identificacion . '</b>';

                //                 return new HtmlString($res);
                //             })
                //             ->afterStateHydrated(function ($set, $get){
                //                 $id = Pedido::find($get('id'));
                //                 $seña = $id?->seña;
                                
                //                 $set('total', $seña);
                //             })
                //             ->mask(fn (TextInput\Mask $mask) => $mask->money(prefix: '$ ', thousandsSeparator: ',', decimalPlaces: 2, isSigned: false)),
                //     ])
                //     ->collapsed()
                //     ->columnSpan(4)
            ])
            ->columns(6);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('')
                //     ->label('Estado')
                //     ->getStateUsing(function ($record): ?string {
                //         try {
                //             $estado = $record->estado;
                //             $result = "";

                //             if ($estado === 'Medido') {
                //                 $result = '<span style="font-size:12px; padding: 3px; font-weight: bold; color: #000000">MEDIDO</span>';
                //             } else {
                //                 $result = 'Un error ha ocurrido';
                //             }

                //             return $result;
                //         } catch (\Exception $e) {

                //             return ($record->resize_date);
                //         }
                //     })
                //     ->formatStateUsing(function (string $state) {
                //         return new HtmlString($state);
                //     }),
                TextColumn::make('medido')
                    ->label('Medido hace')
                    ->since()
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(function ($record): ?string {
                        try {
                            $estado = $record->estado;
                            $result = "";
                            $total = "";

                            if ($estado === 'Medido') {
                                $result = $record->medido;

                                $actual = "";
                                $hoy = strtotime('now');
                                $pasadoDeFecha = strtotime($record->medido);
                                $segundos = $hoy - $pasadoDeFecha;
                                $dias = $segundos / 86400;

                                if ($dias < 6) {
                                    $actual = '<span style="background-color:#05CC2A; font-size:10px; padding: 3px; font-weight: bold; color: white; border: solid 2px #000">EN TIEMPO</span>';
                                } else {
                                    $actual = '<span style="background-color:#CB4335; font-size:10px; padding: 3px; font-weight: bold; color: white; border: solid 2px #000">RECOTIZAR</span>';
                                }

                                $total = $result->diffInDays() . " días " . $actual;
                            }


                            return $total;
                        } catch (\Exception $e) {

                            return 'Ha ocurrido un error';
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
                    ->label('Identificación del pedido'),
                TextColumn::make('materiales_selections.material')
                    ->label('Material')
                    ->searchable(),
                TextColumn::make('bachas_selections.tipo_bacha')
                    ->label('Bacha'),
                TextColumn::make('bachas_selections.material')
                    ->label('Modelo de bacha'),
                TextColumn::make('accesorios_selections.material')
                    ->label('Accesorios'),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'Medido' => 'Medido'
                    ])
                    ->default('Medido'),

                SelectFilter::make('confirmacion')
                    ->label('Confirmación')
                    ->options([
                        'No seleccionado' => 'No seleccionado',
                        'Confirmado' => 'Confirmado'
                    ])
                    ->multiple()
                    ->default((['No seleccionado', 'Confirmado'])),
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
            RelationManagers\BachasSelectionsRelationManager::class,
            RelationManagers\AccesoriosSelectionsRelationManager::class,
            RelationManagers\ClientesRelationManager::class,
            RelationManagers\ArchivosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecotizacions::route('/'),
            'create' => Pages\CreateRecotizacion::route('/create'),
            'edit' => Pages\EditRecotizacion::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
