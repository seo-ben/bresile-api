<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'code'      => 'payment_confirmed',
                'locale'    => 'fr',
                'subject'   => 'Paiement confirmé - Votre inscription est active !',
                'body_html' => '<p>Bonjour {{student_name}},</p><p>Nous confirmons la bonne réception de votre paiement de <strong>{{amount}} {{currency}}</strong>.</p><p>Votre statut d\'inscription est désormais <strong>Actif</strong>. Vous pouvez consulter votre emploi du temps et vos informations de cours directement sur votre espace étudiant.</p><p>À très bientôt dans nos cours !</p>',
            ],
            [
                'code'      => 'visa_documents_issued',
                'locale'    => 'fr',
                'subject'   => 'Vos documents officiels pour la demande de visa sont prêts !',
                'body_html' => '<p>Bonjour {{student_name}},</p><p>L\'école a émis les documents officiels requis pour votre demande de visa auprès des autorités consulaires brésiliennes (lettre d\'acceptation, attestation d\'inscription).</p><p>Vous pouvez les télécharger dès maintenant dans votre espace personnel, rubrique Visa.</p>',
            ],
            [
                'code'      => 'visa_refusal_refund_initiated',
                'locale'    => 'fr',
                'subject'   => 'Prise en charge de votre dossier de remboursement (Garantie Visa)',
                'body_html' => '<p>Bonjour {{student_name}},</p><p>Nous avons bien reçu votre déclaration et le justificatif consulaire concernant l\'issue de votre demande de visa.</p><p>Conformément à notre engagement de transparence, votre demande de remboursement à hauteur de <strong>85%</strong> (soit <strong>{{refund_amount}} {{currency}}</strong>) a été enregistrée et est en cours de traitement par notre service comptable.</p><p>Nous restons à vos côtés pour toute question.</p>',
            ],
            [
                'code'      => 'absence_warning',
                'locale'    => 'fr',
                'subject'   => 'Alerte préventive concernant vos présences en cours',
                'body_html' => '<p>Bonjour {{student_name}},</p><p>Nous constatons un cumul de {{minutes_absent}} minutes d\'absence. Pour rappel, la limite autorisée est de {{max_minutes}} minutes pour ce niveau.</p><p>N\'hésitez pas à transmettre vos justificatifs ou à contacter l\'administration pour éviter toute suspension de votre cursus.</p>',
            ],
        ];

        foreach ($templates as $tpl) {
            EmailTemplate::firstOrCreate(
                ['code' => $tpl['code'], 'locale' => $tpl['locale']],
                $tpl
            );
        }
    }
}

