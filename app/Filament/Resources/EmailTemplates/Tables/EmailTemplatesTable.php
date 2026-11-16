<?php

namespace App\Filament\Resources\EmailTemplates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmailTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code Technique')
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('subject')
                    ->label('Objet du Message')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('sender.email_address')
                    ->label('Expéditeur')
                    ->placeholder('Défaut système')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('locale')
                    ->label('Langue')
                    ->badge()
                    ->color('info'),

                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->recordAction(EditAction::class)
            ->recordActions([
                EditAction::make()
                    ->label('Modifier')
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Modifier le Modèle d’Email')
                    ->modalWidth('3xl'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
