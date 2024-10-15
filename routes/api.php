<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\SausageController;
use App\Http\Controllers\SlaughterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/barcodes-insert', [SausageController::class, 'insertBarcodes']);
Route::post('/last-insert', [SausageController::class, 'lastInsert']);

//public Apis
Route::middleware(['token_check', 'throttle:60,1'])->group(function () {
    Route::post('/v1/fetch-slaughter-data', [ApiController::class, 'getSlaughterData']);
    Route::post('/v1/fetch-missing-slaps', [ApiController::class, 'missingSlapData']);
    Route::post('/v1/fetch-beheading-data', [ApiController::class, 'getBeheadingData']);
    Route::post('/v1/fetch-breaking-data', [ApiController::class, 'getBrakingData']);
    Route::post('/v1/fetch-deboning-data', [ApiController::class, 'getDeboningData']);
    Route::post('/v1/fetch-chopping-data', [ApiController::class, 'getChoppingData']);
});

//without token APIS
Route::post('/v1/save/slaughter-receipts', [ApiController::class, 'saveSlaughterReceipts']);
Route::post('/v1/push/slaughter-lines', [ApiController::class, 'pushSlaughterLines']);
Route::post('/v1/listen/slaughter-receipts', [SlaughterController::class, 'consumeFromQueue']);
