<?php

namespace App\Services;

use App\DTOs\SubmitFormDTO;
use App\Models\Form;
use App\Models\FormAnswer;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\UserProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DynamicFormService
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Retrieve a form by purpose with all active fields and options.
     */
    public function getFormByPurpose(string $purpose): Form
    {
        return Form::with(['fields' => function ($q) {
            $q->orderBy('position')->with(['options' => fn($opt) => $opt->orderBy('position')]);
        }])
            ->where('purpose', $purpose)
            ->where('is_active', true)
            ->firstOrFail();
    }

    /**
     * Submit answers for a dynamic form, validate requirements, and sync system keys.
     */
    public function submitForm(SubmitFormDTO $dto): FormSubmission
    {
        $form = Form::with('fields')->findOrFail($dto->formId);

        return DB::transaction(function () use ($form, $dto) {
            // Validation des champs requis
            $errors = [];
            foreach ($form->fields as $field) {
                $val = $dto->answers[$field->field_key] ?? null;

                if ($field->is_required && (is_null($val) || $val === '')) {
                    $errors[$field->field_key] = ["Le champ \"{$field->label}\" est obligatoire."];
                }
            }

            if (!empty($errors)) {
                throw ValidationException::withMessages($errors);
            }

            $submission = FormSubmission::create([
                'form_id'      => $form->id,
                'user_id'      => $dto->userId,
                'status'       => 'submitted',
                'submitted_at' => now(),
            ]);

            $profileUpdates = [];

            foreach ($form->fields as $field) {
                $val = $dto->answers[$field->field_key] ?? null;
                if (is_null($val)) {
                    continue;
                }

                $answerData = [
                    'submission_id'        => $submission->id,
                    'field_id'             => $field->id,
                    'field_key_snapshot'   => $field->field_key,
                    'field_label_snapshot' => $field->label,
                ];

                if (is_array($val)) {
                    $answerData['value_json'] = $val;
                } else {
                    $answerData['value_text'] = (string) $val;
                }

                FormAnswer::create($answerData);

                // Synchronisation des clés système vers user_profiles si applicable
                if ($field->system_key) {
                    $this->mapSystemKeyToProfile($field->system_key, $val, $profileUpdates);
                }
            }

            if (!empty($profileUpdates)) {
                UserProfile::updateOrCreate(
                    ['user_id' => $dto->userId],
                    $profileUpdates
                );
            }

            $this->auditService->log(
                eventType: 'form_submitted',
                entity: $submission,
                newValues: ['form_id' => $form->id, 'answers_count' => count($dto->answers)],
                actorId: $dto->userId
            );

            return $submission->load('answers.field');
        });
    }

    protected function mapSystemKeyToProfile(string $systemKey, mixed $val, array &$updates): void
    {
        match ($systemKey) {
            'first_name'   => $updates['first_name'] = (string) $val,
            'last_name'    => $updates['last_name'] = (string) $val,
            'phone'        => $updates['phone'] = (string) $val,
            'country_code' => $updates['country_code'] = (string) $val,
            'city'         => $updates['city'] = (string) $val,
            'address'      => $updates['address_line1'] = (string) $val,
            default        => null,
        };
    }
}
