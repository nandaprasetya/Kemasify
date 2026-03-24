<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DesignProjectController;
use App\Http\Controllers\AiGenerationController;
use App\Http\Controllers\RencerController;
use App\Http\Controllers\TokenController;

// ─── Public ──────────────────────────────────────────────────────────────────
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('landing');
})->name('home');

// ─── Auth ─────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ─── Authenticated ────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DesignProjectController::class, 'index'])->name('dashboard');

    // ── Projects ──────────────────────────────────────────────────────────────
    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/new',           [DesignProjectController::class, 'selectModel'])->name('select-model');
        Route::post('/',             [DesignProjectController::class, 'create'])->name('create');
        Route::get('/{slug}/editor', [DesignProjectController::class, 'editor'])->name('editor');
        Route::delete('/{slug}',     [DesignProjectController::class, 'destroy'])->name('destroy');

        // Canvas & Upload
        Route::post('/{slug}/upload-design', [DesignProjectController::class, 'uploadDesign'])->name('upload-design');
        Route::post('/{slug}/save-canvas',   [DesignProjectController::class, 'saveCanvas'])->name('save-canvas');
    });

    // ── AI Generation ─────────────────────────────────────────────────────────
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::post('/generate/{slug}',   [AiGenerationController::class, 'generate'])->name('generate');
        Route::get('/status/{jobId}',     [AiGenerationController::class, 'status'])->name('status');
        Route::post('/cancel/{jobId}',    [AiGenerationController::class, 'cancel'])->name('cancel');
    });

    // ── Render ────────────────────────────────────────────────────────────────
    Route::prefix('render')->name('render.')->group(function () {
        Route::post('/{slug}',         [RencerController::class, 'requestRender'])->name('request');
        Route::get('/status/{jobId}',  [RencerController::class, 'status'])->name('status');
        Route::get('/download/{slug}', [RencerController::class, 'download'])->name('download');
    });

    // ── Token ─────────────────────────────────────────────────────────────────
    Route::prefix('tokens')->name('tokens.')->group(function () {
        Route::get('/',       [TokenController::class, 'index'])->name('index');
        Route::get('/status', [TokenController::class, 'status'])->name('status');
        Route::post('/refill',[TokenController::class, 'refill'])->name('refill');
    });
});