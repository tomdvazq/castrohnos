<?php

namespace App\Filament\Resources\NuevoResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
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

                Select::make('estado')
                    ->label('Estado del pedido')
                    ->options([
                        'Medir' => '🟢 Medir',
                        'Avisa para medir' => '🔵 Avisa para medir',
                        'Remedir' => '🟣 Remedir',
                        'Reclama medición' => '🟠 Reclama medición',
                        // 'Medido' => '✅ Medido',
                        // 'Medida del cliente' => 'Medida del cliente',
                        // 'Corte' => 'Corte',
                        // 'En taller' => 'En taller',
                        // 'Cortado' => 'Cortado',
                        // 'Entregas' => 'Entregas'
                    ])
                    ->default('Medir')
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
