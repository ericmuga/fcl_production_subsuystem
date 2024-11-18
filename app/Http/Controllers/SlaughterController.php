<?php

namespace App\Http\Controllers;

use App\Exports\SlaughterCombinedExport;
use App\Exports\SlaughterForNavExport;
use App\Exports\SlaughterLinesExport;
use App\Imports\ReceiptsImport;
use App\Models\Helpers;
use App\Models\MissingSlapData;
use App\Models\Receipt;
use App\Models\SlaughterData;
use App\Models\Offals;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

if (!defined('SOCKET_EAGAIN')) {
    define('SOCKET_EAGAIN', 11);
}

if (!defined('SOCKET_EWOULDBLOCK')) {
    define('SOCKET_EWOULDBLOCK', 11);
}

if (!defined('SOCKET_EINTR')) {
    define('SOCKET_EINTR', 4);
}
class SlaughterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['importReceiptsFromQueue', 'publishDummyData']);
    }

    public function index(Helpers $helpers)
    {
        $title = "dashboard";

        $slaughtered = DB::table('slaughter_data')
            ->whereDate('created_at', Carbon::today())
            ->count();

        $lined_up = Cache::remember('lined_up', now()->addMinutes(120), function () {
            return DB::table('receipts')
                ->whereDate('slaughter_date', Carbon::today())
                ->sum('receipts.received_qty');
        });

        $missing_slaps = DB::table('missing_slap_data')
            ->whereDate('created_at', Carbon::today())
            ->count();

        $total_weight = DB::table('slaughter_data')
            ->whereDate('created_at', Carbon::today())
            ->sum('slaughter_data.net_weight');

        $slaughtered_baconers = DB::table('slaughter_data')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', 'G0110')
            ->sum('slaughter_data.net_weight');

        $slaughtered_sows = DB::table('slaughter_data')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', 'G0111')
            ->sum('slaughter_data.net_weight');

        $slaughtered_suckling = DB::table('slaughter_data')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', 'G0113')
            ->sum('slaughter_data.net_weight');

        $date = Carbon::today();

        return view('slaughter.dashboard', compact('title', 'slaughtered', 'lined_up', 'missing_slaps', 'date', 'helpers', 'total_weight', 'slaughtered_baconers', 'slaughtered_sows', 'slaughtered_suckling'));
    }

    public function weigh(Helpers $helpers)
    {
        $title = "weigh";

        $configs = Cache::remember('weigh_configs', now()->addMinutes(120), function () {
            return DB::table('scale_configs')
                ->where('section', 'slaughter')
                ->select('tareweight', 'comport')
                ->get()->toArray();
        });

        $receipts = Cache::remember('weigh_receipts', now()->addMinutes(120), function () {
            return DB::table('receipts')
                ->whereDate('slaughter_date', Carbon::today())
                ->select('vendor_tag')
                ->get();
        });

        $slaughter_data = DB::table('slaughter_data')
            ->whereDate('slaughter_data.created_at', Carbon::today())
            ->leftJoin('carcass_types', 'slaughter_data.item_code', '=', 'carcass_types.code')
            ->select('slaughter_data.*', 'carcass_types.description')
            ->orderBy('slaughter_data.created_at', 'DESC')
            ->get();

        $carcass_types = Cache::remember('carcass_types_list', now()->addMinutes(480), function () {
            return DB::table('carcass_types')
                ->select('code', 'description')
                ->get();
        });

        $slaps = DB::table('missing_slap_data')
            ->whereDate('missing_slap_data.created_at', Carbon::today())
            ->leftJoin('carcass_types', 'missing_slap_data.item_code', '=', 'carcass_types.code')
            ->select('missing_slap_data.*', 'carcass_types.description')
            ->orderBy('missing_slap_data.created_at', 'DESC')
            ->get();

        return view('slaughter.weigh', compact('title', 'configs', 'receipts', 'slaughter_data', 'helpers', 'carcass_types', 'slaps'));
    }

    public function loadWeighDataAjax(Request $request)
    {
        $data = DB::table('receipts')
            ->whereDate('slaughter_date', Carbon::today())
            ->where('vendor_tag', $request->slapmark)
            ->where('item_code', $request->carcass_type)
            ->select('receipt_no', 'item_code', 'vendor_no', 'vendor_name')
            ->first();

        return response()->json($data);
    }

    public function loadWeighMoreDataAjax(Request $request)
    {
        $total_per_slap = DB::table('receipts')
            ->whereDate('slaughter_date', '>=', today()->subDays(1))
            ->where('vendor_tag', $request->slapmark)
            ->where('item_code', $request->carcass_type)
            ->sum('receipts.received_qty');

        $total_per_vendor = DB::table('receipts')
            ->whereDate('slaughter_date', '>=', today()->subDays(1))
            ->where('vendor_no', $request->vendor_no)
            ->sum('receipts.received_qty');

        // transcoding from livestock code carcass code to look up in the slaughter data
        if ($request->carcass_type == "G0101") {
            // pig livestock
            $c_type = "G0110";
        }
        if ($request->carcass_type == "G0102") {
            // sow livestock
            $c_type = "G0111";
        }
        if ($request->carcass_type == "G0104") {
            // suckling livestock
            $c_type = "G0113";
        }

        $total_weighed = DB::table('slaughter_data')
            ->whereDate('created_at', Carbon::today())
            ->where('slapmark', $request->slapmark)
            ->where('item_code', $c_type)
            ->count();

        $dataArray = array('total_per_vendor' => $total_per_vendor, 'total_per_slap' => $total_per_slap, 'total_weighed' => $total_weighed);

        return response()->json($dataArray);
    }

    public function readScaleApiService(Request $request, Helpers $helpers)
    {
        $result = $helpers->get_scale_read($request->comport);
        return response()->json($result);
    }

    public function comportlistApiService(Helpers $helpers)
    {
        $result = $helpers->get_comport_list();

        return response()->json($result);
    }

    public function saveWeighData(Request $request, Helpers $helpers)
    {
        try {
            // try save
            $manual_weight = 0;
            if ($request->manual_weight == 'on') {
                $manual_weight = 1;
            }

            $data = [
                'receipt_no' => $request->receipt_no,
                'slapmark' => $request->slapmark,
                'item_code' => $request->carcass_type,
                'vendor_no' => $request->vendor_no,
                'vendor_name' => $request->vendor_name,
                'actual_weight' => $request->reading,
                'net_weight' => $request->net,
                'settlement_weight' => $request->settlement_weight,
                'vendor_name' => $request->vendor_name,
                'meat_percent' => $request->meat_percent,
                'classification_code' => $request->classification_code,
                'manual_weight' => $manual_weight,
                'user_id' => Auth::id(),
                'is_imported' => false, // Set imported to false initially
            ];

            // DB::table('slaughter_data')->insert($data);
            $id = DB::table('slaughter_data')->insertGetId($data);

            // Add the ID and timestamps to the data array for queueing
            $data['id'] = $id;
            $data['timestamp'] = now()->toDateTimeString();

            // Publish data to RabbitMQ
            $helpers->publishToQueue($data, 'slaughter_line.bc');

            Toastr::success('record added successfully', 'Success');
            return redirect()
                ->back()
                ->withInput();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        } catch (\PhpAmqpLib\Exception\AMQPChannelClosedException $e) {
            // Handle the channel closed exception
            Log::error('Channel connection is closed: ' . $e->getMessage());
            return; // Exit the function if the channel is closed
        }
    }

    public function publishDummyData(request $request, Helpers $helpers)
    {
        $channel = $helpers->getRabbitMQChannel();

        // Declare the exchange if it does not exist
        $exchange = 'fcl.exchange.direct';
        $channel->exchange_declare($exchange, 'direct', false, true, false);

        $data = $request->all();

        $msg = new AMQPMessage(json_encode($data), [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ]);

        $channel->basic_publish($msg, $exchange, 'slaughter_receipts.wms');

        Log::info('Dummy data published to slaughter_receipts.wms exchange.');
        return 1;
    }

    public function saveMissingSlapData(Request $request, Helpers $helpers)
    {
        try {
            // try save
            $new = new MissingSlapData();
            $new->slapmark = $request->ms_slap;
            $new->item_code = $request->ms_carcass_type;
            $new->actual_weight = $request->ms_reading;
            $new->net_weight = $request->ms_net;
            $new->settlement_weight = $request->ms_settlement_weight;
            $new->meat_percent = $request->ms_meat_pc;
            $new->classification_code = isset($request->ms_classification) ? $request->ms_classification : null;
            $new->user_id = Auth::id();
            $new->save();

            Toastr::success('record added successfully', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
    }

    public function missingSlapData(Request $request, Helpers $helpers)
    {
        $title = "SlapData";

        $slaps = DB::table('missing_slap_data')
            ->leftJoin('carcass_types', 'missing_slap_data.item_code', '=', 'carcass_types.code')
            ->select('missing_slap_data.*', 'carcass_types.description')
            ->orderBy('missing_slap_data.created_at', 'DESC')
            ->take(1000)
            ->get();

        return view('slaughter.missing_slapmarks', compact('title', 'slaps', 'helpers'));
    }

    public function pendingEtimsData(Helpers $helpers)
    {
        $title = "Purchases for Etims Update";

       $results = DB::connection('main')->table('FCL$Purch_ Inv_ Header as a')
            ->select(
                'v.Phone No_ as phonenumber',
                'a.Uncommitted as is_sms_sent',
                DB::raw('a.[Buy-from Vendor No_] as vendor_no'),
                DB::raw('a.[Buy-from Vendor Name] as vendor_name'),
                DB::raw('a.[Your Reference] as settlement_no'),
                DB::raw('SUM(CASE WHEN b.[Type] <> 1 THEN b.Quantity ELSE 0 END) AS totalWeight'),
                DB::raw('COALESCE(SUM(b.Amount), 0) - 
                    (SELECT ISNULL(SUM(Amount), 0) 
                        FROM [FCL$Purch_ Cr_ Memo Line] as c 
                        INNER JOIN [FCL$Purch_ Cr_ Memo Hdr_] as d 
                            ON d.No_ = c.[Document No_] 
                            AND RIGHT(d.[Vendor Cr_ Memo No_], 2) <> \'-R\'
                            AND d.[Your Reference] = a.[Your Reference]) AS netAmount'),
                    DB::raw('(CASE WHEN SUM(CASE WHEN b.[Type] <> 1 THEN b.Quantity ELSE 0 END) = 0 THEN 0 ELSE
                        (COALESCE(SUM(b.Amount), 0) - 
                            (SELECT ISNULL(SUM(Amount), 0) 
                                FROM [FCL$Purch_ Cr_ Memo Line] as c 
                                INNER JOIN [FCL$Purch_ Cr_ Memo Hdr_] as d 
                                    ON d.No_ = c.[Document No_] 
                                    AND d.[Your Reference] = a.[Your Reference])) / 
                        (SUM(CASE WHEN b.[Type] <> 1 THEN b.Quantity ELSE 0 END)) END) AS unitPrice')

        )
        ->join('FCL$Purch_ Inv_ Line as b', 'a.No_', '=', 'b.Document No_')
        ->join('FCL$Vendor as v', 'a.Pay-to Vendor No_', '=', 'v.No_')
        ->where('a.Vendor Posting Group', '=', 'PIGFARMERS')
        ->where('a.Buy-from County', '=', '')
        ->where('a.Posting Date', '>=', '2024-05-02 00:00:00.000')
        ->where('a.Your Reference', '<>', '')
        ->where(function ($query) {
            $query->whereRaw('(
                SELECT COUNT(*) 
                FROM [FCL$Purch_ Cr_ Memo Hdr_] as e 
                WHERE e.[Vendor Cr_ Memo No_] = CONCAT(a.[Your Reference], \'-R\')
            ) = 0');
        })
        ->groupBy('a.Your Reference', 'a.Buy-from Vendor No_', 'v.Phone No_', 'a.Buy-from Vendor No_', 'a.Buy-from Vendor Name', 'a.Your Reference', 'a.Uncommitted')
        ->orderBy('a.Buy-from Vendor No_')
        ->get();

        return view('slaughter.pending-etims', compact('title', 'results', 'helpers'));
    }

    public function updatePendingEtimsData(Request $request, Helpers $helpers)
    {
        try {
            
            // info($request->item_name.':'.$request->cu_inv_no);            
            $helpers->forgetCache('pendings_for_etims');

            DB::transaction(function () use ($request, $helpers) {
                DB::connection('main')
                    ->table('FCL$Purch_ Inv_ Header')
                    ->where('Your Reference', $request->item_name) // Use column name directly without alias
                    ->update([
                        'Buy-from County' => $request->cu_inv_no,
                    ]);
                
                DB::table('settlement_purchase_invoices')
                    ->insert([
                        'settlement_no' => $request->item_name,
                        'cu_inv_no' => $request->cu_inv_no,
                        'user_id' => Auth::id(),
                    ]);
            });

            Toastr::success("Purchase Invoice no for  {$request->item_name} updated successfully", 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            $helpers->CustomErrorlogger($e->getMessage(),  __FUNCTION__);
            return back();
        }
    }

    public function sendSmsCurl(Request $request)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('app.sms_send_url'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($request->all()),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
            CURLOPT_SSL_VERIFYPEER => false, // Disable SSL certificate verification
            CURLOPT_SSL_VERIFYHOST => false
        ));

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            info("CURL Error: {$error_msg}");
        }
        
        curl_close($curl);
        // info($response);
        return $response;
    }

    public function updateSmsSentStatus(Request $request)
    {
        try {
            $settlementNo = $request->input('settlement_no');

            DB::connection('main')
                ->table('FCL$Purch_ Inv_ Header')
                ->where('Your Reference', $settlementNo)
                ->update(['Uncommitted' => true]);

            return response()->json(['success' => true, 'message' => 'SMS status for ' . $settlementNo . ' updated successfully']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update SMS status. Error: ' . $e->getMessage()]);
        }
    }

    public function importedReceipts(Helpers $helpers)
    {
        $title = "receipts";

        $receipts = Cache::remember('imported_receipts', now()->addMinutes(120), function () {
            return DB::table('receipts')
                ->whereDate('created_at', '>=', Carbon::yesterday())
                ->orderBy('created_at', 'DESC')
                ->take(1000)
                ->get();
        });

        return view('slaughter.receipts', compact('title', 'receipts', 'helpers'));
    }

    public function importReceipts(Request $request, Helpers $helpers)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,txt',
            'slaughter_date' => 'required',

        ]);

        if ($validator->fails()) {
            # failed validation
            $messages = $validator->errors();
            foreach ($messages->all() as $message) {
                Toastr::error($message, 'Error!');
            }
            return back();
        }

        // upload
        $database_date = Carbon::parse($request->slaughter_date);

        // forgetCache data
        $helpers->forgetCache('lined_up');
        $helpers->forgetCache('weigh_receipts');
        $helpers->forgetCache('imported_receipts');

        try {
            //code...
            DB::transaction(function () use ($request, $helpers, $database_date) {

                //delete existing records of same slaughter date
                // DB::table('receipts')->where('slaughter_date', $database_date)->delete();

                $fileD = fopen($request->file, "r");
                // $column = fgetcsv($fileD); // skips first row as header

                while (!feof($fileD)) {
                    $rowData[] = fgetcsv($fileD);
                }

                foreach ($rowData as $key => $row) {

                    DB::table('receipts')->updateOrInsert(
                        [
                            'enrolment_no' => $row[0],
                            'vendor_tag' => $row[1],
                            'slaughter_date' => $database_date,
                        ],
                        [
                            'receipt_no' => $row[2],
                            'vendor_no' => $row[3],
                            'vendor_name' => $row[4],
                            'receipt_date' => $row[5],
                            'item_code' => $row[6],
                            'description' => $row[7],
                            'received_qty' => $row[8],
                            'user_id' => Auth::id(),
                            'slaughter_date' => $database_date,
                        ]
                    );
                }
            });

            Toastr::success('receipts uploaded successfully', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error Occurred. Wrong Data format!. Records not saved!');
            return back()
                ->withInput();
        }
    }

    public function importReceiptsFromQueue(Helpers $helpers)
    {
        // forgetCache data
        $helpers->forgetCache('lined_up');
        $helpers->forgetCache('weigh_receipts');
        $helpers->forgetCache('imported_receipts');

        try {
            $connection = $this->getRabbitMQConnection();
            $channel = $connection->channel();
            $channel->queue_declare('receipts_queue', false, true, false, false);

            $callback = function ($msg) use ($helpers) {
                $data = json_decode($msg->body, true);

                DB::transaction(function () use ($data, $helpers) {
                    foreach ($data as $row) {
                        DB::table('receipts')->updateOrInsert(
                            [
                                'enrolment_no' => $row['enrolment_no'],
                                'vendor_tag' => $row['vendor_tag'],
                                'slaughter_date' => $row['slaughter_date'],
                            ],
                            [
                                'receipt_no' => $row['receipt_no'],
                                'vendor_no' => $row['vendor_no'],
                                'vendor_name' => $row['vendor_name'],
                                'receipt_date' => $row['receipt_date'],
                                'item_code' => $row['item_code'],
                                'description' => $row['description'],
                                'received_qty' => $row['received_qty'],
                                'user_id' => Auth::id(),
                            ]
                        );

                        // Update the imported column to true
                        DB::table('slaughter_data')
                            ->where('receipt_no', $row['receipt_no'])
                            ->update(['imported' => true]);
                    }
                });

                // Acknowledge the message
                $msg->ack();

                Log::info('Receipts processed successfully.');
            };

            $channel->basic_consume('receipts_queue', '', false, false, false, false, $callback);

            while ($channel->is_consuming()) {
                $channel->wait();
            }

            $channel->close();
            $connection->close();
        } catch (\Exception $e) {
            Log::error('Failed to establish RabbitMQ connection: ' . $e->getMessage());
            Toastr::error($e->getMessage(), 'Error Occurred. Wrong Data format!. Records not saved!');
        }
    }

    public function slaughterDataReport(Helpers $helpers)
    {
        $title = "Slaughter Data";

        $slaughter_data = DB::table('slaughter_data')
            ->leftJoin('carcass_types', 'slaughter_data.item_code', '=', 'carcass_types.code')
            ->select('slaughter_data.*', 'carcass_types.description')
            ->orderBy('slaughter_data.created_at', 'DESC')
            ->take(1000)
            ->get();

        return view('slaughter.slaughter_report', compact('title', 'helpers', 'slaughter_data'));
    }

    public function combinedSlaughterReport(Request $request)
    {
        $slaughter_combined = DB::table('slaughter_data')
            ->whereDate('slaughter_data.created_at', Carbon::parse($request->date))
            ->leftJoin('carcass_types', 'slaughter_data.item_code', '=', 'carcass_types.code')
            ->select('slaughter_data.item_code', 'carcass_types.description AS carcass', DB::raw('COUNT(slaughter_data.id) As no_of_carcasses'), DB::raw('SUM(slaughter_data.net_weight) As total_net'))
            ->groupBy('slaughter_data.item_code', 'carcass_types.description')
            ->get();

        $exports = Session::put('session_export_data', $slaughter_combined);

        return Excel::download(new SlaughterCombinedExport, 'SlaughterSummaryReport-' . $request->date . '.xlsx');
    }

    public function exportSlaughterLinesReport(Request $request)
    {
        $from_date = Carbon::parse($request->from_date);
        $to_date = Carbon::parse($request->to_date);

        $slaughter_lines = DB::table('slaughter_data')
            ->leftJoin('carcass_types', 'slaughter_data.item_code', '=', 'carcass_types.code')
            ->leftJoin('users', 'slaughter_data.user_id', '=', 'users.id')
            ->select('slaughter_data.receipt_no', 'slaughter_data.slapmark', 'slaughter_data.item_code', 'carcass_types.description', 'slaughter_data.vendor_no', 'slaughter_data.vendor_name', 'slaughter_data.actual_weight', 'slaughter_data.net_weight', 'slaughter_data.meat_percent', 'slaughter_data.settlement_weight', 'slaughter_data.classification_code', 'slaughter_data.manual_weight', 'users.username', 'slaughter_data.created_at', 'carcass_types.updated_at')
            ->orderBy('slaughter_data.created_at', 'DESC')
            ->whereDate('slaughter_data.created_at', '>=', $from_date)
            ->whereDate('slaughter_data.created_at', '<=', $to_date)
            ->get();

        $exports = Session::put('session_export_data', $slaughter_lines);

        return Excel::download(new SlaughterLinesExport, 'SlaughterLinesReportFor-' . $request->from_date . ' to ' . $request->to_date . '.xlsx');
    }

    public function exportSlaughterForNav(Request $request, Helpers $helpers)
    {
        $title = "Nav import";

        $slaughter_for_Nav = SlaughterData::whereDate('slaughter_data.created_at', Carbon::parse($request->date))
            ->leftJoin('carcass_types', 'slaughter_data.item_code', '=', 'carcass_types.code')
            ->select('slaughter_data.created_at As date', 'slaughter_data.created_at As time', 'slaughter_data.item_code', 'slaughter_data.receipt_no', DB::raw('ROUND(slaughter_data.net_weight, 0) As weight'), 'slaughter_data.meat_percent', 'slaughter_data.classification_code', 'slaughter_data.slapmark')
            ->get();

        foreach ($slaughter_for_Nav as $item) {

            $item['date'] = $helpers->formatTodateOnly($item['date']);
            $item['time'] = $helpers->formatToHoursMinsOnly($item['time']);
        }

        $exports = Session::put('session_export_data', $slaughter_for_Nav);

        return Excel::download(new SlaughterForNavExport, 'SlaughterForNavImport-' . $request->date . '.csv');
    }

    public function scaleSettings(Helpers $helpers, $section = null)
    {
        $title = "scale";

        $scale_settings = DB::table('scale_configs')
            ->where('section', $section)
            ->get();

        return view('slaughter.scale_settings', compact('title', 'scale_settings', 'helpers'));
    }

    public function UpdateScalesettings(Request $request, Helpers $helpers)
    {
        try {
            // forgetCache weigh_configs
            Cache::flush();

            // update
            DB::table('scale_configs')
                ->where('id', $request->item_id)
                ->update([
                    'comport' => $request->edit_comport,
                    'baudrate' => $request->edit_baud,
                    'tareweight' => $request->edit_tareweight,
                    'updated_at' => Carbon::now(),
                ]);


            Toastr::success("record {$request->item_name} updated successfully", 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
    }

    public function changePassword()
    {
        $title = "password";

        return view('slaughter.change_password', compact('title'));
    }

    public function disease(Helpers $helpers)
    {
        $title = "disease";
        
        $diseaseCodes = Cache::remember('diseaseCodes', now()->addMinutes(120), function () {
            return DB::table('disease_list')
                ->get();
        });

        $itemCodes = Cache::remember('item_codes', now()->addMinutes(120), function () {
            return DB::table('carcass_types')
                ->get();
        });

        $diseaseEntries = DB::table('disease_entries as a')
                ->leftJoin('users as b', 'a.user_id', '=', 'b.id')
                ->select('a.*', 'b.username as user_name')
                ->whereDate('a.created_at', '>=', Carbon::yesterday())
                ->orderByDesc('id')
                ->take(1000)
                ->get();

        $receipts = DB::table('receipts')
                ->whereDate('slaughter_date', '>=', today()->subDays(1))
                ->select('vendor_tag', 'receipt_no')
                ->get();


        return view('slaughter.disease', compact('title', 'diseaseCodes', 'itemCodes', 'receipts', 'diseaseEntries','helpers'));
    }

    public function recordDisease(Request $request, Helpers $helpers)
    {
        try {
            $data = [
                'receipt_no' => $request->receipt_no,
                'slapmark' => $request->slapmark,
                'item_code' => $request->item_code,
                'disease_code' => $request->disease_code,
                'user_id' => Auth::id(),
            ];

            DB::table('disease_entries')->insert($data);

            $data['timestamp'] = now()->toDateTimeString();
            $helpers->publishToQueue($data, 'slaughter_disease.wms');

            Toastr::success('record added successfully', 'Success');
            return redirect()
                ->back()
                ->withInput();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
    }

    public function lairageTransfers(Helpers $helpers)
    {
        $title = "lairage transfers";

        $animalTypes = [
            'G0101' => 'Baconer',
            'G0102' => 'Sow',
            'G0104' => 'Suckling',
        ];

        $transfers = DB::table('idt_transfers as transfers')
                ->where('transfers.transfer_from', '1000')
                ->whereDate('transfers.created_at', Carbon::today())
                ->leftJoin('users as users', 'transfers.user_id', '=', 'users.id')
                ->select('transfers.*', 'users.username')
                ->orderByDesc('transfers.id')
                ->take(1000)
                ->get();

        return view('slaughter.lairage_transfers', compact('title', 'animalTypes', 'transfers', 'helpers'));
    }

    public function saveLairageTransfer(Request $request, Helpers $helpers)
    {
        try {
            // try save
            DB::table('idt_transfers')->insert([
                'product_code' => $request->product_code,
                'location_code' => '1010',
                'total_pieces' => $request->total_pieces,
                'total_weight' => '0',
                'batch_no' => '0',
                'with_variance' => '0',
                'transfer_from' => '1000',
                'transfer_type' => 0,
                'user_id' => Auth::id(),
            ]);

            Toastr::success('Transfer to slaughter recorded successfully', 'Success');
            return redirect()
                ->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
    }

    public function updateLairageTransfer(Request $request,Helpers $helpers)
    {
        try {
            // try save
            DB::table('idt_transfers')
                ->where('id', $request->transfer_id)
                ->update([
                    'product_code' => $request->edit_product_code,
                    'updated_at' => Carbon::now(),
                    'edited' => 1,
                    'edited_by' => Auth::id(),
                ]);
            Toastr::success('Updated transfer record successfully', 'Success');
            return redirect()
                ->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
    }

    public function weighOffals(Helpers $helpers)
    {
        $title = "Weigh Offals";
        
        $productCodes = [
            'G6001' => 'Offals(Lungs ,spleens)',
            'G1240' => 'Pork maw',
            'G1241' => 'Small Intestines',
            'G6002' => 'Blood',
            'G1239' => 'Pork Liver',
        ];

        $configs = Cache::remember('offals_weigh_configs', now()->addMinutes(120), function () {
            return DB::table('scale_configs')
                ->where('section', 'offals')
                ->get();
        });

        $offalsData = DB::table('offals')
            ->whereDate('offals.created_at', Carbon::today())
            ->leftJoin('users', 'offals.user_id', '=', 'users.id')
            ->select('offals.*', 'users.username as username')
            ->orderBy('offals.created_at', 'DESC')
            ->get();

        return view('slaughter.weigh_offals', compact('title', 'productCodes', 'offalsData', 'configs', 'helpers'));
    }

    public function saveOffalsWeight(Request $request, Helpers $helpers)
    {
        try {
            $manual_weight = 0;
            if ($request->manual_weight == 'on') {
                $manual_weight = 1;
            }

            Offals::create([
                'product_code'=> $request->product_code,
                'scale_reading'=> $request->reading,
                'net_weight'=> $request->net_weight,
                'is_manual'=> $manual_weight,
                'user_id' => Auth::id(),
            ]);

            Toastr::success('record added successfully', 'Success');
            return redirect()
                ->back()
                ->withInput();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
    }
}
