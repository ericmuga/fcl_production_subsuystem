<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

if (!defined('PhpAmqpLib\Wire\IO\SOCKET_EINTR')) {
    define('PhpAmqpLib\Wire\IO\SOCKET_EINTR', 4); // Common value for EINTR on Unix-like systems
}

if (!defined('PhpAmqpLib\Wire\IO\SOCKET_EWOULDBLOCK')) {
    define('PhpAmqpLib\Wire\IO\SOCKET_EWOULDBLOCK', 11); // Common value for EWOULDBLOCK
}

if (!defined('PhpAmqpLib\Wire\IO\SOCKET_EAGAIN')) {
    define('PhpAmqpLib\Wire\IO\SOCKET_EAGAIN', 11); // Common value for EAGAIN
}

class Helpers
{
    public function validateUserPassword()
    {
        if (Hash::check("param1", "param2")) {
            //add logic here
        }

        //    param1 - user password that has been entered on the form
        //    param2 - old password hash stored in database

    }

    public function randomBootstrap()
    {
        $classesArray = [
            'bg-danger',
            'bg-dark',
            'bg-info',
            'bg-primary',
            'bg-secondary',
            'bg-success',
            'bg-warning'
        ];

        // Get two random items from the input array.
        $k = array_rand($classesArray);
        return $classesArray[$k];
    }

    public function dateToHumanFormat($date)
    {
        return date("F jS, Y", strtotime($date));
    }

    public function amPmDate($date)
    {
        return date('d-m-Y g:i A', strtotime($date));
    }

    public function getProductName($code)
    {
        return Product::where('code', $code)->value('description');
    }

    public function getButcheryDate()
    {
        $proposed_butchery_date = Carbon::yesterday();
        if (SlaughterData::whereDate('created_at', '=', Carbon::yesterday())->exists()) {
            // yesterday is valid
            return $proposed_butchery_date = Carbon::yesterday();
        } elseif (SlaughterData::whereDate('created_at', '=', Carbon::yesterday()->subDays(1))->exists()) {
            # yesterday minus 1 day is valid
            return $proposed_butchery_date = Carbon::yesterday()->subDays(1);
        } elseif (SlaughterData::whereDate('created_at', '=', Carbon::yesterday()->subDays(2))->exists()) {
            # yesterday minus 2 day is valid
            return $proposed_butchery_date = Carbon::yesterday()->subDays(2);
        } elseif (SlaughterData::whereDate('created_at', '=', Carbon::yesterday()->subDays(3))->exists()) {
            # yesterday minus 3 day is valid
            return $proposed_butchery_date = Carbon::yesterday()->subDays(3);
        }
        return $proposed_butchery_date;
    }

    public function getInputData()
    {
        $input_count = DB::table('beheading_data')
            ->whereDate('created_at', Carbon::today())
            ->sum('no_of_carcass');
        return $input_count;
    }

    public function getOutputData()
    {
        $output_legs = DB::table('butchery_data')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', 'G1100')
            ->sum('no_of_items');

        $output_middles = DB::table('butchery_data')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', 'G1101')
            ->sum('no_of_items');

        $output_shoulders = DB::table('butchery_data')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', 'G1102')
            ->sum('no_of_items');

        $output_count = ['output_legs' => $output_legs, 'output_middles' => $output_middles, 'output_shoulders' => $output_shoulders];
        return $output_count;
    }

    public function getProductCode($product_name)
    {
        $product_code = Product::where('description', $product_name)
            // ->orWhere('description', 'like', '%' . $product_name . '%')
            ->value('code');

        return $product_code;
    }

    public function getReadScaleApiServiceUrl()
    {
        $ip = \Request::getClientIp(true);

        if ($ip == '::1') {
            $ip = '127.0.0.1';
        }
        return $url = 'http://' . $ip . ':3000/api/get-scale-reading';
    }

    public function getComportListServiceUrl()
    {
        $ip = \Request::getClientIp(true);

        if ($ip == '::1') {
            $ip = '127.0.0.1';
        }
        return $url = 'http://' . $ip . ':3000/api/get-comport-list';
    }

    public function get_scale_read($comport)
    {
        $curl = curl_init();
        $url = $this->getReadScaleApiServiceUrl();
        $full_url = $url . '/' . $comport;

        curl_setopt_array($curl, array(
            CURLOPT_URL => $full_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_TIMEOUT => 5,   // time is in seconds 
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                // Set Here Your Requesred Headers
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function get_comport_list()
    {
        $curl = curl_init();

        $url = $this->getComportListServiceUrl();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_TIMEOUT => 5,   // time is in seconds 
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                // Set Here Your Requesred Headers
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function getProductProcesses($product_id)
    {
        $processes =  DB::table("product_processes")
            ->leftJoin('processes', 'product_processes.process_code', '=', 'processes.process_code')
            ->where('product_id', $product_id)
            ->pluck('processes.process');

        return $processes;
    }

    public function validateLogin($post_data)
    {
        $url = config('app.login_api_url');
        $result = $this->send_curl($url, $post_data);
        return $result;
    }

    public function send_curl($url, $post_data)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_TIMEOUT => 5,   // time is in seconds 
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => array(
                // Set Here Your Requesred Headers
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function addUser($username)
    {
        try {
            // try save
            DB::table('users')->insert([
                'username' => $username,
                'email' => strtolower($username) . "@farmerschoice.co.ke",
                'role' => 'user',
            ]);
            return 1;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function formatTodateOnly($db_date)
    {

        return date('d-m-Y', strtotime($db_date));
    }

    public function formatToHoursMinsOnly($db_date)
    {

        return date('H:i', strtotime($db_date));
    }

    public function forgetCache($key)
    {
        Cache::forget($key);
    }

    public function getSowItemCodeConversion($request_code)
    {
        $item_code = '';
        if ($request_code == 'G1100') {
            //leg
            $item_code = 'G1108';
        } elseif ($request_code == 'G1101') {
            // shoulder
            $item_code = 'G1109';
        } else {
            // middle
            $item_code = 'G1110';
        }

        return $item_code;
    }

    public function numberOfSalesCarcassesCalculation($no_of_pieces)
    {
        $number_of_carcasses = '1';
        if ($no_of_pieces > 1) {
            $number_of_carcasses = ceil($no_of_pieces / 2);
        }
        return $number_of_carcasses;
    }

    public function insertChangeDataLogs($table_name, $item_id, $entry_type)
    {
        DB::table('change_logs')->insert([
            'table_name' => $table_name,
            'item_id' => $item_id,
            'entry_type' => $entry_type,
            'user_id' => Auth::id(),
        ]);
    }

    public function optimizeCache()
    {
        $clear = Artisan::call('cache:clear');
        $optimize = Artisan::call('optimize');
        $view = Artisan::call('view:cache');
    }

    public function generateIdtBatch($production_date)
    {
        $alphas = 'abcdefghjklmnpqrstuvwxyz';
        $batch_month = (int)date('m') - 1;

        return date($production_date) . strtoupper(mb_substr($alphas, $batch_month, 1));
    }

    public function getLocationCode($export_status, $location_code)
    {
        $location = $location_code;

        if ($export_status == 1) {
            $location = 3600;
        } elseif ($export_status == '3035') {
            $location = 3035;
        }
        return $location;
    }

    public function insertItemLocations()
    {
        $item_codes = DB::table('items')
            ->select('code')
            ->get();

        $location_codes = DB::table('items')
            ->select('code')
            ->distinct()
            ->get()->toArray();

        foreach ($item_codes as $code) {
            # code...
            if (!in_array($code, $location_codes)) {
                dd($code);
            }
        }
        return 1;
    }

    public function CustomErrorlogger($e, $function_name)
    {
        Log::error('An exception occurred in ' .$function_name, ['exception' => $e]);
    }

    //Rabbit MQ
    // public function publishToQueue($data, $queue_name)
    // {
    //     // Add the company name flag to the data
    //     $data['company_name'] = 'FCL';

    //     $channel = $this->getRabbitMQChannel();

    //     try {
    //         $channel->exchange_declare('fcl.exchange.direct', 'direct', false, true, false);

    //         $msg = new AMQPMessage(json_encode($data), [
    //             'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
    //         ]);

    //         $channel->basic_publish($msg, 'fcl.exchange.direct', $queue_name);
    //         Log::info("Message published to queue: {$queue_name}");
    //     } catch (\Exception $e) {
    //         Log::error("Failed to publish message to queue {$queue_name}: {$e->getMessage()}");
    //         $this->CustomErrorlogger($e, 'publishToQueue');
    //     }
    // }

    // private $rabbitMQConnection = null;
    // private $rabbitMQChannel = null;

    // private function getRabbitMQConnection()
    // {
    //     if ($this->rabbitMQConnection === null) {
    //         try {
    //             $this->rabbitMQConnection = new AMQPStreamConnection(
    //                 config('app.rabbitmq_host'), // RabbitMQ host
    //                 config('app.rabbitmq_port'), // RabbitMQ port (default for AMQP is 5672)
    //                 config('app.rabbitmq_user'), // RabbitMQ user
    //                 config('app.rabbitmq_password') // RabbitMQ password
    //             );
    //             Log::info('RabbitMQ connection established successfully.');
    //         } catch (\Exception $e) {
    //             Log::error('Failed to establish RabbitMQ connection: ' . $e->getMessage());
    //             throw $e;
    //         }
    //     }
    //     return $this->rabbitMQConnection;
    // }

    // private function getRabbitMQChannel()
    // {
    //     if ($this->rabbitMQChannel === null) {
    //         $connection = $this->getRabbitMQConnection();
    //         $this->rabbitMQChannel = $connection->channel();
    //     }
    //     return $this->rabbitMQChannel;
    // }

    // public function declareQueue($queue_name)
    // {
    //     $channel = $this->getRabbitMQChannel();

    //     try {
    //         $channel->queue_declare(
    //             $queue_name,
    //             false,
    //             true,
    //             false,
    //             false,
    //             false,
    //             new \PhpAmqpLib\Wire\AMQPTable([
    //                 'x-dead-letter-exchange' => 'fcl.exchange.dlx',
    //                 'x-dead-letter-routing-key' => $queue_name,
    //             ])
    //         );
    //         Log::info("Queue declared: {$queue_name}");
    //     } catch (\Exception $e) {
    //         Log::error("Failed to declare queue {$queue_name}: {$e->getMessage()}");
    //         $this->CustomErrorlogger($e, 'declareQueue');
    //     }
    // }

    // public function __destruct()
    // {
    //     try {
    //         if ($this->rabbitMQChannel) {
    //             $this->rabbitMQChannel->close();
    //         }
    //         if ($this->rabbitMQConnection) {
    //             $this->rabbitMQConnection->close();
    //         }
    //         Log::info("RabbitMQ connection and channel closed.");
    //     } catch (\Exception $e) {
    //         Log::error("Error closing RabbitMQ resources: {$e->getMessage()}");
    //     }
    // }

    // public function consumeFromQueue()
    // {
    //     $channel = $this->getRabbitMQChannel();

    //     // List of queues to declare and consume from
    //     $queues = [
    //         // 'slaughter_receipts.wms',
    //         'master_data_items.wms',
    //         'master_data_locations.wms',
    //         'master_data_products.wms',
    //         'master_data_family.wms',
    //         'master_data_disease_list.wms',
    //         'master_data_assets.wms',
    //         'master_data_recipe.wms',
    //         'intercompany_transfers.wms',
    //         // 'yet_another_queue_name'
    //     ];

    //     // Declare each queue
    //     foreach ($queues as $queue) {
    //         $this->declareQueue($queue);
    //     }

    //     // Define callback functions for each queue with queue-specific handling logic
    //     $callbacks = [
    //         'slaughter_receipts.wms' => function ($msg) {
    //             $this->processQueueMessage($msg, 'Slaughter Receipts', function ($data) {
    //                 return $this->insertReceiptData($data);
    //             });
    //         },
    //         'master_data_items.wms' => function ($msg) {
    //             $this->processQueueMessage($msg, 'Master Data Items', function ($data) {
    //                 foreach ($data['items'] as $item) {
    //                     DB::table('items')->updateOrInsert(
    //                         ['code' => $item['code']],
    //                         [
    //                             'barcode' => $item['barcode'],
    //                             'description' => $item['description'],
    //                             'unit_of_measure' => $item['base-unit-of-measure'],
    //                             'blocked' => $item['blocked'],
    //                         ]
    //                     );
    //                 }
    //             });
    //         },
    //         'master_data_locations.wms' => function ($msg) {
    //             $this->processQueueMessage($msg, 'Master Data Locations', function ($data) {
    //                 foreach ($data['locations'] as $location) {
    //                     DB::table('stock_locations')->updateOrInsert(
    //                         ['location_code' => $location['code']],
    //                         ['description' => $location['name']]
    //                     );
    //                 }
    //             });
    //         },
    //         'master_data_products.wms' => function ($msg) {
    //             $this->processQueueMessage($msg, 'Master Data Products', function ($data) {
    //                 foreach ($data['products'] as $product) {
    //                     DB::table('products')->updateOrInsert(
    //                         ['code' => $product['code']],
    //                         [
    //                             'description' => $product['description'],
    //                             'unit_of_measure' => $product['unit_of_measure'],
    //                             'product_type' => $product['product_type'],
    //                             'process_type' => $product['process_type']
    //                         ]
    //                     );
    //                 }
    //             });
    //         },
    //         'master_data_family.wms' => function ($msg) {
    //             $this->processQueueMessage($msg, 'Master Data Families', function ($data) {
    //                 foreach ($data['families'] as $family) {
    //                     DB::table('family')->updateOrInsert(
    //                         ['family_no' => $family['family_no']],
    //                         [
    //                             'item_no' => $family['item_no'],
    //                             'family_description' => $family['family_description'],
    //                             'item_type' => $family['item_type'],
    //                             'process_code' => $family['process_code']
    //                         ]
    //                     );
    //                 }
    //             });
    //         },
    //         'master_data_disease_list.wms' => function ($msg) {
    //             $this->processQueueMessage($msg, 'Master Data Diseases', function ($data) {
    //                 foreach ($data['diseases'] as $disease) {
    //                     DB::table('disease_list')->updateOrInsert(
    //                         ['disease_code' => $disease['disease_code']],
    //                         ['description' => $disease['description']]
    //                     );
    //                 }
    //             });
    //         },
    //         'master_data_assets.wms' => function ($msg) {
    //             $this->processQueueMessage($msg, 'Master Data Assets', function ($data) {
    //                 foreach ($data['assets'] as $asset) {
    //                     DB::table('assets')->updateOrInsert(
    //                         ['fa_code' => $asset['Fa_Code']],
    //                         [
    //                             'no' => $asset['No_'],
    //                             'description' => $asset['Description'],
    //                             'chassis' => $asset['Chassis'],
    //                             'engine_no' => $asset['Engine_No'],
    //                             'fa_class_code' => $asset['FA_Class_Code'],
    //                             'make_brand' => $asset['Make_Brand'],
    //                             'comments' => $asset['Comments'],
    //                             'responsible_employee' => $asset['Responsible_employee'],
    //                             'location_code' => $asset['Location_code'],
    //                             'location_name' => $asset['LocationName']
    //                         ]
    //                     );
    //                 }
    //             });
    //         },
    //         'master_data_recipe.wms' => function ($msg) {
    //             $this->processQueueMessage($msg, 'Master Data Recipe', function ($data) {
    //                 foreach ($data['headers'] as $header) {
    //                     DB::table('template_header')->updateOrInsert(
    //                         ['template_no' => $header['template_no']],
    //                         [
    //                             'template_name' => $header['template_name'],
    //                             'blocked' => $header['blocked'],
    //                             'user_id' => null
    //                         ]
    //                     );
    //                 }
    //                 foreach ($data['lines'] as $line) {
    //                     DB::table('template_lines')->updateOrInsert(
    //                         [
    //                             'template_no' => $line['template_no'],
    //                             'item_code' => $line['item_code']
    //                         ],
    //                         [
    //                             'description' => $line['description'],
    //                             'percentage' => $line['percentage'],
    //                             'units_per_100' => $line['units_per_100'],
    //                             'type' => $line['type'],
    //                             'main_product' => $line['main_product'],
    //                             'shortcode' => $line['shortcode'],
    //                             'unit_measure' => $line['unit_measure'],
    //                             'location' => $line['location']
    //                         ]
    //                     );
    //                 }
    //             });
    //         },
    //         'intercompany_transfers.wms' => function ($msg) {
    //             $this->processQueueMessage($msg, 'Intercompany Transfers', function ($data) {
    //                 DB::table('idt_transfers')->insert([
    //                     'product_code' => substr($data['item_code'], 1),
    //                     'location_code' => '1570',
    //                     'total_pieces' => isset($data['no_of_pieces']) ? $data['no_of_pieces'] : 0,
    //                     'total_weight' => $data['net_weight'],
    //                     'batch_no' => isset($data['batch_no']) ? $data['batch_no'] : 0,
    //                     'with_variance' => 0,
    //                     'transfer_type' => 1,
    //                     'user_id' => 1,
    //                     'transfer_from' => $data['from_location_code'],
    //                     'manual_weight' => $data['manual_weight'],
    //                 ]);
    //             });
    //         },
    //     ];

    //     // Start consuming messages from each queue
    //     foreach ($queues as $queue) {
    //         $channel->basic_consume($queue, '', false, false, false, false, $callbacks[$queue]);
    //     }

    //     while (true) {
    //         try {
    //             while (count($channel->callbacks)) {
    //                 $channel->wait(null, false, 5); // Timeout of 5 seconds
    //             }
    //         } catch (\Exception $e) {
    //             Log::error('Error in message consumption: ' . $e->getMessage());
    //         }
    //     }
    // }

    /**
     * Processes a queue message with the given processing logic.
     */
    // private function processQueueMessage($msg, $context, $processData)
    // {
    //     $data = json_decode($msg->body, true);
    //     Log::info("{$context} received for processing: " . json_encode($data));

    //     try {
    //         $processData($data);
    //         Log::info("{$context} data processed successfully.");
    //         $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    //         Log::info("Message acknowledged.");
    //     } catch (\Exception $e) {
    //         Log::error("Failed to process {$context} data: " . $e->getMessage());
    //         $msg->delivery_info['channel']->basic_nack($msg->delivery_info['delivery_tag']);
    //     }
    // }

    public function getProcessName($process_code)
    {
        $process_codes_list = Cache::rememberForever('process_codes_list', function () {
            return DB::table('processes')->select('process_code', 'process')->get();
        });

        $process_codes_map = $process_codes_list->pluck('process', 'process_code');

        return $process_codes_map[$process_code] ?? 'Unknown';
    }

    public function insertReceiptData($data)
    {
        // forgetCache data
        $this->forgetCache('lined_up');
        $this->forgetCache('weigh_receipts');
        $this->forgetCache('imported_receipts');

        try {
            // insert data
            foreach ($data['receiptLines'] as $line) {
                DB::table('receipts')->updateOrInsert(
                    [
                        'enrolment_no' => $line['ReceiptNo'],
                        'vendor_tag' => $line['Slapmark']
                    ],
                    [
                        'receipt_no' => $line['ReceiptNo'],
                        'vendor_no' => $line['FarmerNo'],
                        'vendor_name' => $line['FarmerName'],
                        'receipt_date' => Carbon::parse($line['ReceiptDate'])->format('d/m/y'), // e.g., 29/10/24
                        'item_code' => $line['Item'],
                        'description' => $line['ItemDescription'],
                        'received_qty' => $line['ReceivedQty'],
                        'user_id' => 1,
                        'slaughter_date' => Carbon::now()->format('Y-m-d 00:00:00.000'), // e.g., 2024-10-29 00:00:00.000
                    ]
                );
            }
            Log::info('Receipt data inserted successfully.');
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to insert receipt data: ' . $e->getMessage());
            return false;
        }

    }
}
