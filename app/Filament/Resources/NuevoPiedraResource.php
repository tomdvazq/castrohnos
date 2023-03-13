<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\NuevoPiedra;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\NuevoPiedraResource\Pages;
use App\Filament\Resources\NuevoPiedraResource\RelationManagers;

class NuevoPiedraResource extends Resource
{
    protected static ?string $model = NuevoPiedra::class;

    protected static ?string $navigationGroup = 'Piedras';
    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';
    protected static ?string $navigationLabel = 'Nuevo';
    protected static ?string $pluralModelLabel = 'Clientes';
    protected static ?string $modelLabel = 'cliente';
    protected static ?string $slug = 'piedras/nuevo-cliente';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('📝 Datos obligatorios')
                    ->schema([
                        TextInput::make('nombre')
                            ->required(),
                        TextInput::make('contacto')
                            ->required(),
                    ]),
                Fieldset::make('📍 Ubicación')
                    ->schema([
                        TextInput::make('direccion')
                            ->label('Dirección'),
                        TextInput::make('localidad'),
                        TextInput::make('entrecalle_1')
                            ->label(function () {
                                $label = 'Entrecalle <b>(1)</b>';

                                return new HtmlString($label);
                            }),
                        TextInput::make('entrecalle_2')
                            ->label(function () {
                                $label = 'Entrecalle <b>(2)</b>';

                                return new HtmlString($label);
                            }),
                        Section::make('🤔 ¿Hay que especificar algo de la dirección?')
                            ->schema([
                                RichEditor::make('direccion_detalles')
                                    ->label('')
                                    ->columnSpan('full')
                                    ->disableToolbarButtons([
                                        'attachFiles',
                                        'codeBlock',
                                        'h2',
                                        'h3',
                                        'blockquote',
                                        'redo',
                                        'strike',
                                        'undo',
                                    ])
                            ])
                            ->collapsed(),
                    ])
                    ->columns(4),
                Section::make('Información adicional')
                    ->schema([
                        TextInput::make('documento')
                            ->numeric()
                            ->mask(fn (TextInput\Mask $mask) => $mask->pattern('00.000.000')),
                        TextInput::make('cuit_cuil')
                            ->label('CUIT/CUIL')
                            ->numeric()
                            ->mask(fn (TextInput\Mask $mask) => $mask->pattern('00-00000000-00')),
                        TextInput::make('razon_social')
                            ->label('Razón Social'),
                    ])
                    ->collapsed()
                    ->columns(3)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre'),
                TextColumn::make('direccion')
                    ->label('Dirección'),
                TextColumn::make('localidad'),
                TextColumn::make('contacto'),
                TextColumn::make('documento'),
                TextColumn::make('cuit_cuil')
                    ->label('CUIT/CUIL'),
                TextColumn::make('razon_social')
                    ->label('Razón Social'),
            ])
            ->filters([
                //
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
            RelationManagers\PedidoPiedrasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\CreateNuevoPiedra::route('/create'),
            'edit' => Pages\EditNuevoPiedra::route('/{record}/edit'),
        ];
    }
}
