<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\EmailOutbox;
use App\Models\EmailTemplate;
use App\Models\SenderEmail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailNotificationService
{
    /**
     * Send an automated email based on an admin-configurable template.
     *
     * @param string $templateCode The code of the template (e.g., 'bank_transfer_instructions')
     * @param User $recipient The recipient user
     * @param array<string, mixed> $variables Dynamic variables to interpolate in subject & body
     * @param string $locale Preferred locale
     */
    public function sendTemplateEmail(
        string $templateCode,
        User $recipient,
        array $variables = [],
        string $locale = 'fr'
    ): EmailOutbox {
        // 1. Récupération du template actif configuré par l'administrateur
        $template = EmailTemplate::with('sender')
            ->where('code', $templateCode)
            ->where('locale', $locale)
            ->where('is_active', true)
            ->first();

        // Repli sur le français si la langue demandée n'existe pas
        if (!$template && $locale !== 'fr') {
            $template = EmailTemplate::with('sender')
                ->where('code', $templateCode)
                ->where('locale', 'fr')
                ->where('is_active', true)
                ->first();
        }

        // Si aucun template en base, fallback sur un template par défaut
        $subject = $template ? $template->subject : "Notification École de Portugais";
        $body = $template ? $template->body_html : "<p>Bonjour {{student_name}}, une mise à jour est disponible sur votre dossier.</p>";

        // 2. Détermination de l'expéditeur
        $sender = $template?->sender ?? SenderEmail::where('is_default', true)->first();
        $fromEmail = $sender?->email_address ?? config('mail.from.address', 'contact@ecole-bresil.com');

        // 3. Interpolation des variables
        $variables = array_merge([
            'student_name' => $recipient->profile?->first_name 
                ? ($recipient->profile->first_name . ' ' . $recipient->profile->last_name) 
                : $recipient->email,
            'app_url'      => config('app.url'),
            'date'         => now()->format('d/m/Y'),
        ], $variables);

        $parsedSubject = $this->interpolateVariables($subject, $variables);
        $parsedBody = $this->interpolateVariables($body, $variables);

        // 4. Enregistrement dans la boîte d'envoi (email_outbox) pour audit et traçabilité
        $outbox = EmailOutbox::create([
            'template_id' => $template?->id,
            'user_id'     => $recipient->id,
            'from_email'  => $fromEmail,
            'to_email'    => $recipient->email,
            'subject'     => $parsedSubject,
            'body_html'   => $parsedBody,
            'status'      => 'queued',
            'queued_at'   => now(),
        ]);

        // 5. Envoi direct via le Mailer Laravel
        try {
            Mail::html($parsedBody, function ($message) use ($recipient, $fromEmail, $parsedSubject) {
                $message->to($recipient->email)
                    ->from($fromEmail, 'École de Portugais Brésilien')
                    ->subject($parsedSubject);
            });

            $outbox->update([
                'status'  => 'sent',
                'sent_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error("Échec d'envoi d'email automatique: " . $e->getMessage(), [
                'outbox_id' => $outbox->id,
                'to'        => $recipient->email,
            ]);

            $outbox->update([
                'status'        => 'failed',
                'error_message' => substr($e->getMessage(), 0, 500),
            ]);
        }

        return $outbox;
    }

    /**
     * Helper pour envoyer les instructions de virement bancaire.
     */
    public function sendBankTransferInstructions(User $student, float $amount, string $currency): EmailOutbox
    {
        $accounts = BankAccount::where('is_active', true)->get();
        $accountDetailsHtml = '<ul>';
        foreach ($accounts as $acc) {
            $accountDetailsHtml .= "<li><strong>{$acc->bank_name}</strong> - IBAN: {$acc->iban} - BIC: {$acc->swift_bic} (Titulaire: {$acc->beneficiary_name})</li>";
        }
        $accountDetailsHtml .= '</ul>';

        return $this->sendTemplateEmail(
            templateCode: 'bank_transfer_instructions',
            recipient: $student,
            variables: [
                'amount'               => number_format($amount, 2, ',', ' '),
                'currency'             => $currency,
                'bank_account_details' => $accountDetailsHtml,
            ],
            locale: $student->preferred_locale ?? 'fr'
        );
    }

    /**
     * Remplace les tags mustache {{variable}} par leur valeur réelle.
     */
    protected function interpolateVariables(string $text, array $variables): string
    {
        foreach ($variables as $key => $value) {
            if (is_scalar($value) || is_null($value)) {
                $text = str_replace('{{' . $key . '}}', (string) $value, $text);
                $text = str_replace('{{ ' . $key . ' }}', (string) $value, $text);
            }
        }
        return $text;
    }
}

