<?php

namespace App\Filament\Resources\ClienteResource\RelationManagers;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Cliente;
use Filament\Resources\Form;
use Filament\Resources\Table;
use App\Models\MaterialListado;
use App\Models\MaterialesSelection;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class PedidosRelationManager extends RelationManager
{
    protected static string $relationship = 'pedidos';

    protected static ?string $recordTitleAttribute = 'cliente_id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('identificacion')
                    ->label('Identificación del pedido'),

                DatePicker::make('created_at')
                    ->label('Pedido realizado el')
                    ->default(Carbon::now())
                    ->timezone('America/Argentina/Buenos_Aires')
                    ->displayFormat('d/m/Y'),

                Select::make('estado')
                    ->label('Estado del pedido')
                    ->options([
                        // 'Medir' => 'Medir',
                        // 'Avisa para medir' => 'Avisa para medir',
                        // 'Remedir' => 'Remedir',
                        // 'Reclama medición' => 'Reclama medición',
                        'Medida del cliente' => '📐 Medida del cliente',
                        'Corte' => '🪓 Corte',
                        'En taller' => '👩‍🔧 En taller',
                        'Cortado' => '👍 Cortado',
                        'Entregas' => '🚚 Entregas'
                    ])
                    ->default('Medida del cliente'),

                DatePicker::make('entrega')
                    ->label('Pedido a entregar el')
                    ->timezone('America/Argentina/Buenos_Aires')
                    ->displayFormat('d/m/Y'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Código del pedido'),
                TextColumn::make('identificacion')
                    ->label('Identificación del pedido'),
                TextColumn::make('estado')
                    ->label('Estado del pedido')
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\MaterialesSelectionsRelationManager::class,
        ];
    }
}
