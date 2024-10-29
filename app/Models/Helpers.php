<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Helpers
{
    public function authenticatedUserId()
    {
        return Session::get('session_userId');
    }

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
            'user_id' => $this->authenticatedUserId(),
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
    public function publishToQueue($data, $queue_name)
    {
        $channel = $this->getRabbitMQChannel();

        // Declare the exchange if it does not exist
        $channel->exchange_declare('fcl.exchange.direct', 'direct', false, true, false);

        $msg = new AMQPMessage(json_encode($data), [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ]);

        $channel->basic_publish($msg, 'fcl.exchange.direct', $queue_name);
    }

    private $rabbitMQConnection = null;
    private $rabbitMQChannel = null;

    private function getRabbitMQConnection()
    {
        if ($this->rabbitMQConnection === null) {
            try {
                $this->rabbitMQConnection = new AMQPStreamConnection(
                    config('app.rabbitmq_host'), // RabbitMQ host
                    config('app.rabbitmq_port'), // RabbitMQ port (default for AMQP is 5672)
                    config('app.rabbitmq_user'), // RabbitMQ user
                    config('app.rabbitmq_password') // RabbitMQ password
                );
                Log::info('RabbitMQ connection established successfully.');
            } catch (\Exception $e) {
                Log::error('Failed to establish RabbitMQ connection: ' . $e->getMessage());
                throw $e;
            }
        }
        return $this->rabbitMQConnection;
    }

    private function getRabbitMQChannel()
    {
        if ($this->rabbitMQChannel === null) {
            $connection = $this->getRabbitMQConnection();
            $this->rabbitMQChannel = $connection->channel();
        }
        return $this->rabbitMQChannel;
    }

    public function __destruct()
    {
        if ($this->rabbitMQChannel !== null) {
            $this->rabbitMQChannel->close();
        }
        if ($this->rabbitMQConnection !== null) {
            $this->rabbitMQConnection->close();
        }
    }

    public function consumeFromQueue()
    {
        $channel = $this->getRabbitMQChannel();

        // Declare the queues if they do not exist
        $queues = [
            'slaughter_receipts.wms',
            // 'another_queue_name',
            // 'yet_another_queue_name'
        ];

        foreach ($queues as $queue) {
            $channel->queue_declare($queue, false, true, false, false);
        }

        // Define callback functions for each queue
        $callbacks = [
            'slaughter_receipts.wms' => function ($msg) {
                $data = json_decode($msg->body, true);

                try {
                    $this->insertReceiptData($data);
                    // Log the received message
                    Log::info('Slaughter receipts Received: ' . json_encode($data));
                    // Acknowledge the message
                    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                } catch (\Exception $e) {
                    // Log the error
                    Log::error('Failed to insert receipt data: ' . $e->getMessage());
                }
            },
            'another_queue_name' => function ($msg) {
                $data = json_decode($msg->body, true);
                // Process the message here
                Log::info('Another queue message Received: ' . json_encode($data));
                // Acknowledge the message
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            },
            'yet_another_queue_name' => function ($msg) {
                $data = json_decode($msg->body, true);
                // Process the message here
                Log::info('Yet another queue message Received: ' . json_encode($data));
                // Acknowledge the message
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            }
        ];

        // Start consuming messages from each queue
        foreach ($queues as $queue) {
            $channel->basic_consume($queue, '', false, false, false, false, $callbacks[$queue]);
        }

        while (true) {
            try {
                while (count($channel->callbacks)) {
                    $channel->wait(null, false, 5); // Wait for a message with a timeout of 5 seconds
                }
            } catch (\PhpAmqpLib\Exception\AMQPTimeoutException $e) {
                // Handle the timeout exception if needed
                Log::info('No messages in the queue. Waiting for new messages...');
                // Do not break the loop; continue waiting for new messages
            } catch (\PhpAmqpLib\Exception\AMQPChannelClosedException $e) {
                // Handle the channel closed exception
                Log::error('Channel connection is closed: ' . $e->getMessage());
                // Optionally, you can try to reconnect here
                break; // Exit the inner loop if the channel is closed
            }

            // Sleep for a short period before restarting the loop
            sleep(1);
        }

        // Close the channel and connection after consuming messages
        $channel->close();
        $this->rabbitMQConnection->close();
    }

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
            DB::table('receipts')->insert([
                'enrolment_no' => $data['ReceiptNo'],
                'vendor_tag' => $data['Slapmark'],
                'receipt_no' => $data['ReceiptNo'],
                'vendor_no' => $data['FarmerNo'],
                'vendor_name' => $data['FarmerName'],
                'receipt_date' => Carbon::parse($data['ReceiptDate'])->format('d/m/y'), // e.g., 29/10/24
                'item_code' => $data['Item'],
                'description' => $data['ItemDescription'],
                'received_qty' => $data['ReceivedQty'],
                'user_id' => 1,
                'slaughter_date' => Carbon::now()->format('Y-m-d 00:00:00.000'), // e.g., 2024-10-29 00:00:00.000
            ]);
            Log::success('Receipt data inserted successfully.', 'Success');
        } catch (\Exception $e) {
            Log::error($e->getMessage(), 'Error!');
            return back();
        }

    }
}
