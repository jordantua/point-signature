<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;

Route::get('/', function () {
    return view('welcome');
});

// Route::post('/transactions', [TransactionController::class, 'store']);
// Route::get('/transactions', [TransactionController::class, 'index']);
// Route::post('/transactions/seed', [TransactionController::class, 'seed']);
