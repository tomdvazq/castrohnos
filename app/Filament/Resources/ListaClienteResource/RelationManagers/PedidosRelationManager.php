<?php

namespace App\Filament\Resources\ListaClienteResource\RelationManagers;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput\Mask;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class PedidosRelationManager extends RelationManager
{
    protected static string $relationship = 'pedidos';

    protected static ?string $pluralModelLabel = 'mesadas';
    protected static ?string $modelLabel = 'mesada';

    protected static ?string $recordTitleAttribute = 'cliente_id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('identificacion')
                    ->required()
                    ->label('Identificación del pedido'),

                DatePicker::make('created_at')
                    ->label('Pedido realizado el')
                    ->default(Carbon::now())
                    ->timezone('America/Argentina/Buenos_Aires')
                    ->displayFormat('d/m/Y'),

                Select::make('estado')
                    ->label('Estado del pedido')
                    ->options([
                        'Medir' => '🟢 Medir',
                        'Avisa para medir' => '🔵 Avisa para medir',
                        'Remedir' => '🟣 Remedir',
                        'Reclama medición' => '🟠 Reclama medición',
                        'Medido' => '✅ Medido',
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

                Select::make('confirmacion')
                    ->label('Confirmación del pedido')
                    ->helperText('En caso de que el cliente haya dejado una seña marcar el pedido como "Confirmado". De lo contrario, seleccionar "No confirmado" para redireccionar la orden a la solapa "A confirmar"')
                    ->options([
                        "No confirmado" => '❌ No confirmado',
                        "Confirmado" => '🤩 Confirmado'
                    ])
                    ->default("Confirmado"),

                TextInput::make('seña')
                    ->label('Valor de la seña')
                    ->helperText('En caso de que el pedido haya sido marcado como "Confirmado" aclarar cuanto dinero dejó de seña. Tenga en cuenta que este campo es un tipo de dato numérico y no permite letras ni signos especiales.')
                    ->mask(fn (Mask $mask) => $mask->money(prefix: '$ ', thousandsSeparator: ',', decimalPlaces: 2, isSigned: false))

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
                    ->label('Estado del pedido'),
                TextColumn::make('confirmacion')
                    ->label('Confirmación'),
                TextColumn::make('seña')
                    ->money('ars')
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
