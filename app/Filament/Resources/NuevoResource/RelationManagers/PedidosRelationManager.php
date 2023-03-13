<?php

namespace App\Filament\Resources\NuevoResource\RelationManagers;

use App\Models\AccesorioListado;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Bacha;
use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\Material;
use App\Models\Accesorios;
use App\Models\AccesoriosSelection;
use App\Models\BachaListado;
use Filament\Resources\Form;
use Filament\Resources\Table;
use App\Models\BachasSelection;
use App\Models\MaterialListado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use App\Models\MaterialesSelection;
use Filament\Tables\Actions\Action;
use Illuminate\Foundation\Auth\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class PedidosRelationManager extends RelationManager
{
    protected static string $relationship = 'pedidos';

    protected static ?string $pluralModelLabel = 'Mesadas';
    protected static ?string $modelLabel = 'mesada';

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
                ActionGroup::make([
                    Action::make('agregarMaterial')
                        ->label('Agregar material')
                        ->icon('heroicon-o-plus-circle')
                        ->color('success')
                        ->action(function (Pedido $record, array $data): void {
                            $record->id;
                        })
                        ->form([
                            Fieldset::make('Selección de material')
                                ->schema([
                                    Select::make('material_id')
                                        ->options(Material::all()->pluck('tipo', 'id')->toArray())
                                        ->label('Tipo de material')
                                        ->afterStateUpdated(fn (callable $set, $get) => $set('material_listado_id', null))
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
                                        ->label('Stock del material')
                                        ->numeric()
                                        ->suffix('m²'),
                                ]),

                            TextInput::make('material')
                                ->label('')
                                ->lazy()
                                ->columnSpan('full')
                                ->extraAttributes(['style' => 'display: none'])
                                ->saveRelationshipsUsing(function ($get, $record) {

                                    $lastSelectionId = MaterialesSelection::all()->last()->id;
                                    $newSelectionId = $lastSelectionId + 1;

                                    $result = DB::table('materiales_selections')->insert([
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
                        Action::make('agregarBacha')
                            ->label('Agregar bacha')
                            ->icon('heroicon-o-plus-circle')
                            ->color('primary')
                            ->action(function (Pedido $record, array $data): void {
                                $record->id;
                            })
                            ->form([
                                Fieldset::make('Selección de material')
                                    ->schema([
                                        Select::make('tipo_bacha')
                                        ->label('Tipo de bacha')
                                        ->options([
                                            'Baño' => 'Baño',
                                            'Cocina' => 'Cocina'
                                        ])
                                        ->required(),

                                        Select::make('bacha_id')
                                            ->options(Bacha::all()->pluck('marca', 'id')->toArray())
                                            ->label('Marca')
                                            ->afterStateUpdated(fn (callable $set, $get) => $set('bacha_listado_id', null))
                                            ->reactive(),

                                        Select::make('bacha_listado_id')
                                            ->label('Línea')
                                            ->options(function (callable $get) {
                                                $bacha = Bacha::find($get('bacha_id'));

                                                if (!$bacha) {
                                                    return BachaListado::all()->pluck('linea', 'id');
                                                }

                                                $value = $bacha->bachasStock->pluck('linea', 'id');

                                                return $value;
                                            })
                                            ->afterStateUpdated(function ($set, $get) {
                                                $id = BachaListado::find($get('bacha_listado_id'));
                                                $material = $id?->linea;
                                                $stock = $id->stock;

                                                $set('material', $material);
                                                $set('stock', $stock);
                                            })
                                            ->reactive()
                                            ->searchable(),
                                    ])
                                    ->columns(3),

                                Fieldset::make('Cantidad y stock')
                                    ->schema([
                                        TextInput::make('cantidad')
                                            ->label('Cantidad')
                                            ->saveRelationshipsUsing(function ($set, $get) {
                                                $bacha = BachaListado::find($get('bacha_listado_id'));
                                                $m2 = $get('cantidad');
                                                $stock = $bacha->stock;

                                                $bacha->stock = intval($stock) - intval($m2);

                                                $bacha->save();
                                            })
                                            ->numeric()
                                            ->suffix('U'),

                                        TextInput::make('stock')
                                            ->label('Stock de la bacha')
                                            ->numeric()
                                            ->suffix('U'),
                                    ]),

                                TextInput::make('material')
                                    ->label('')
                                    ->lazy()
                                    ->columnSpan('full')
                                    ->extraAttributes(['style' => 'display: none'])
                                    ->saveRelationshipsUsing(function ($get, $record) {

                                        $lastSelectionId = BachasSelection::all()->last()->id;
                                        $newSelectionId = $lastSelectionId + 1;

                                        $result = DB::table('bachas_selections')->insert([
                                            'id' => $newSelectionId,
                                            'pedido_id' => $record->id,
                                            'bacha_id' => $get('bacha_id'),
                                            'bacha_listado_id' => $get('bacha_listado_id'),
                                            'cantidad' => $get('cantidad'),
                                            'material' => $get('material'),
                                        ]);

                                        return $result;
                                    })
                            ]),
                        Action::make('agregarAccesorio')
                            ->label('Agregar accesorio')
                            ->icon('heroicon-o-plus-circle')
                            ->color('danger')
                            ->action(function (Pedido $record, array $data): void {
                                $record->id;
                            })
                            ->form([
                                Fieldset::make('Selección de accesorio')
                                    ->schema([
                                        Select::make('accesorio_id')
                                            ->options(Accesorios::all()->pluck('marca', 'id')->toArray())
                                            ->label('Marca')
                                            ->afterStateUpdated(fn (callable $set, $get) => $set('bacha_listado_id', null))
                                            ->reactive(),

                                        Select::make('accesorio_listado_id')
                                            ->label('Línea')
                                            ->options(function (callable $get) {
                                                $bacha = Accesorios::find($get('accesorio_id'));

                                                if (!$bacha) {
                                                    return AccesorioListado::all()->pluck('tipo', 'id');
                                                }

                                                $value = $bacha->accesoriosStock->pluck('tipo', 'id');

                                                return $value;
                                            })
                                            ->afterStateUpdated(function ($set, $get) {
                                                $id = AccesorioListado::find($get('accesorio_listado_id'));
                                                $material = $id?->tipo;
                                                $stock = $id->stock;

                                                $set('material', $material);
                                                $set('stock', $stock);
                                            })
                                            ->reactive()
                                            ->searchable(),
                                    ])
                                    ->columns(2),

                                Fieldset::make('Cantidad y stock')
                                    ->schema([
                                        TextInput::make('cantidad')
                                            ->label('Cantidad')
                                            ->saveRelationshipsUsing(function ($set, $get) {
                                                $accesorio = AccesorioListado::find($get('accesorio_listado_id'));
                                                $m2 = $get('cantidad');
                                                $stock = $accesorio->stock;

                                                $accesorio->stock = intval($stock) - intval($m2);

                                                $accesorio->save();
                                            })
                                            ->numeric()
                                            ->suffix('U'),

                                        TextInput::make('stock')
                                            ->label('Stock de la bacha')
                                            ->numeric()
                                            ->suffix('U'),
                                    ]),

                                TextInput::make('material')
                                    ->label('')
                                    ->lazy()
                                    ->columnSpan('full')
                                    ->extraAttributes(['style' => 'display: none'])
                                    ->saveRelationshipsUsing(function ($get, $record) {

                                        $lastSelectionId = AccesoriosSelection::all()->last()->id;
                                        $newSelectionId = $lastSelectionId + 1;

                                        $result = DB::table('accesorios_selections')->insert([
                                            'id' => $newSelectionId,
                                            'pedido_id' => $record->id,
                                            'accesorio_id' => $get('accesorio_id'),
                                            'accesorio_listado_id' => $get('accesorio_listado_id'),
                                            'cantidad' => $get('cantidad'),
                                            'material' => $get('material'),
                                        ]);

                                        return $result;
                                    })
                            ]),
                ])
                    ->icon('heroicon-o-plus-circle')
                    ->color('success'),
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
