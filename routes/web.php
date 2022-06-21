<?php

use App\Http\Controllers\ButcheryController;
use App\Http\Controllers\ButcheryStockController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SausageController;
use App\Http\Controllers\SlaughterController;
use App\Http\Controllers\SpicesController;
use App\Http\Controllers\TestKraApi;
use App\Http\Controllers\TestKraApiController;
use App\Http\Controllers\ButcheryStockController;
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
Route::post('export-slaughter-lines-report', [SlaughterController::class, 'exportSlaughterLinesReport']);
Route::post('export-slaughter-for-nav', [SlaughterController::class, 'exportSlaughterForNav']);
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
Route::post('/butchery/sales-update', [ButcheryController::class, 'updateSalesData'])->name('butchery_sales_update');
Route::post('/butchery/transfers-update', [ButcheryController::class, 'updateTransfersData'])->name('butchery_transfers_update');
Route::post('/butchery/sales-returns', [ButcheryController::class, 'updateSalesReturns'])->name('butchery_sales_returns');
Route::post('/butchery/scale-2-update', [ButcheryController::class, 'updateScaleTwoData'])->name('butchery_scale2_update');
Route::get('/slaughter-data-ajax', [ButcheryController::class, 'loadSlaughterDataAjax'])->name('load_slaughter_data');
Route::get('/butchery/scale-3', [ButcheryController::class, 'scaleThree'])->name('butchery_scale3');
Route::post('/butchery/scale-3-save', [ButcheryController::class, 'saveScaleThreeData'])->name('butchery_scale3_save');
Route::post('/butchery/scale-3-update', [ButcheryController::class, 'updateScaleThreeData'])->name('butchery_scale3_update');
Route::get('/product_details_ajax', [ButcheryController::class, 'getProductDetailsAjax'])->name('product_type_ajax');
Route::get('/product_process_ajax', [ButcheryController::class, 'getProductProcessesAjax']);
Route::get('/butchery/products', [ButcheryController::class, 'products'])->name('butchery_products');
Route::post('/butchery/products/add', [ButcheryController::class, 'addProductProcess'])->name('butchery_products_add');
Route::post('/butchery/products/delete', [ButcheryController::class, 'deleteProductProcess'])->name('butchery_products_delete');
Route::get('/products/processes_ajax', [ButcheryController::class, 'loadProductionProcesses']);
Route::post('/products/processes_ajax/edit', [ButcheryController::class, 'loadProductionProcessesEdit']);
Route::get('/butchery/weight-split', [ButcheryController::class, 'weighSplitting'])->name('butchery_split_weights');
Route::post('/butchery/weight-split', [ButcheryController::class, 'saveWeighSplitting'])->name('butchery_split_save');
Route::post('/butchery/product-add', [ButcheryController::class, 'addProduct'])->name('butchery_add_product');
Route::post('/load_split_data', [ButcheryController::class, 'loadSplitData'])->name('load_split_data');
Route::get('/butchery/beheading-report', [ButcheryController::class, 'getBeheadingReport'])->name('butchery_beheading_report');
Route::post('export-beheading-combined-report', [ButcheryController::class, 'combinedBeheadingReport']);
Route::get('/butchery/breaking-report', [ButcheryController::class, 'getBrakingReport'])->name('butchery_breaking_report');
Route::post('export-breaking-combined-report', [ButcheryController::class, 'combinedBreakingReport']);
Route::get('/butchery/deboning-report', [ButcheryController::class, 'getDeboningReport'])->name('butchery_deboning_report');
Route::post('export-deboned-combined-report', [ButcheryController::class, 'combinedDeboningReport']);
Route::get('/butchery/sales-report', [ButcheryController::class, 'getSalesReport'])->name('butchery_sales_report');
Route::get('/butchery/transfers-report', [ButcheryController::class, 'getTransfersReport'])->name('butchery_transfers_report');
Route::get('/butchery/scale3-products', [ButcheryController::class, 'getDeboningProductsList'])->name('butchery_scale3_list');
Route::get('/butchery/scale-settings', [ButcheryController::class, 'scaleSettings'])->name('butchery_scale_settings');
Route::post('/butchery/update/scale-settings', [ButcheryController::class, 'UpdateScalesettings'])->name('butchery_update_scale_settings');
Route::get('/butchery/password', [ButcheryController::class, 'changePassword'])->name('butchery_change_password');

// Marination
Route::get('butchery-marination', [ButcheryController::class, 'weighMarination'])->name('weigh_marination');
Route::post('butchery-marination/save', [ButcheryController::class, 'saveMarinationData'])->name('save_marination');
Route::post('butchery-marination/update', [ButcheryController::class, 'updateMarinationData'])->name('update_marination');

/*-------------End Butchery------------------ */


/*-------------stocks------------------ */
Route::get('/stocks/dashboard', [ButcheryStockController::class, 'index'])->name('stock_dashboard');
Route::get('/stocks/transactions', [ButcheryStockController::class, 'stocksTransactions'])->name('stocks_transactions');
/*-------------End Admin------------------ */


/*-------------Sausage------------------ */
Route::get('/sausage/dashboard', [SausageController::class, 'index'])->name('sausage_dashboard');
Route::get('/sausage/today-entries/{filter?}', [SausageController::class, 'productionEntries'])->name('sausage_entries');
Route::get('/items', [SausageController::class, 'itemsList'])->name('items_list');
/*-------------End Admin------------------ */

/*-------------Start Spices------------------ */
Route::get('/spices/dashboard', [SpicesController::class, 'index'])->name('spices_dashboard');
Route::get('/spices/template-list', [SpicesController::class, 'templateList'])->name('template_list');
Route::post('template-list/upload', [SpicesController::class, 'importReceipts'])->name('template_upload');
Route::get('template-lines/{template_no}', [SpicesController::class, 'templateLines'])->name('template_lines');
Route::post('production/batches/create', [SpicesController::class, 'createBatchLines'])->name('batches_create');
Route::get('production/batches/{filter?}', [SpicesController::class, 'batchLists'])->name('batches_list');
Route::get('production/lines/{batch_no}', [SpicesController::class, 'productionLines'])->name('production_lines');
/*-------------End Spices------------------ */


Route::post('api/test', [SpicesController::class, 'createInvoice'])->name('verify_pin');
