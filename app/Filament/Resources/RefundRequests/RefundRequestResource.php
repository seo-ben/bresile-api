<?php

namespace App\Filament\Resources\RefundRequests;

use App\Filament\Resources\RefundRequests\Pages\CreateRefundRequest;
use App\Filament\Resources\RefundRequests\Pages\EditRefundRequest;
use App\Filament\Resources\RefundRequests\Pages\ListRefundRequests;
use App\Filament\Resources\RefundRequests\Schemas\RefundRequestForm;
use App\Filament\Resources\RefundRequests\Tables\RefundRequestsTable;
use App\Models\RefundRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RefundRequestResource extends Resource
{
    protected static \UnitEnum|string|null $navigationGroup = "Inscriptions & Visas";
    protected static ?int $navigationSort = 4;

    protected static ?string $model = RefundRequest::class;
    protected static ?string $navigationLabel = 'Remboursements (85%)';
    protected static ?string $pluralModelLabel = 'Remboursements Garantie Consulaire';

    public static function getRecordTitle(?\Illuminate\Database\Eloquent\Model $record): ?string
    {
        if (! $record) {
            return 'Dossier de Remboursement';
        }

        $profile = $record->student?->profile;
        if ($profile && ($profile->first_name || $profile->last_name)) {
            return 'Remboursement — ' . trim("{$profile->first_name} {$profile->last_name}");
        }

        return $record->student?->email ? "Remboursement — {$record->student->email}" : 'Dossier de Remboursement';
    }

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    public static function form(Schema $schema): Schema
    {
        return RefundRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RefundRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRefundRequests::route('/'),
        ];
    }
}
