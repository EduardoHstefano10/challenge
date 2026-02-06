<?php

use App\Http\Controllers\CreditReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/credit-report/export', [CreditReportController::class, 'export'])
    ->name('credit-report.export');
