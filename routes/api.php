<?php

use App\Http\Controllers\Api\TandonIngestController;
use Illuminate\Support\Facades\Route;

Route::post('/tandon/{tandon}/bacaan', [TandonIngestController::class, 'store'])
    ->middleware('throttle:60,1');
