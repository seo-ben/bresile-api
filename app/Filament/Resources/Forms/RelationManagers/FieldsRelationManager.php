<?php

namespace App\Filament\Resources\Forms\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'fields';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('field_key')
                    ->required(),
                TextInput::make('label')
                    ->required(),
                TextInput::make('help_text')
                    ->default(null),
                Select::make('field_type')
                    ->options([
            'text' => 'Text',
            'textarea' => 'Textarea',
            'number' => 'Number',
            'date' => 'Date',
            'email' => 'Email',
            'phone' => 'Phone',
            'url' => 'Url',
            'select' => 'Select',
            'multiselect' => 'Multiselect',
            'radio' => 'Radio',
            'checkbox' => 'Checkbox',
            'file' => 'File',
            'country' => 'Country',
            'brazil_city' => 'Brazil city',
        ])
                    ->required(),
                Select::make('system_key')
                    ->options([
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'phone' => 'Phone',
            'country_code' => 'Country code',
            'study_mode' => 'Study mode',
            'rhythm' => 'Rhythm',
            'brazil_city_id' => 'Brazil city id',
        ])
                    ->default(null),
                Toggle::make('is_required')
                    ->required(),
                Toggle::make('is_editable_after_submit')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('position')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('default_value')
                    ->default(null),
                Textarea::make('validation_rules')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('visible_if')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->columns([
                TextColumn::make('field_key')
                    ->searchable(),
                TextColumn::make('label')
                    ->searchable(),
                TextColumn::make('help_text')
                    ->searchable(),
                TextColumn::make('field_type')
                    ->badge(),
                TextColumn::make('system_key')
                    ->badge(),
                IconColumn::make('is_required')
                    ->boolean(),
                IconColumn::make('is_editable_after_submit')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('position')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('default_value')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
