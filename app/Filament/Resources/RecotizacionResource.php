<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Cliente;
use App\Models\Recotizacion;
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

    public static function canCreate(): bool {
        return false;
    }
}
