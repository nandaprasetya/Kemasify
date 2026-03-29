<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DesignProjectController;
use App\Http\Controllers\AiGenerationController;
use App\Http\Controllers\RencerController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProductModelController;
use App\Http\Controllers\Admin\AiJobController;
use App\Http\Controllers\Admin\RenderJobController;

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
        Route::get('/dashboard', [DesignProjectController::class, 'index'])->name('index');
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

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
 
    // ── Dashboard ─────────────────────────────────────────────────────────────
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
 
    // ── Users ─────────────────────────────────────────────────────────────────
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/',                        [UserController::class, 'index'])->name('index');
        Route::get('/{user}',                  [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit',             [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}',                  [UserController::class, 'update'])->name('update');
        Route::delete('/{user}',               [UserController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/give-tokens',     [UserController::class, 'giveTokens'])->name('give-tokens');
        Route::post('/{user}/toggle-admin',    [UserController::class, 'toggleAdmin'])->name('toggle-admin');
        Route::post('/{user}/upgrade-premium', [UserController::class, 'upgradePremium'])->name('upgrade-premium');
    });
 
    // ── Product Models ────────────────────────────────────────────────────────
    Route::prefix('product-models')->name('product-models.')->group(function () {
        Route::get('/',                          [ProductModelController::class, 'index'])->name('index');
        Route::get('/create',                    [ProductModelController::class, 'create'])->name('create');
        Route::post('/',                         [ProductModelController::class, 'store'])->name('store');
        Route::get('/{productModel}/edit',       [ProductModelController::class, 'edit'])->name('edit');
        Route::put('/{productModel}',            [ProductModelController::class, 'update'])->name('update');
        Route::delete('/{productModel}',         [ProductModelController::class, 'destroy'])->name('destroy');
        Route::post('/{productModel}/toggle',    [ProductModelController::class, 'toggleActive'])->name('toggle');
    });
 
    // ── AI Jobs ───────────────────────────────────────────────────────────────
    Route::prefix('ai-jobs')->name('ai-jobs.')->group(function () {
        Route::get('/',              [AiJobController::class, 'index'])->name('index');
        Route::get('/{aiJob}',       [AiJobController::class, 'show'])->name('show');
        Route::post('/{aiJob}/retry',[AiJobController::class, 'retry'])->name('retry');
        Route::delete('/{aiJob}',    [AiJobController::class, 'destroy'])->name('destroy');
    });
 
    // ── Render Jobs ───────────────────────────────────────────────────────────
    Route::prefix('render-jobs')->name('render-jobs.')->group(function () {
        Route::get('/',                   [RenderJobController::class, 'index'])->name('index');
        Route::get('/{renderJob}',        [RenderJobController::class, 'show'])->name('show');
        Route::post('/{renderJob}/retry', [RenderJobController::class, 'retry'])->name('retry');
        Route::delete('/{renderJob}',     [RenderJobController::class, 'destroy'])->name('destroy');
    });
});