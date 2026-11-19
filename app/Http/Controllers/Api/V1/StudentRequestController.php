<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Enrollment;
use App\Models\StudentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StudentRequestController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $requests = StudentRequest::where('student_id', $user->id)
            ->orderByDesc('id')
            ->get()
            ->map(fn (StudentRequest $r) => $this->formatRequest($r))
            ->values();

        return $this->successResponse(
            data: $requests,
            message: 'Liste de vos demandes récupérée avec succès.'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'       => 'required|string|in:a1_brazil,offer_access,city_change,other',
            'subject'    => 'required|string|max:190',
            'message'    => 'required|string|max:4000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp,doc,docx|max:15360',
        ]);

        $user = $request->user();
        $latestEnrollment = Enrollment::where('student_id', $user->id)->latest('id')->first();

        $attachmentPath = null;
        $attachmentName = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('student_requests', 'public');
            $attachmentName = $file->getClientOriginalName();
        }

        $studentRequest = StudentRequest::create([
            'student_id'      => $user->id,
            'enrollment_id'   => $latestEnrollment?->id,
            'level_id'        => $latestEnrollment?->level_id,
            'type'            => $validated['type'],
            'subject'         => $validated['subject'],
            'message'         => $validated['message'],
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'status'          => 'open',
        ]);

        return $this->successResponse(
            data: $this->formatRequest($studentRequest),
            message: 'Votre demande a été transmise à l’administration avec succès.',
            code: Response::HTTP_CREATED
        );
    }

    protected function formatRequest(StudentRequest $r): array
    {
        return [
            'id'                   => $r->id,
            'reference'            => 'DEM-' . ($r->created_at?->format('Y') ?? date('Y')) . '-' . str_pad((string) $r->id, 4, '0', STR_PAD_LEFT),
            'type'                 => $r->type,
            'subject'              => $r->subject ?: match ($r->type) {
                'a1_brazil'    => 'Inscription Anticipée A1 Présentiel Brésil',
                'city_change'  => 'Changement de Ville d’Accueil',
                'offer_access' => 'Demande d’accès à une offre spéciale',
                default        => 'Demande administrative',
            },
            'message'              => $r->message,
            'attachment_name'      => $r->attachment_name,
            'attachment_url'       => $r->attachment_path ? asset('storage/' . ltrim($r->attachment_path, '/')) : null,
            'status'               => $r->status ?? 'open',
            'decision_note'        => $r->decision_note,
            'admin_attachment_url' => $r->admin_attachment_path ? asset('storage/' . ltrim($r->admin_attachment_path, '/')) : null,
            'resolved_at'          => $r->resolved_at?->format('d/m/Y à H:i'),
            'created_at'           => $r->created_at?->format('d/m/Y à H:i'),
        ];
    }
}
