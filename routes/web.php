<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ButcheryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SlaughterController;
use Illuminate\Support\Facades\Route;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
Route::get('logs', [LogViewerController::class,'index']);

/*-------------auth------------------ */
Route::get('/', [LoginController::class, 'getLogin'])->name('login');
Route::post('/', [LoginController::class, 'processlogin'])->name('process_login');
Route::get('/logout', [LoginController::class, 'getLogout'])->name('logout');

/*-------------End auth------------------ */


/*-------------Slaughter------------------ */
Route::get('/slaughter/dashboard', [SlaughterController::class, 'index'])->name('slaughter_dashboard');
Route::get('/slaughter/weigh', [SlaughterController::class, 'weigh'])->name('slaughter_weigh');
Route::get('/scale-ajax', [SlaughterController::class, 'loadWeighDataAjax'])->name('load_weigh_data');
Route::get('/slaughter/receipts', [SlaughterController::class, 'importedReceipts'])->name('slaughter_receipts');
Route::get('/slaughter/data-report', [SlaughterController::class, 'slaughterDataReport'])->name('slaughter_data_report');
Route::get('/slaughter/scale-settings', [SlaughterController::class, 'scaleSettings'])->name('slaughter_scale_settings');
Route::get('/slaughter/password', [SlaughterController::class, 'changePassword'])->name('slaughter_change_password');
/*-------------End Slaughter------------------ */


/*-------------Butchery------------------ */
Route::get('/butchery/dashboard', [ButcheryController::class, 'index'])->name('butchery_dashboard');
Route::get('/butchery/scale-1-2', [ButcheryController::class, 'scaleOneAndTwo'])->name('butchery_scale1_2');
Route::get('/butchery/scale-3', [ButcheryController::class, 'scaleThree'])->name('butchery_scale3');
Route::get('/butchery/products', [ButcheryController::class, 'products'])->name('butchery_products');
Route::get('/butchery/scale-settings', [ButcheryController::class, 'scaleSettings'])->name('butchery_scale_settings');
Route::get('/butchery/password', [ButcheryController::class, 'changePassword'])->name('butchery_change_password');
/*-------------End Butchery------------------ */


/*-------------Admin------------------ */
Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin_dashboard');
Route::get('/admin/users', [AdminController::class, 'getUsers'])->name('admin_users');
Route::post('/admin/add/user', [AdminController::class, 'addUser'])->name('admin_add_user');
Route::get('/admin/password', [AdminController::class, 'changePassword'])->name('admin_change_password');
/*-------------End Admin------------------ */
