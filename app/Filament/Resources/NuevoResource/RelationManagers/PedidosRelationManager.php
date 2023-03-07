<?php

namespace App\Filament\Resources\NuevoResource\RelationManagers;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Pedido;
use App\Models\Material;
use Filament\Resources\Form;
use Filament\Resources\Table;
use App\Models\MaterialListado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use App\Models\MaterialesSelection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
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
                Section::make('Nueva mesada')
                    ->description('')
                    ->schema([
                    TextInput::make('identificacion')
                        ->label('Identificación de la mesada')
                        ->required(),
    
                    Select::make('estado')
                        ->label('Estado de la mesada')
                        ->options([
                            'Medir' => '🟢 Medir',
                            'Avisa para medir' => '🔵 Avisa para medir',
                            'Remedir' => '🟣 Remedir',
                            'Reclama medición' => '🟠 Reclama medición',
                        ])
                        ->default('Medida del cliente'),
    
                        DatePicker::make('created_at')
                            ->label('Pedido realizado el')
                            ->helperText('⏲ Por defecto, será la fecha de hoy')
                            ->default(Carbon::now())
                            ->timezone('America/Argentina/Buenos_Aires')
                            ->displayFormat('d/m/Y'),
                ])
                ->collapsed()
                ->columns(3),

                Section::make('Medidas del cliente')
                    ->description('')
                    ->schema([
                        TextInput::make('identificacion')
                            ->label('Identificación de la mesada')
                            ->required(),

                        DatePicker::make('created_at')
                            ->label('Pedido realizado el')
                            ->helperText('⏲ Por defecto, será la fecha de hoy')
                            ->default(Carbon::now())
                            ->timezone('America/Argentina/Buenos_Aires')
                            ->displayFormat('d/m/Y'),

                        Select::make('estado')
                            ->label('Estado del pedido')
                            ->options([
                                'Medida del cliente' => '📐 Medida del cliente',
                                'Corte' => '🪓 Corte',
                                'En taller' => '👩‍🔧 En taller',
                                'Cortado' => '👍 Cortado',
                                'Entregas' => '🚚 Entregas'
                            ]),

                        DatePicker::make('entrega')
                            ->label('Pedido a entregar el')
                            ->helperText('🚚 Fecha aproximada para entregar la mesada')
                            ->timezone('America/Argentina/Buenos_Aires')
                            ->displayFormat('d/m/Y'),

                        
                        Select::make('confirmacion')
                            ->label('Confirmación del pedido')
                            ->helperText('✅ Por favor, confirme la mesada')
                            ->options([
                                "No seleccionado" => "🤔 No seleccionado",
                                "Confirmado" => '🤩 Confirmado'
                            ])
                            ->disablePlaceholderSelection()
                            ->default("No seleccionado"),
                    ])
                    ->columns(2)
                    ->collapsed(),

                Section::make('Materiales')
                    ->schema([
                        Select::make('material_id')
                        ->options(Material::all()->pluck('tipo', 'id')->toArray())
                        ->label('Tipo de material')
                        ->afterStateUpdated(fn (callable $set, $get) => $set('material_listado_id', null))
                        ->saveRelationshipsUsing(function ($set, $get) {
                            $id = MaterialesSelection::create(['id']);
                            $pedido = MaterialesSelection::create(['pedido_id', $set(1)]);
                        })
                        ->saveRelationshipsUsing(function($get, $set) {


                            DB::table('materiales_selections')->insert([
                                'id' => '1500',
                                'pedido_id' => '10',
                                'material_id' => $get('material_id'),
                                'material_listado_id' => $get('material_listado_id'),
                                'cantidad' => $get('cantidad'),
                                'material' => $get('material'),
                            ]);
                        })
                        ->reactive(),
    
                    Select::make('material_listado_id')
                        ->label('Material')
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
                        ->label('Stock del material')
                        ->numeric()
                        ->suffix('m²'),
    
                    TextInput::make('material')
                        ->label('Renderización de materiales')
                        ->lazy()
                        ->columnSpan('full')
                    ])
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
