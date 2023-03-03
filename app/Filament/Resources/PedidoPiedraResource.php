<?php

namespace App\Filament\Resources;

use Exception;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Cliente;
use App\Models\PedidoPiedra;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput\Mask;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PedidoPiedraResource\Pages;
use App\Filament\Resources\PedidoPiedraResource\RelationManagers;

class PedidoPiedraResource extends Resource
{
    protected static ?string $model = PedidoPiedra::class;

    protected static ?string $navigationGroup = 'Piedras';
    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';
    protected static ?string $navigationLabel = 'Piedras';
    protected static ?string $pluralModelLabel = 'Piedras';
    protected static ?string $modelLabel = 'piedra';
    protected static ?string $slug = 'piedras';
    protected static ?int $navigationSort = 4;


    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Fieldset::make('Este pedido de piedras')
                ->schema([
                    Select::make('cliente_id')
                        ->label('Pertenece a')
                        ->disabled()
                        ->options(Cliente::all()->pluck('nombre', 'id')->toArray()),

                    DatePicker::make('created_at')
                        ->label('Fue ordenado el')
                        ->timezone('America/Argentina/Buenos_Aires')
                        ->displayFormat('d/m/Y')
                        ->disabled(),

                    TextInput::make('identificacion')
                        ->label('Identificación del pedido')
                        ->columnSpanFull(),
                ])
                ->columnSpan(2),
            Fieldset::make('Estado')
                ->schema([
                    Select::make('estado')
                        ->label('Actualmente en')
                        ->options([
                            "Retira" => "🔵 Retira",
                            "Avisa por la entrega" => "🟠 Avisa por la entrega",
                            "Entregar" => "🟢 Entregar",
                            "Reclama entrega de piedras" => "🔴 Reclama entrega de piedras"
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
                        try {
                            $hoy = strtotime('now');
                            $delivery = strtotime($record->entrega);
                            $segundos = $hoy - $delivery;
                            $dias = $segundos / 86400;
                            $result = "";

                            if ($dias > -7 and $dias < 0) {
                                $result = "🟡 En ";
                            } elseif ($dias < -7) {
                                $result = "🟢 En ";
                            } else {
                                $result = "🔴 Hace ";
                            }

                            if ($record->entrega === null) {
                                return '❌ No definido';
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
                TextColumn::make('identificacion'),
                TextColumn::make('estado'),
                TextColumn::make('seña') 
                    ->money('ars'),
            ])
            ->filters([
                //
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
            RelationManagers\PiedrasSelectionsRelationManager::class,
            RelationManagers\ClientesRelationManager::class,
            RelationManagers\ArchivosRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPedidoPiedras::route('/'),
            'create' => Pages\CreatePedidoPiedra::route('/create'),
            'edit' => Pages\EditPedidoPiedra::route('/{record}/edit'),
        ];
    }    

    public static function canCreate(): bool
    {
        return false;
    }
}
