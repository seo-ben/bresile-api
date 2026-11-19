<?php

namespace App\Filament\Resources\OfficialDocuments;

use App\Filament\Resources\OfficialDocuments\Pages\ListOfficialDocuments;
use App\Models\FileModel;
use App\Models\VisaApplication;
use App\Models\VisaDocument;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class OfficialDocumentResource extends Resource
{
    protected static \UnitEnum|string|null $navigationGroup = 'Inscriptions & Visas';
    protected static ?int $navigationSort = 5;

    protected static ?string $model = VisaDocument::class;
    protected static ?string $slug = 'official-documents';
    protected static ?string $navigationLabel = 'Documents Officiels';
    protected static ?string $pluralModelLabel = 'Documents Officiels & Attestations';
    protected static ?string $modelLabel = 'Document Officiel';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-arrow-up';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Envoyer un Document Officiel à l’Étudiant')
                    ->description('Le fichier téléversé apparaît immédiatement en téléchargement dans l’espace étudiant (Documents Officiels & Attestations).')
                    ->schema([
                        Select::make('visa_application_id')
                            ->label('Étudiant / Dossier Associé')
                            ->options(function () {
                                return VisaApplication::with(['student.profile', 'enrollment.level'])
                                    ->latest('id')
                                    ->get()
                                    ->mapWithKeys(function ($v) {
                                        $profile = $v->student?->profile;
                                        $name = $profile && ($profile->first_name || $profile->last_name)
                                            ? trim("{$profile->first_name} {$profile->last_name}")
                                            : ($v->student?->email ?? 'Candidat');
                                        $level = $v->enrollment?->level?->name ?? 'Formation';
                                        $visaType = $v->enrollment?->visa_type === 'visitor_visa' ? 'Visa Visiteur' : 'VITEM IV';
                                        return [$v->id => "{$name} ({$v->student?->email}) — {$level} [{$visaType}]"];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Grid::make(2)->schema([
                            Select::make('document_type')
                                ->label('Type de Document Officiel')
                                ->options([
                                    'attestation' => 'Attestation d’Inscription Provisoire / Officielle',
                                    'contrat'     => 'Contrat & Garantie 85% de Remboursement',
                                    'fiche'       => 'Fiche de Candidature Officielle',
                                    'guide'       => 'Guide d’Installation & Visa au Brésil',
                                    'autre'       => 'Autre document officiel personnalisé',
                                ])
                                ->default('attestation')
                                ->live()
                                ->afterStateUpdated(function ($state, $set) {
                                    $defaults = [
                                        'attestation' => 'Attestation d’Inscription Officielle',
                                        'contrat'     => 'Contrat & Garantie 85% de Remboursement',
                                        'fiche'       => 'Fiche de Candidature Officielle',
                                        'guide'       => 'Guide d’Installation & Visa au Brésil',
                                    ];
                                    if (isset($defaults[$state])) {
                                        $set('title', $defaults[$state]);
                                    }
                                })
                                ->dehydrated(false),

                            TextInput::make('title')
                                ->label('Intitulé affiché à l’étudiant')
                                ->default('Attestation d’Inscription Officielle')
                                ->required()
                                ->maxLength(190),
                        ]),

                        FileUpload::make('uploaded_file_path')
                            ->label('Fichier Officiel (PDF, JPG, PNG — max 15 Mo)')
                            ->disk('public')
                            ->directory('official_documents')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(15360)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->helperText('Téléversez le document signé/tamponné. L’étudiant pourra le télécharger directement depuis son portail.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student')
                    ->label('Étudiant Destinataire')
                    ->state(function (VisaDocument $record): string {
                        $student = $record->visaApplication?->student;
                        $profile = $student?->profile;
                        if ($profile && ($profile->first_name || $profile->last_name)) {
                            return trim("{$profile->first_name} {$profile->last_name}");
                        }
                        return $student?->email ?? 'Candidat';
                    })
                    ->description(fn (VisaDocument $record): string => $record->visaApplication?->student?->email ?? '')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('visaApplication.student', function ($q) use ($search) {
                            $q->where('email', 'like', "%{$search}%")
                              ->orWhereHas('profile', function ($qp) use ($search) {
                                  $qp->where('first_name', 'like', "%{$search}%")
                                     ->orWhere('last_name', 'like', "%{$search}%");
                              });
                        });
                    })
                    ->weight('bold'),

                TextColumn::make('title')
                    ->label('Document Officiel')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                TextColumn::make('file.original_name')
                    ->label('Fichier Téléversé')
                    ->state(fn (VisaDocument $record): string => $record->file?->original_name ?? 'Voir le fichier')
                    ->url(function (VisaDocument $record): ?string {
                        $path = $record->file?->storage_path;
                        return $path ? asset('storage/' . ltrim($path, '/')) : null;
                    }, shouldOpenInNewTab: true)
                    ->icon('heroicon-m-document-arrow-down')
                    ->color('success'),

                TextColumn::make('issued_at')
                    ->label('Envoyé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                DeleteAction::make()->label('Supprimer'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Supprimer la sélection'),
                ]),
            ]);
    }

    public static function createDocumentFromUpload(int $visaApplicationId, string $title, string $storagePath): VisaDocument
    {
        $disk = Storage::disk('public');
        $size = $disk->exists($storagePath) ? $disk->size($storagePath) : 0;
        $mime = $disk->exists($storagePath) ? ($disk->mimeType($storagePath) ?: 'application/pdf') : 'application/pdf';

        $file = FileModel::create([
            'uploaded_by'   => (int) (auth()->id() ?: 1),
            'storage_disk'  => 'public',
            'storage_path'  => $storagePath,
            'original_name' => basename($storagePath),
            'mime_type'     => $mime,
            'size_bytes'    => $size,
        ]);

        $doc = VisaDocument::create([
            'visa_application_id' => $visaApplicationId,
            'title'               => $title,
            'file_id'             => $file->id,
            'issued_by'           => (int) (auth()->id() ?: 1),
            'issued_at'           => now(),
        ]);

        $visa = VisaApplication::find($visaApplicationId);
        if ($visa && $visa->status === 'draft') {
            $visa->update(['status' => 'documents_issued']);
        }

        return $doc;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOfficialDocuments::route('/'),
        ];
    }
}
