<?php

namespace App\Filament\Resources\ListaClienteResource\RelationManagers;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Material;
use App\Models\PedidoPiedra;
use Filament\Resources\Form;
use Filament\Resources\Table;
use App\Models\MaterialListado;
use App\Models\PiedrasSelection;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class PedidoPiedrasRelationManager extends RelationManager
{
    protected static string $relationship = 'pedido_piedras';

    protected static ?string $pluralModelLabel = 'Piedras';
    protected static ?string $modelLabel = 'piedra';

    protected static ?string $recordTitleAttribute = 'cliente_id';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            TextInput::make('identificacion')
                ->required()
                ->maxLength(255),
            DatePicker::make('created_at')
                ->label('Pedido de piedras realizado el')
                ->timezone('America/Argentina/Buenos_Aires')
                ->displayFormat('d/m/Y')
                ->disabled()
                ->default(Carbon::now()),
            Select::make('estado')
                ->options([
                    "Retira" => "🔵 Retira",
                    "Avisa por la entrega" => "🟠 Avisa por la entrega",
                    "Entregar" => "🟢 Entregar",
                    "Reclama entrega de piedras" => "🔴 Reclama entrega de piedras"
                ]),
            DatePicker::make('entrega')
                ->label('Piedras a entregar el')
                ->timezone('America/Argentina/Buenos_Aires')
                ->displayFormat('d/m/Y')
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('identificacion'),
                TextColumn::make('entrega')
                    ->since(),
                TextColumn::make('estado'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('agregarPiedra')
                    ->label('Agregar piedra')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->action(function (PedidoPiedra $record, array $data): void {
                        $record->id;
                    })
                    ->form([
                        Fieldset::make('Selección de piedra')
                            ->schema([
                                Select::make('material_id')
                                    ->options(Material::all()->pluck('tipo', 'id')->toArray())
                                    ->label('Tipo de piedra')
                                    ->afterStateUpdated(fn (callable $set, $get) => $set('material_listado_id', null))
                                    ->reactive(),

                                Select::make('material_listado_id')
                                    ->label('Piedra')
                                    ->options(function (callable $get) {
                                        $material = Material::find($get('material_id'));

                                        if (!$material) {
                                            return MaterialListado::all()->pluck('material', 'id');
                                        }

                                        $value = $material->materialesStock->pluck('material', 'id');

                                        return $value;
                                    })
                                    ->afterStateUpdated(function ($set, $get) {
                                        $id = MaterialListado::find($get('material_listado_id'));
                                        $material = $id?->material;
                                        $stock = $id->stock;

                                        $set('material', $material);
                                        $set('stock', $stock);
                                    })
                                    ->reactive()
                                    ->searchable(),
                            ]),

                        Fieldset::make('Cantidad y stock')
                            ->schema([
                                TextInput::make('cantidad')
                                    ->label('Cantidad')
                                    ->saveRelationshipsUsing(function ($set, $get) {
                                        $material = MaterialListado::find($get('material_listado_id'));
                                        $m2 = $get('cantidad');
                                        $stock = $material->stock;

                                        $material->stock = intval($stock) - intval($m2);

                                        $material->save();
                                    })
                                    ->numeric()
                                    ->suffix('m²'),

                                TextInput::make('stock')
                                    ->label('Stock de la piedra')
                                    ->numeric()
                                    ->suffix('m²'),
                            ]),

                        TextInput::make('material')
                            ->label('')
                            ->lazy()
                            ->columnSpan('full')
                            ->extraAttributes(['style' => 'display: none'])
                            ->saveRelationshipsUsing(function ($get, $record) {

                                $lastSelectionId = PiedrasSelection::all()->last()->id;
                                $newSelectionId = $lastSelectionId + 1;

                                $result = DB::table('piedras_selections')->insert([
                                    'id' => $newSelectionId,
                                    'pedido_id' => $record->id,
                                    'material_id' => $get('material_id'),
                                    'material_listado_id' => $get('material_listado_id'),
                                    'cantidad' => $get('cantidad'),
                                    'material' => $get('material'),
                                ]);

                                return $result;
                            })
                    ]),
                ])
                ->icon('heroicon-o-plus-circle')
                ->color('success'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }    
}
