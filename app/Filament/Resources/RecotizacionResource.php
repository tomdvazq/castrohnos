<?php

namespace App\Filament\Resources;

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
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
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
    protected static ?int $navigationSort = 4;

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
                            ->label('Identificación de la mesada')
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(2),
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
                    ->columnSpan(1),

                Fieldset::make('Herramientas')
                    ->schema([
                        Select::make('estado')
                            ->label('Devolver pedido a mediciones')
                            ->options([
                                'Remedir' => '📏 Devolver a mediciones',
                            ])
                            ->helperText('En caso de que haya habido algún error en las medidas, puede seleccionar "📏 Devolver a mediciones". Tenga en cuenta que el pedido volverá a la solapa de mediciones y ya no será visualizado en recotización.')
                            ->placeholder('👌 No es necesario')
                            ->columnSpan(1),

                        DatePicker::make('remedir')
                            ->label('Fecha en la que el pedido volvió a mediciones')
                            ->helperText('Sí existe una fecha como valor actual en este campo, será debido a que el pedido ya fue remedido alguna vez. En caso de que eso suceda, haga click sobre el campo y modifique la fecha.')
                            ->timezone('America/Argentina/Buenos_Aires')
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('')
                    ->label('Estado')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(function ($record): ?string {
                        try {
                            $estado = $record->estado;
                            $result = "";

                            if ($estado === 'Medido') {
                                $result = '<span style="background-color:#27AE60; font-size:12px; padding: 3px; font-weight: bold; color: white">MEDIDO</span>';
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
                    ->label('Hace')
                    ->getStateUsing(function ($record): ?string {
                        try {
                            $estado = $record->estado;
                            $result = $record->medido;
                            $valor = "";
                            $total = "";

                            $fechaMedido = strtotime($record->medido);
                            $fechaConcluida = strtotime('+5 days' . $fechaMedido);
                            $segundos = $fechaConcluida - $fechaMedido;
                            $dias = $segundos / 86400;

                            if ($dias < -19390) {
                                $valor = '<span style="background-color:#2FC441; padding:3px; color:white; border-radius: 5px; font-size:14px;">A tiempo</span>';

                                $total = $valor . $result->diffInDays();
                            } elseif ($dias >= -19390) {
                                $valor = '<span style="background-color:#CB4335; padding:3px; color:white; border-radius: 5px; font-size:14px;">Recotizar</span>';

                                $total = $valor;
                            }

                            return $total;
                        } catch (\Exception $e) {

                            return ('Ha ocurrido un error'
                            );
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
