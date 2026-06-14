<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CalculationController;
use App\Http\Controllers\ProductDemandController;

// Authentication routes (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Protected routes (Admin Auth)
Route::middleware('auth')->group(function () {
    // Session Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
    // Redirect Root to Dashboard
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Products Master Data CRUD & CSV operations
    Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
    Route::get('/products/template', [ProductController::class, 'downloadTemplate'])->name('products.template');
    Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');
    Route::resource('products', ProductController::class);

    // Calculations Hadley-Within & Exports
    Route::get('/calculations/export', [CalculationController::class, 'exportCsv'])->name('calculations.export');
    Route::get('/calculations/{calculation}/print', [CalculationController::class, 'print'])->name('calculations.print');
    Route::resource('calculations', CalculationController::class)->except(['edit', 'update']);

    // Demands Data Module
    Route::resource('demands', ProductDemandController::class);
});
