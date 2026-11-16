<?php

namespace App\Filament\Widgets;

use App\Models\Enrollment;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestEnrollmentsWidget extends TableWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Dernières Inscriptions & Dossiers Étudiants')
            ->description('Suivi en temps réel des apprenants inscrits pour la mobilité au Brésil')
            ->query(
                Enrollment::query()
                    ->with(['student.profile', 'level', 'city'])
                    ->latest()
            )
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('student')
                    ->label('Étudiant')
                    ->state(function (Enrollment $record): string {
                        $profile = $record->student?->profile;
                        if ($profile && ($profile->first_name || $profile->last_name)) {
                            return trim("{$profile->first_name} {$profile->last_name}");
                        }
                        return $record->student?->email ?? 'Candidat';
                    })
                    ->description(fn (Enrollment $record): string => $record->student?->email ?? '')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('student', function ($q) use ($search) {
                            $q->where('email', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('level.name')
                    ->label('Niveau Langue')
                    ->badge()
                    ->color('emerald'),

                TextColumn::make('city.name')
                    ->label('Destination Brésil')
                    ->icon('heroicon-m-map-pin')
                    ->default('Non assignée'),

                TextColumn::make('rhythm')
                    ->label('Rythme')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'completed' => 'info',
                        'canceled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'active' => 'Actif',
                        'pending' => 'En attente',
                        'completed' => 'Terminé',
                        'canceled' => 'Annulé',
                        default => ucfirst($state ?? 'Inconnu'),
                    }),

                TextColumn::make('created_at')
                    ->label('Date Inscription')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Voir Dossier')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Enrollment $record): string => url("/admin/enrollments/{$record->id}/edit")),
            ]);
    }
}
