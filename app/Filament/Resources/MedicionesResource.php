<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Cliente;
use App\Models\Mediciones;
use Filament\Resources\Form;
use Filament\Resources\Table;
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
use App\Filament\Resources\MedicionesResource\Pages;
use Awcodes\FilamentBadgeableColumn\Components\Badge;
use App\Filament\Resources\MedicionesResource\RelationManagers;
use Awcodes\FilamentBadgeableColumn\Components\BadgeableColumn;

class MedicionesResource extends Resource
{
    protected static ?string $model = Mediciones::class;

    protected static ?string $navigationGroup = 'Mediciones';
    protected static ?string $navigationIcon = 'heroicon-o-pencil';
    protected static ?string $navigationLabel = 'Mediciones';
    protected static ?string $pluralModelLabel = 'Mediciones';
    protected static ?string $modelLabel = 'medición';
    protected static ?string $slug = 'mediciones';
    protected static ?int $navigationSort = 5;

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

                Fieldset::make('Herramientas del medidor')
                    ->schema([
                        Select::make('estado')
                            ->options([
                                'Medir' => '🟢 Medir',
                                'Avisa para medir' => '🔵 Avisa para medir',
                                'Remedir' => '🟣 Remedir',
                                'Reclama medición' => '🟠 Reclama medición',
                                'Medido' => '✅ Medido',
                                // 'Medida del cliente' => 'Medida del cliente',
                                // 'Corte' => 'Corte',
                                // 'En taller' => 'En taller',
                                // 'Cortado' => 'Cortado',
                                // 'Entregas' => 'Entregas'
                            ])
                            ->columnSpan('full'),

                            DatePicker::make('remedir')
                            ->label('Remedir')
                            ->timezone('America/Argentina/Buenos_Aires')
                            ->displayFormat('d/m/Y'),

                            DatePicker::make('avisa')
                            ->label('Avisa')
                            ->timezone('America/Argentina/Buenos_Aires')
                            ->displayFormat('d/m/Y'),
                        ])
                        ->columnSpan(1),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                BadgeableColumn::make('estado')
                    ->label('Estado')
                    ->searchable()
                    ->sortable()
                    ->badges([
                        Badge::make('medir')
                            ->label('🟢')
                            ->textColor('white')
                            ->color('#')
                            ->visible(fn ($record): ?string => $record->estado === 'Medir'),

                        Badge::make('avisa')
                            ->label('🔵')
                            ->textColor('white')
                            ->color('#')
                            ->visible(fn ($record): bool => $record->estado === 'Avisa para medir'),

                        Badge::make('remedir')
                            ->label('🟣')
                            ->textColor('white')
                            ->color('#')
                            ->visible(fn ($record): bool => $record->estado === 'Remedir'),

                        Badge::make('reclama')
                            ->label('🟠')
                            ->textColor('white')
                            ->color('#')
                            ->visible(fn ($record): bool => $record->estado === 'Reclama medición'),
                    ])
                    ->getStateUsing(function ($record): ?string {
                        try {
                            $estado = $record->estado;
                            $result = "";

                            if ($estado === 'Medir') {
                                $result = '<span style="background-color:#27AE60; font-size:12px; padding: 3px; font-weight: bold; color: white">MEDIR</span>';
                            } elseif ($estado === 'Reclama medición') {
                                $result = '<span style="background-color:#CB4335; font-size:12px; padding: 3px; font-weight: bold; color: white">RECLAMA MEDICIÓN</span>';   
                            } elseif ($estado === 'Remedir') {
                                $result = '<span style="background-color:#992FC4; font-size:12px; padding: 3px; font-weight: bold; color: white">REMEDIR</span>';
                            } elseif ($estado === 'Avisa para medir') {
                                $result = '<span style="background-color:#11D3F1; font-size:12px; padding: 3px; font-weight: bold; color: white">AVISA PARA MEDIR</span>';
                            }

                            return $result;
                        } catch (\Exception $e) {

                            return ($record->resize_date);
                        }
                    })
                    ->formatStateUsing(function (string $state) {
                        return new HtmlString($state);
                    }),
                TextColumn::make('created_at')
                    ->label('Lapsos')
                    ->since()
                    ->getStateUsing(function ($record): ?string {
                        try {
                            $estado = $record->estado;
                            $result = "";
                            $total = "";

                            if ($estado === 'Medir' || $estado === 'Reclama medición') {
                                $result = $record->created_at;

                                $actual = "";
                                $hoy = strtotime('now');
                                $pasadoDeFecha = strtotime($record->created_at);
                                $segundos = $hoy - $pasadoDeFecha;
                                $dias = $segundos / 86400;
                                
                                if ($dias < 6){
                                    $actual = "🟢 ";
                                } else {
                                    $actual = "🔴 ";
                                }

                                $total = $actual . " Hace " . $result->diffInDays() . " días";
                            } elseif ($estado === 'Remedir') {
                                $result = $record->remedir;

                                $actual = "";
                                $hoy = strtotime('now');
                                $pasadoDeFecha = strtotime($record->remedir);
                                $segundos = $hoy - $pasadoDeFecha;
                                $dias = $segundos / 86400;
                                
                                if ($dias < 6){
                                    $actual = "🟢 ";
                                } else {
                                    $actual = "🔴 ";
                                }

                                $total = $actual . " Hace " . $result->diffInDays() . " días";
                            } elseif ($estado === 'Avisa para medir') {
                                $result = $record->avisa;

                                $actual = "";
                                $hoy = strtotime('now');
                                $pasadoDeFecha = strtotime($record->avisa);
                                $segundos = $hoy - $pasadoDeFecha;
                                $dias = $segundos / 86400;
                                
                                if ($dias < 120){
                                    $actual = "🟢 ";
                                } else {
                                    $actual = "<div style='display: flex; flex-direction: row; justify-content: center; align-items: center;'>🔴 <b style='font-size: 10px'>RECOTIZAR</b></div>";
                                }

                                $total = $actual . " Hace " . $result->diffInDays() . " días";
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
                    ->label('Contacto'),
                TextColumn::make('identificacion')
                    ->label('Identificación del pedido'),
                TextColumn::make('materiales_selections.material')
                    ->label('Material')
                    ->searchable(),
                TextColumn::make('bacha'),
                TextColumn::make('bacha_modelo')
                    ->label('Modelo de bacha'),
                TextColumn::make('accesorio'),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'Medir' => 'Medir',
                        'Avisa para medir' => 'Avisa para medir',
                        'Remedir' => 'Remedir',
                        'Reclama medición' => 'Reclama medición',
                    ])
                ->multiple()
                ->default((['Medir', 'Avisa para medir', 'Remedir', 'Reclama medición'])),
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
            'index' => Pages\ListMediciones::route('/'),
            'create' => Pages\CreateMediciones::route('/create'),
            'edit' => Pages\EditMediciones::route('/{record}/edit'),
        ];
    }
    
    public static function canCreate(): bool
    {
        return false;
    }
}
