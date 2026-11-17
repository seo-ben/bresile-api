<?php

namespace App\Http\Resources;

use App\Services\AutoTranslationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $translator = app(AutoTranslationService::class);
        $lang = $translator->resolveLocale($request);

        return [
            'id'          => $this->id,
            'purpose'     => $this->purpose,
            'title'       => $translator->translate($this->title, $lang),
            'description' => $translator->translate($this->description, $lang),
            'fields'      => $this->fields->map(fn($f) => [
                'id'                       => $f->id,
                'field_key'                => $f->field_key,
                'label'                    => $translator->translate($f->label, $lang),
                'help_text'                => $translator->translate($f->help_text, $lang),
                'field_type'               => $f->field_type,
                'system_key'               => $f->system_key,
                'is_required'              => (bool) $f->is_required,
                'is_locked'                => (bool) $f->is_locked,
                'is_editable_after_submit' => (bool) $f->is_editable_after_submit,
                'visible_if'               => $f->visible_if,
                'validation_rules'         => $f->validation_rules,
                'position'                 => $f->position,
                'options'                  => $f->options->map(fn($o) => [
                    'value'    => $o->option_value,
                    'label'    => $translator->translate($o->option_label, $lang),
                    'position' => $o->position,
                ]),
            ]),
        ];
    }
}
