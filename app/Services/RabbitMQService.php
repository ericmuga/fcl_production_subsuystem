<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use App\Models\MessagePushStatus;
use Illuminate\Support\Facades\Log;

class RabbitMQService
{
    protected $connection;
    protected $channel;

    // public function __construct()
    // {
    //     // Attempt connection
    //     $this->connect();
    // }

    // private function connect()
    // {
    //     // Connect to RabbitMQ
    //     try {
    //         $host = config('app.rabbitmq_host');
    //         $port = config('app.rabbitmq_port');
    //         $user = config('app.rabbitmq_user');
    //         $password = config('app.rabbitmq_password');

    //         $this->connection = new AMQPStreamConnection($host, $port, $user, $password);
    //         $this->channel = $this->connection->channel();
    //     } catch (\Exception $e) {
    //         Log::error('RabbitMQ Connection Error: ' . $e->getMessage());
    //         $this->connection = null;
    //     }
    // }

    // public function publish($queue, $message)
    // {
    //     // Publish a message to a queue
    //     try {
    //         if (!$this->connection) {
    //             $this->storePendingMessage($queue, $message);
    //             return;
    //         }

    //         // Declare the queue
    //         $this->channel->queue_declare($queue, false, true, false, false);
    //         $msg = new AMQPMessage($message);
    //         $this->channel->basic_publish($msg, '', $queue);

    //         // Save successful message status
    //         $this->storeMessageStatus($queue, $message, 'sent');
    //     } catch (\Exception $e) {
    //         Log::error('RabbitMQ Publish Error: ' . $e->getMessage());
    //         $this->storePendingMessage($queue, $message);
    //     }
    // }

    private function storePendingMessage($queue, $message)
    {
        // Save the message as pending in the database
        MessagePushStatus::create([
            'queue' => $queue,
            'message' => $message,
            'status' => 'pending',
        ]);
    }

    private function storeMessageStatus($queue, $message, $status)
    {
        // Update the message status in the database after publishing
        MessagePushStatus::create([
            'queue' => $queue,
            'message' => $message,
            'status' => $status,
        ]);
    }

    public function retryPendingMessages()
    {
        // Retry sending pending messages
        if (!$this->connection) {
            $this->connect();
        }

        if ($this->connection) {
            $pendingMessages = MessagePushStatus::where('status', 'pending')->get();

            foreach ($pendingMessages as $pending) {
                try {
                    $this->publish($pending->queue, $pending->message);

                    // If successful, update status to 'sent'
                    $pending->update(['status' => 'sent']);
                } catch (\Exception $e) {
                    Log::error('RabbitMQ Retry Error: ' . $e->getMessage());
                }
            }
        }
    }

    public function close()
    {
        if ($this->channel) {
            $this->channel->close();
        }
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
