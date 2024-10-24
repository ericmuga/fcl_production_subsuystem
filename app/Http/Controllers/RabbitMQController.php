<?php

namespace App\Http\Controllers;

use App\Services\RabbitMQService;

class RabbitMQController extends Controller
{
    protected $rabbitMQService;

    public function __construct(RabbitMQService $rabbitMQService)
    {
        $this->rabbitMQService = $rabbitMQService;
    }

    public function publishMessage()
    {
        $this->rabbitMQService->publish('queue_name', 'Test message');
    }

    public function consumeMessages()
    {
        $this->rabbitMQService->consume('queue_name', function ($msg) {
            echo 'Received message: ' . $msg->body;
        });
    }
}
