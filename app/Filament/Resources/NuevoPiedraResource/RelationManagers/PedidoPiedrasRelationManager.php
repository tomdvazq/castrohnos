<?php

namespace App\Filament\Resources\NuevoPiedraResource\RelationManagers;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class PedidoPiedrasRelationManager extends RelationManager
{
    protected static string $relationship = 'pedido_piedras';

    protected static ?string $recordTitleAttribute = 'pedido_id';

    protected static ?string $pluralModelLabel = 'Piedras';

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
                TextInput::make('seña')
                    ->helperText('')
                    ->mask(fn (TextInput\Mask $mask) => $mask->money(prefix: '$ ', thousandsSeparator: ',', decimalPlaces: 2, isSigned: false)),
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
                TextColumn::make('seña') 
                    ->money('ars'),
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
}
