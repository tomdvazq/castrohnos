<?php

namespace App\Filament\Resources\MedicionesResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;

class ClientesRelationManager extends RelationManager
{
    protected static string $relationship = 'clientes';

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static ?string $pluralModelLabel = 'Cliente';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nombre')
                    ->required(),
                TextInput::make('contacto')
                    ->required()
                    ->suffixAction(
                        fn (?string $state): Action =>
                        Action::make('Whatsapp')
                            ->icon('heroicon-s-phone')
                            ->url(
                                filled($state) ? "https://api.whatsapp.com/send?phone=549{$state}&text=¡Hola!%20nos%20comunicamos%20desde%20Castro%20Hermanos" : null,
                                shouldOpenInNewTab: true,
                            ),
                    ),
                TextInput::make('localidad'),
                TextInput::make('direccion')
                    ->label('Dirección')
                    ->suffixAction(
                        fn (?string $state): Action =>
                        Action::make('Maps')
                            ->icon('heroicon-s-location-marker')
                            ->url(
                                filled($state) ? "https://www.google.com.ar/maps/place/{$state}" : null,
                                shouldOpenInNewTab: true,
                            ),
                    ),
                TextInput::make('documento'),
                TextInput::make('cuit_cuil')
                    ->label('CUIT/CUIL'),
                TextInput::make('razon_social')
                    ->label('Razón Social'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre'),
                TextColumn::make('contacto'),
                TextColumn::make('direccion')
                    ->label('Dirección'),
                TextColumn::make('localidad'),
                TextColumn::make('documento'),
                TextColumn::make('cuit_cuil')
                    ->label('CUIT/CUIL'),
                TextColumn::make('razon_social')
                    ->label('Razón social'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
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
}
