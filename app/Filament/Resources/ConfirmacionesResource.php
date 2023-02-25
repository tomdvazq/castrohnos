<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Cliente;
use Filament\Resources\Form;
use Filament\Resources\Table;
use App\Models\Confirmaciones;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ConfirmacionesResource\Pages;
use App\Filament\Resources\ConfirmacionesResource\RelationManagers;

class ConfirmacionesResource extends Resource
{
    protected static ?string $model = Confirmaciones::class;

    protected static ?string $navigationGroup = 'A confirmar';
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'A confirmar';
    protected static ?string $pluralModelLabel = 'A confirmar';
    protected static ?string $modelLabel = 'A confirmar';
    protected static ?string $slug = 'a-confirmar';
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

                Fieldset::make('Herramientas')
                    ->schema([
                        Select::make('estado')
                            ->label('Devolver pedido a mediciones')
                            ->options([
                                'Remedir' => '📏 Devolver a mediciones',
                            ])
                            ->helperText('En caso de que haya habido algún error en las medidas, puede seleccionar "📏 Devolver a mediciones". Tenga en cuenta que el pedido volverá a la solapa de mediciones y ya no será visualizado en A confirmar.')
                            ->placeholder('👌 No es necesario')
                            ->columnSpan(1),

                        DatePicker::make('remedir')
                            ->label('Fecha en la que el pedido volvió a mediciones')
                            ->helperText('Sí existe una fecha como valor actual en este campo, será debido a que el pedido ya fue remedido alguna vez. En caso de que eso suceda, haga click sobre el campo y modifique la fecha.')
                            ->timezone('America/Argentina/Buenos_Aires')
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),

                        Select::make('confirmacion')
                            ->label('Confirmación del pedido')
                            ->helperText('En caso de que el cliente haya dejado una seña marcar el pedido como "Confirmado". De lo contrario, seleccionar "No confirmado" para redireccionar la orden a la solapa "A confirmar"')
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
                    ->columns(2)
            ])
            ->columns(6);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('')
                    ->label('Estado')
                    ->getStateUsing(function ($record): ?string {
                        try {
                            $estado = $record->estado;
                            $result = "";

                            if ($estado === 'Medido') {
                                $result = '<span style="font-size:12px; padding: 3px; font-weight: bold; color: #000000">MEDIDO</span>';
                            } else {
                                $result = 'Un error ha ocurrido';
                            }

                            return $result;
                        } catch (\Exception $e) {

                            return ($record->resize_date);
                        }
                    })
                    ->formatStateUsing(function (string $state) {
                        return new HtmlString($state);
                    }),
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
                                    $actual = '<span style="background-color:#05CC2A; font-size:12px; padding: 3px; font-weight: bold; color: white; border: solid 2px #000">EN TIEMPO</span>';
                                } else {
                                    $actual = '<span style="background-color:#CB4335; font-size:12px; padding: 3px; font-weight: bold; color: white; border: solid 2px #000">RECOTIZAR</span>';
                                }

                                $total = $result->diffInDays() . " días " . $actual ;
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
                        'Medido' => 'Medido'
                    ])
                    ->default('Medido'),

                SelectFilter::make('confirmacion')
                    ->label('Confirmacion')
                    ->options([
                        'No confirmado' => 'No confirmado',
                    ])
                    ->default('No confirmado')
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConfirmaciones::route('/'),
            'create' => Pages\CreateConfirmaciones::route('/create'),
            'edit' => Pages\EditConfirmaciones::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}