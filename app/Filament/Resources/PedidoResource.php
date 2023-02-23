<?php

namespace App\Filament\Resources;

use Exception;
use Filament\Forms;
use Filament\Tables;
use App\Models\Pedido;
use App\Models\Cliente;
use Filament\Resources\Form;
use Filament\Resources\Table;
use App\Models\MaterialListado;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use App\Models\MaterialesSelection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Actions\Action;
use App\Filament\Resources\PedidoResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationGroup;
use App\Filament\Resources\PedidoResource\RelationManagers;

class PedidoResource extends Resource
{
    protected static ?string $model = Pedido::class;

    protected static ?string $navigationGroup = 'Pedidos';
    protected static ?string $navigationIcon = 'heroicon-o-collection';
    protected static ?int $navigationSort = 1;


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
                            'Medido' => 'Medido',
                            'Medida del cliente' => 'Medida del cliente',
                            'Corte' => 'Corte',
                            'En taller' => 'En taller',
                            'Cortado' => 'Cortado',
                            'Entregas' => 'Entregas'
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
                TextColumn::make('entrega')
                    ->label('Entrega')
                    ->sortable()
                    ->since()
                    ->getStateUsing(function ($record): ?string {
                        try{
                            $hoy = strtotime('now');
                            $delivery = strtotime($record->entrega);
                            $segundos = $hoy - $delivery;
                            $dias = $segundos / 86400;
                            $result = "";

                            if($dias > -7 and $dias < 0){
                                $result = "🟡 En ";
                            } elseif ($dias < -7){
                                $result = "🟢 En ";
                            } else {
                                $result = "🔴 Hace ";
                            }


                            return $result . " " . $record->entrega->diffInDays() . " días";


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
                        'Medido' => 'Medido',
                        'Medida del cliente' => 'Medida del cliente',
                        'Corte' => 'Corte',
                        'En taller' => 'En taller',
                        'Cortado' => 'Cortado',
                        'Entregas' => 'Entregas'
                    ])
                ->multiple()
                ->default((['Medido', 'Medida del cliente', 'Corte', 'En taller', 'Cortado', 'Entregas'])),
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
            'index' => Pages\ListPedidos::route('/'),
            'create' => Pages\CreatePedido::route('/create'),
            'edit' => Pages\EditPedido::route('/{record}/edit'),
        ];
    }
    
    public static function canCreate(): bool
    {
        return false;
    }
}
