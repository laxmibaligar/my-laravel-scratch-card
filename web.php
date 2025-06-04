<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckListController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\StyleController;
use App\Http\Controllers\StridesController;
use App\Http\Controllers\SpinController;
use App\Http\Controllers\KurlonController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\ReporteesController;




// scratch card
Route::get('scratch-card.html/{hash?}',[\App\Http\Controllers\ScratchCardController::class, 'showscratchcard']);
Route::post('/save-scratch-card', [\App\Http\Controllers\ScratchCardController::class, 'saveScratchCard']);


// Route::get('/insert-gifts', [\App\Http\Controllers\ScratchCardController::class, 'insertGifts']);

