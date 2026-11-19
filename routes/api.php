<?php

use App\Http\Controllers\Api\V1\AcademicController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EnrollmentController;
use App\Http\Controllers\Api\V1\FormController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\StudentRequestController;
use App\Http\Controllers\Api\V1\VisaController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ==========================================
    // 1. ENDPOINTS PUBLICS (Accessibles à tous)
    // ==========================================

    // Authentification
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Formulaire d'inscription dynamique (piloté par l'admin via CMS)
    Route::get('/forms/registration', [FormController::class, 'getRegistrationForm']);

    // Données de configuration académique & informationnelle
    Route::prefix('academic')->group(function () {
        Route::get('/levels', [AcademicController::class, 'getLevels']);
        Route::get('/cities', [AcademicController::class, 'getCities']);
        Route::get('/universities', [AcademicController::class, 'getUniversities']);
        Route::get('/refund-policies', [AcademicController::class, 'getRefundPolicies']);
        Route::get('/cms/{key}', [AcademicController::class, 'getCmsContent']);
    });

    // Coordonnées pour virement bancaire
    Route::get('/payments/bank-accounts', [PaymentController::class, 'getBankAccounts']);

    // Capture des numéros WhatsApp depuis le bandeau du site pour l'administration
    Route::post('/leads/whatsapp', [AcademicController::class, 'storeWhatsappLead']);

    // Identité du site (Logo, nom, slogan multilingue, email & téléphone)
    Route::get('/site/settings', [AcademicController::class, 'getSiteSettings']);


    // ==========================================
    // 2. ENDPOINTS PROTÉGÉS (auth:sanctum)
    // ==========================================
    Route::middleware('auth:sanctum')->group(function () {

        // Session & Profil
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Soumission de formulaire dynamique
        Route::post('/forms/submit', [FormController::class, 'submit']);

        // Inscriptions académiques de l'étudiant
        Route::get('/enrollments', [EnrollmentController::class, 'index']);
        Route::get('/enrollments/{id}', [EnrollmentController::class, 'show']);
        Route::post('/enrollments', [EnrollmentController::class, 'store']);

        // Paiements & Déclarations de virement
        Route::get('/payments', [PaymentController::class, 'index']);
        Route::post('/payments', [PaymentController::class, 'store']);
        Route::post('/payments/stripe/checkout', [PaymentController::class, 'createStripeCheckout']);
        Route::post('/payments/stripe/verify', [PaymentController::class, 'verifyStripeSession']);

        // Procédure de Visa & Remboursement
        Route::get('/visa/{enrollmentId}', [VisaController::class, 'show']);
        Route::post('/visa/initiate', [VisaController::class, 'initiate']);
        Route::post('/visa/declare-outcome', [VisaController::class, 'declareOutcome']);

        // Demandes étudiantes à l'administration (avec pièces jointes et retours)
        Route::get('/student-requests', [StudentRequestController::class, 'index']);
        Route::post('/student-requests', [StudentRequestController::class, 'store']);
    });
});
