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
Route::get('logs', [LogViewerController::class, 'index']);

/*-------------auth------------------ */
Route::get('/', [LoginController::class, 'getLogin'])->name('login');
Route::post('/', [LoginController::class, 'processlogin'])->name('process_login');
Route::get('/redirecting', [LoginController::class, 'getSectionRedirect'])->name('redirect_page');
Route::get('/logout', [LoginController::class, 'getLogout'])->name('logout');

/*-------------End auth------------------ */


/*-------------Slaughter------------------ */
Route::get('/slaughter/dashboard', [SlaughterController::class, 'index'])->name('slaughter_dashboard');
Route::get('/slaughter/weigh', [SlaughterController::class, 'weigh'])->name('slaughter_weigh');
Route::get('/scale-ajax', [SlaughterController::class, 'loadWeighDataAjax'])->name('load_weigh_data');
Route::get('/scale-ajax-2', [SlaughterController::class, 'loadWeighMoreDataAjax'])->name('load_weigh_more_data');
Route::get('slaughter/read-scale-api-service', [SlaughterController::class, 'readScaleApiService']);
Route::get('slaughter/comport-list-api-service', [SlaughterController::class, 'comportlistApiService']);
Route::post('/slaughter/save-weigh', [SlaughterController::class, 'saveWeighData'])->name('save_weigh_data');
Route::post('/slaughter/save-missing', [SlaughterController::class, 'saveMissingSlapData'])->name('save_missing_data');
Route::get('/slaughter/missing-slaps', [SlaughterController::class, 'missingSlapData'])->name('missing_slap_data');
Route::get('/slaughter/receipts', [SlaughterController::class, 'importedReceipts'])->name('slaughter_receipts');
Route::post('/slaughter/import-receipts', [SlaughterController::class, 'importReceipts'])->name('slaughter_import_receipts');
Route::get('/slaughter/data-report', [SlaughterController::class, 'slaughterDataReport'])->name('slaughter_data_report');
Route::post('export-slaughter-combined-report', [SlaughterController::class, 'combinedSlaughterReport']);
Route::get('/slaughter/scale-settings', [SlaughterController::class, 'scaleSettings'])->name('slaughter_scale_settings');
Route::post('/slaughter/update/scale-settings', [SlaughterController::class, 'UpdateScalesettings'])->name('slaughter_update_scale_settings');
Route::get('/slaughter/password', [SlaughterController::class, 'changePassword'])->name('slaughter_change_password');
/*-------------End Slaughter------------------ */


/*-------------Butchery------------------ */
Route::get('/butchery/dashboard', [ButcheryController::class, 'index'])->name('butchery_dashboard');
Route::get('/butchery/scale-1-2', [ButcheryController::class, 'scaleOneAndTwo'])->name('butchery_scale1_2');
Route::get('butchery/read-scale-api-service', [ButcheryController::class, 'readScaleApiService']);
Route::get('butchery/comport-list-api-service', [ButcheryController::class, 'comportlistApiService']);
Route::post('/butchery/scale-1-save', [ButcheryController::class, 'saveScaleOneData'])->name('butchery_scale1_save');
Route::post('/butchery/scale-2-save', [ButcheryController::class, 'saveScaleTwoData'])->name('butchery_scale2_save');
Route::post('/butchery/scale-1-update', [ButcheryController::class, 'updateScaleOneData'])->name('butchery_scale1_update');
Route::post('/butchery/scale-2-update', [ButcheryController::class, 'updateScaleTwoData'])->name('butchery_scale2_update');
Route::get('/slaughter-data-ajax', [ButcheryController::class, 'loadSlaughterDataAjax'])->name('load_slaughter_data');
Route::get('/butchery/scale-3', [ButcheryController::class, 'scaleThree'])->name('butchery_scale3');
Route::post('/butchery/scale-3-save', [ButcheryController::class, 'saveScaleThreeData'])->name('butchery_scale3_save');
Route::get('/product_type_ajax', [ButcheryController::class, 'getProductTypeAjax'])->name('product_type_ajax');
Route::get('/product_process_ajax', [ButcheryController::class, 'getProductProcessesAjax']);
Route::get('/butchery/products', [ButcheryController::class, 'products'])->name('butchery_products');
Route::get('/butchery/weight-split', [ButcheryController::class, 'weighSplitting'])->name('butchery_split_weights');
Route::post('/butchery/weight-split', [ButcheryController::class, 'saveWeighSplitting'])->name('butchery_split_save');
Route::post('/butchery/product-add', [ButcheryController::class, 'addProduct'])->name('butchery_add_product');
Route::post('/load_split_data', [ButcheryController::class, 'loadSplitData'])->name('load_split_data');
Route::get('/butchery/beheading-report', [ButcheryController::class, 'getBeheadingReport'])->name('butchery_beheading_report');
Route::post('export-beheading-combined-report', [ButcheryController::class, 'combinedBeheadingReport']);
Route::get('/butchery/braking-report', [ButcheryController::class, 'getBrakingReport'])->name('butchery_breaking_report');
Route::post('export-breaking-combined-report', [ButcheryController::class, 'combinedBreakingReport']);
Route::get('/butchery/deboning-report', [ButcheryController::class, 'getDeboningReport'])->name('butchery_deboning_report');
Route::post('export-deboned-combined-report', [ButcheryController::class, 'combinedDeboningReport']);
Route::get('/butchery/sales-report', [ButcheryController::class, 'getSalesReport'])->name('butchery_sales_report');
Route::get('/butchery/scale-settings', [ButcheryController::class, 'scaleSettings'])->name('butchery_scale_settings');
Route::post('/butchery/update/scale-settings', [ButcheryController::class, 'UpdateScalesettings'])->name('butchery_update_scale_settings');
Route::get('/butchery/password', [ButcheryController::class, 'changePassword'])->name('butchery_change_password');
/*-------------End Butchery------------------ */


/*-------------Admin------------------ */
Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin_dashboard');
Route::get('/admin/users', [AdminController::class, 'getUsers'])->name('admin_users');
Route::post('/admin/add/user', [AdminController::class, 'addUser'])->name('admin_add_user');
Route::get('/admin/password', [AdminController::class, 'changePassword'])->name('admin_change_password');
/*-------------End Admin------------------ */
