<?php

namespace App\Filament\Resources\MedicionesResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ArchivosRelationManager extends RelationManager
{
    protected static string $relationship = 'archivos';

    protected static ?string $recordTitleAttribute = 'pedido_id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('identificacion')
                    ->label('Identificación de archivo')
                    ->columnSpan('full'),
                Select::make('categoria')
                    ->label('Corresponde a una/un')
                    ->options([
                        'Factura' => 'Factura',
                        'Nota de crédito' => 'Nota de crédito',
                        'Nota de débito' => 'Nota de débito',
                        'Archivo' => 'Archivo',
                        'Link' => 'Link'
                    ])
                    ->columnSpan(1),
                Select::make('tipo')
                    ->label('Tipo de archivo')
                    ->options([
                        'AutoCAD' => 'AutoCAD',
                        'PDF' => 'PDF',
                        'Excel' => 'Excel',
                        'Dropbox' => 'Dropbox',
                    ])
                    ->columnSpan(1),

                Section::make('Archivos')
                    ->schema([
                        FileUpload::make('archivo')
                        ->label('Archivo')
                        ->helperText('Disponible para AutoCAD, PDF o Excel')
                        ->enableOpen()
                        ->enableDownload()
                        ->preserveFilenames()
                        ->panelLayout(null),
                    ])
                    ->collapsed()
                    ->columnSpan('full'),
                
                Section::make('Dropbox')
                    ->schema([
                        TextInput::make('dropbox')
                        ->label('Dropbox')
                        ->helperText('Disponible para subir links de Dropbox')
                        ->suffixAction(fn (?string $state): Action =>
                            Action::make('Dropbox')
                                ->icon('heroicon-s-external-link')
                                ->url(
                                    filled($state) ? "{$state}" : null,
                                    shouldOpenInNewTab: true,
                                ),
                        )
                    ])
                    ->collapsed()
                    ->columnSpan('full')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('identificacion'),
                TextColumn::make('categoria'),
                TextColumn::make('tipo'),
                TextColumn::make('archivo')
                    ->getStateUsing(function ($record) {
                        $archivo = $record->archivo;
                        $dropbox = $record->dropbox;
                        $res = "";

                        if($archivo === null) {
                            $res = $dropbox;
                        } else if ($dropbox === null) {
                            $res = $archivo;
                        }

                        return $res;
                    }),
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