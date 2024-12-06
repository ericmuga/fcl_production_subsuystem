<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\BeefLambController;
use App\Http\Controllers\ButcheryController;
use App\Http\Controllers\ButcheryStockController;
use App\Http\Controllers\ChoppingController;
use App\Http\Controllers\Despatch;
use App\Http\Controllers\DespatchController;
use App\Http\Controllers\FreshcutsBulkController;
use App\Http\Controllers\GenericController;
use App\Http\Controllers\HighCare1Controller;
use App\Http\Controllers\HighCare2Controller;
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


Route::group(['middleware' => ['web', 'auth']], function(){
    Route::get('logs', [LogViewerController::class, 'index']);
});

/*-------------auth------------------ */
Route::get('/', [LoginController::class, 'home'])->name('home');
Route::post('/', [LoginController::class, 'processlogin'])->name('process_login');
Route::get('/logout', [LoginController::class, 'getLogout'])->name('logout');
Route::get('/users', [LoginController::class, 'users'])->name('users');
Route::post('/user/permissions_axios/edit', [LoginController::class, 'getUserPermissionsAxios']);
Route::post('/user/update', [LoginController::class, 'updateUser'])->name('update_user');
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
Route::get('/slaughter/pending-etims', [SlaughterController::class, 'pendingEtimsData'])->name('pending_etims');
Route::post('/slaughter/update-pending-etims', [SlaughterController::class, 'updatePendingEtimsData'])->name('update_pending_etims');
Route::post('/send-sms', [SlaughterController::class, 'sendSmsCurl'])->name('send_sms');
Route::post('/update-sms-sent-status', [SlaughterController::class, 'updateSmsSentStatus'])->name('update_send_sms_status');
Route::get('/slaughter/receipts', [SlaughterController::class, 'importedReceipts'])->name('slaughter_receipts');
Route::post('/slaughter/import-receipts', [SlaughterController::class, 'importReceipts'])->name('slaughter_import_receipts');
Route::get('/slaughter/data-report', [SlaughterController::class, 'slaughterDataReport'])->name('slaughter_data_report');
Route::post('export-slaughter-combined-report', [SlaughterController::class, 'combinedSlaughterReport']);
Route::post('export-slaughter-lines-report', [SlaughterController::class, 'exportSlaughterLinesReport']);
Route::post('export-slaughter-for-nav', [SlaughterController::class, 'exportSlaughterForNav']);
Route::get('/slaughter/password', [SlaughterController::class, 'changePassword'])->name('slaughter_change_password');
Route::get('/slaughter/disease', [SlaughterController::class, 'disease'])->name('slaughter_disease');
Route::post('/slaughter/record-disease', [SlaughterController::class, 'recordDisease'])->name('record_disease');
Route::get('/slaughter/lairage_transfers', [SlaughterController::class, 'lairageTransfers'])->name('lairage_transfers');
Route::post('idt_lairage/save', [SlaughterController::class, 'saveLairageTransfer'])->name('save_idt_lairage');
Route::post('idt_lairage/update', [SlaughterController::class, 'updateLairageTransfer'])->name('update_idt_lairage');
Route::get('slaughter/offals', [SlaughterController::class, 'weighOffals'])->name('weigh_offals');
Route::post('slaughter/save-offals', [SlaughterController::class, 'saveOffalsWeight'])->name('save_offals_weight');
Route::get('slaughter/lairage-transfer-reports', [SlaughterController::class, 'lairageTransferReports'])->name('lairage_transfer_reports');
Route::get('slaughter/lairage-transfer-summary', [SlaughterController::class, 'exportLairageTransferSummaryReport'])->name('lairage_transfer_summary');
//queues
Route::get('/import-receipts-from-queue', [SlaughterController::class, 'importReceiptsFromQueue']);
/*-------------End Slaughter------------------ */


/*-------------Butchery------------------ */
Route::get('/butchery/dashboard', [ButcheryController::class, 'index'])->name('butchery_dashboard');
Route::get('/butchery/dashboard-v2', [ButcheryController::class, 'dashboardv2'])->name('butchery_dashboardv2');
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
Route::get('/butchery/scale-settings/{filter?}/{layout?}', [ButcheryController::class, 'scaleSettings'])->name('butchery_scale_settings');
Route::post('/butchery/update/scale-settings', [ButcheryController::class, 'UpdateScalesettings'])->name('butchery_update_scale_settings');
Route::get('/butchery/password', [ButcheryController::class, 'changePassword'])->name('butchery_change_password');
Route::post('export-beheading-lines-report', [ButcheryController::class, 'linesBeheadingReport'])->name('export-beheading-lines-report');
Route::post('export-breaking-lines-report', [ButcheryController::class, 'linesBreakingReport'])->name('export-breaking-lines-report');
Route::post('export-deboning-lines-report', [ButcheryController::class, 'linesDeboningReport'])->name('export-deboning-lines-report');
Route::get('butchery/idt/receive', [ButcheryController::class, 'receiveIdt'])->name('butchery_receive_idt');
Route::post('butchery/idt/receive/save', [ButcheryController::class, 'updateReceiveIdt'])->name('butchery_update_idt');

// Marination
Route::get('butchery-marination', [ButcheryController::class, 'weighMarination'])->name('weigh_marination');
Route::post('butchery-marination/save', [ButcheryController::class, 'saveMarinationData'])->name('save_marination');
Route::post('butchery-marination/update', [ButcheryController::class, 'updateMarinationData'])->name('update_marination');

Route::get('/insert', [ButcheryController::class, 'insertItemLocations']);

/*-------------End Butchery------------------ */


/*-------------stocks------------------ */
Route::get('/stocks/dashboard', [ButcheryStockController::class, 'index'])->name('stock_dashboard');
Route::get('/stocks/transactions', [ButcheryStockController::class, 'stocksTransactions'])->name('stocks_transactions');
/*-------------End stocks ------------------ */

/*-------------Fresh cuts and bulk------------------ */
Route::prefix('freshcuts_bulk')->group(function () {
    Route::get('/dashboard', [FreshcutsBulkController::class, 'index'])->name('freshcuts_bulk_dashboard');
    Route::get('/idt', [FreshcutsBulkController::class, 'getIdt'])->name('freshcuts_bulk_idt');
    Route::post('/idt/create', [FreshcutsBulkController::class, 'createIdt'])->name('freshcuts_create_idt');
    Route::post('/cancel/idt-issue', [FreshcutsBulkController::class, 'cancelIdtIssue'])->name('freshcuts_cancel_idt');
    Route::get('/idt-report/{filter?}', [FreshcutsBulkController::class, 'idtReport'])->name('freshcuts_bulk_report');
    Route::post('/idt-report/export', [FreshcutsBulkController::class, 'freshIdtReport'])->name('fresh_idt_report');
});

/*-------------End fresh cuts and bulk ------------------ */


/*-------------Sausage------------------ */
Route::get('/sausage/dashboard', [SausageController::class, 'index'])->name('sausage_dashboard');
Route::get('/sausage/create-idt', [SausageController::class, 'getIdt'])->name('sausage_idt');
Route::get('/sausage/receive-idt', [SausageController::class, 'getReceiveIdt'])->name('sausage_idt_receive');
Route::post('/item/details-axios', [SausageController::class, 'getItemDetails'])->name('item_details');
Route::post('/fetch-transferToLocations-axios', [SausageController::class, 'getTransferToLocations'])->name('fetch_transfer_locations');
Route::post('/check-user-rights', [SausageController::class, 'checkUserRights'])->name('check_user_rights');
Route::post('/validate-user', [SausageController::class, 'validateUser'])->name('validateUser');
Route::post('/save/idt', [SausageController::class, 'saveTransfer'])->name('save_idt');
Route::post('/edit/idt-issue', [SausageController::class, 'editIdtIssue'])->name('edit_idt_issue');
Route::post('sausage/receive/idt', [SausageController::class, 'updateReceiveIdt'])->name('update_idt_receive');
Route::get('/sausage/today-entries/{filter?}', [SausageController::class, 'productionEntries'])->name('sausage_entries');
Route::post('/export-sausage-entries', [SausageController::class, 'exportSausageEntries'])->name('export_sausage_entries');
Route::get('/sausage/idt-report/{filter?}', [SausageController::class, 'idtReport'])->name('sausage_idt_report');
Route::get('/items', [SausageController::class, 'itemsList'])->name('items_list');
Route::get('/per-batch-report/{filter?}', [SausageController::class, 'perBatchReport'])->name('per_batch_sausage');
Route::post('/sausage-get-batchno-axios', [SausageController::class, 'getBatchNoAxios']);
Route::get('/sausage/stuffing-weights', [SausageController::class, 'stuffingWeights'])->name('stuffing_weights');
Route::post('/sausage/chopping-receipts/save', [SausageController::class, 'saveStuffingWeights'])->name('save_stuffing_weights');
/*-------------End Admin------------------ */

/*-------------Start Spices------------------ */
Route::get('/spices/dashboard', [SpicesController::class, 'index'])->name('spices_dashboard');
Route::get('/spices/template-list', [SpicesController::class, 'templateList'])->name('template_list');
Route::get('/spices/items-list', [SpicesController::class, 'itemsList'])->name('spices_items');
Route::get('/spices/stock-list', [SpicesController::class, 'stockList'])->name('spices_stock');
Route::get('/spices/stock-lines/{filter?}', [SpicesController::class, 'stockLines'])->name('spices_stock_lines');
Route::get('/spices/stock-lines-info', [SpicesController::class, 'stockLineInfo'])->name('spices_stock_line_info');
Route::get('/spices/physical-stock', [SpicesController::class, 'physicalStock'])->name('spices_physical_stock');
Route::post('/spices/physical-stock/create', [SpicesController::class, 'addPhysicalStock'])->name('add_physical_stock');
Route::post('template-list/upload', [SpicesController::class, 'importTemplates'])->name('template_upload');
Route::get('template-lines/{template_no}', [SpicesController::class, 'templateLines'])->name('template_lines');
Route::post('production/batches/create', [SpicesController::class, 'createBatchLines'])->name('batches_create');
Route::get('production/batches/{filter?}', [SpicesController::class, 'batchLists'])->name('batches_list');
Route::get('production/lines/{batch_no}', [SpicesController::class, 'productionLines'])->name('production_lines');
Route::post('production/batch/update', [SpicesController::class, 'updateBatchItems'])->name('update_batch');
Route::post('production/batch/close', [SpicesController::class, 'closeOrPostBatch'])->name('close_post_batch');

#chopping
Route::get('chopping/batches/{filter?}', [ChoppingController::class, 'batchLists'])->name('chopping_batches_list');
Route::get('chopping/create/batch', [ChoppingController::class, 'choppingCreateBatch'])->name('chopping_batch_create');
Route::post('chopping/create/batch', [ChoppingController::class, 'choppingSaveBatch'])->name('chopping_batch_save');
Route::get('chopping/lines/{batch_no}/{batch_from?}', [ChoppingController::class, 'productionLines'])->name('chopping_production_lines');
Route::get('chopping/lines-report', [ChoppingController::class, 'postedLinesReport'])->name('chopping_posted_report');
Route::post('chopping/lines-report/export', [ChoppingController::class, 'postedLinesReportExport'])->name('chopping_posted_report_export');
Route::get('chopping/lines-report-summ/{filter?}', [ChoppingController::class, 'postedLinesReportSummary'])->name('chopping_posted_report_summary');
Route::post('chopping/batch/update', [ChoppingController::class, 'updateBatchItems'])->name('chopping_update_batch');
Route::post('chopping/batch/close', [ChoppingController::class, 'closeOrPostBatch'])->name('chopping_close_batch');

#chopping v2
Route::prefix('v2/chopping')->group(function () {
    Route::get('/weigh', [ChoppingController::class, 'weigh'])->name('chopping_weigh');
    Route::post('/make/run', [ChoppingController::class, 'makeChoppingRun']);
    Route::get('/fetch-open-runs', [ChoppingController::class, 'fetchOpenRuns']);
    Route::get('/fetch-products', [ChoppingController::class, 'fetchTemplateProducts']);
    Route::post('/save-weighings', [ChoppingController::class, 'saveChoppingWeights'])->name('save_chopping_weights');
    Route::post('/close-run', [ChoppingController::class, 'closeChoppingRun'])->name('close_chopping_run');
    Route::get('lines/{batch_no}', [ChoppingController::class, 'choppingLines'])->name('chopping_lines');
    Route::get('/lines-report', [ChoppingController::class, 'choppingLinesReport'])->name('chopping_v2_report');
    Route::post('/lines-export', [ChoppingController::class, 'choppingLinesV2Export'])->name('chopping_v2_export');
});

/*-------------End Spices------------------ */

/*-------------Start Despatch------------------ */
Route::get('/despatch/dashboard', [DespatchController::class, 'index'])->name('despatch_dashboard');
Route::get('/despatch/idt/{filter?}', [DespatchController::class, 'getIdt'])->name('despatch_idt');
Route::get('/despatch/issue-idt/{filter?}', [DespatchController::class, 'issueIdt'])->name('despatch_issue_idt');
Route::post('/despatch/issue-idt/save', [DespatchController::class, 'saveIssuedIdt'])->name('despatch_save_issued_idt');
Route::post('/receive/idt', [DespatchController::class, 'receiveTransfer'])->name('receive_idt');
Route::post('/receive/idt-freshcuts', [DespatchController::class, 'receiveTransferFreshcuts'])->name('receive_idt_fresh');
Route::get('/despatch/idt-report/{filter?}', [DespatchController::class, 'idtReport'])->name('despatch_idt_report');
Route::post('/despatch/idt-export', [DespatchController::class, 'exportIdtHistory'])->name('despatch_export_idt');
Route::get('/despatch/idt-variance/{filter?}', [DespatchController::class, 'idtVarianceReport'])->name('despatch_idt_variance');
Route::get('/despatch/idt-per-chiller', [DespatchController::class, 'idtStocksPerChiller'])->name('despatch_idt_per_chiller');
Route::get('/despatch/stocks-take', [DespatchController::class, 'takeStocks'])->name('take_stocks');
Route::post('/despatch/stocks-save', [DespatchController::class, 'saveStocks'])->name('save_stocks');
Route::post('/import-stocks', [DespatchController::class, 'importStocks'])->name('import_stocks_excel');
/*-------------End Despatch------------------ */

/*-------------Start HighCare1------------------ */
Route::prefix('highcare1')->group(function () {
    Route::get('/dashboard', [HighCare1Controller::class, 'index'])->name('highcare1_dashboard');
    Route::get('/idt', [HighCare1Controller::class, 'getIdt'])->name('highcare1_idt');
    Route::get('/idt-receive', [HighCare1Controller::class, 'getReceiveIdt'])->name('highcare_idt_receive');
    Route::post('/idt/receive-update', [HighCare1Controller::class, 'updateReceiveIdt'])->name('update_idt_receive_highcare');
    Route::get('/idt/report/{filter?}', [HighCare1Controller::class, 'idtReport'])->name('highcare1_idt_report');
    Route::post('/save/high-care-idt', [HighCare1Controller::class, 'saveTransfer'])->name('save_idt_high_care');

    Route::get('/idt-bulk', [HighCare1Controller::class, 'getIdtBulk'])->name('highcare1_idt_bulk');
    Route::post('/idt-bulk/save', [HighCare1Controller::class, 'saveIdtBulk'])->name('highcare1_idt_save_bulk');
});
/*-------------End HighCare1------------------ */

/*-------------Start Beef/Lamb------------------ */
Route::prefix('Beef')->group(function () {
    Route::get('/dashboard', [BeefLambController::class, 'index'])->name('beef_dashboard');
    Route::get('/slicing', [BeefLambController::class, 'getBeefSlicing'])->name('slicing_beef');
    Route::post('/save', [BeefLambController::class, 'saveBeefSlicing'])->name('beef_slicing_save');
    Route::get('/receiving', [BeefLambController::class, 'getIdtReceiving'])->name('idt_receiving');
    Route::get('/receiving-v2', [BeefLambController::class, 'getIdtReceivingV2'])->name('idt_receivingv2');
    Route::post('/idt-save', [BeefLambController::class, 'saveIdtReceiving'])->name('save_idt_receiving');
    Route::post('/idt-update', [BeefLambController::class, 'updateIdtReceiving'])->name('update_idt_receiving');
});
/*-------------End Beef------------------ */

/*-------------Start Assets----------------- */
Route::prefix('asset')->group(function () {
    Route::get('/dashboard', [AssetController::class, 'index'])->name('assets_dashboard');
    Route::get('/create', [AssetController::class, 'createMovement'])->name('create_movement');
    Route::post('/save', [AssetController::class, 'saveMovement'])->name('save_movement');
    Route::post('/cancel', [AssetController::class, 'cancelMovement'])->name('assets_cancel_trans');
    Route::get('/fetch-data', [AssetController::class, 'fetchData'])->name('assets_fetch_data');
    Route::get('/fetch-depts', [AssetController::class, 'fetchDeptsData'])->name('assets_fetch_depts');
    Route::get('/fetch-employees', [AssetController::class, 'getAssetEmployeeList'])->name('assets_fetch_employees');
    Route::post('/check-user', [AssetController::class, 'validateUserAssets'])->name('validateUserAsset');
    Route::get('/movement-history', [AssetController::class, 'movementHistory'])->name('movement_history');
    Route::get('/list', [AssetController::class, 'assetList'])->name('asset_list');
});
/*-------------End Assets------------------ */

/*-------------Start Scale----------------- */
Route::prefix('scale-settings')->group(function () {
    Route::get('/{section}', [SlaughterController::class, 'scaleSettings'])->name('scale_settings');
    Route::post('/update', [SlaughterController::class, 'UpdateScaleSettings'])->name('update_scale_settings');
});
/*-------------End Scale------------------ */

