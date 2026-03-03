<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class QueueManager
{

    //Constructor for QueueManager class
    public function __construct()
    {
    }

    public function queue_data($data)
    {
        // require_once __DIR__ . '/rabbitmqphp/autoload.php';

        // for live use username: admin, password: UgB9HYFXP5EhcX8f
        // url: http://104.248.238.57:15672/
        $connection = new AMQPStreamConnection(
            'localhost',
            5672,
            'guest',
            'guest'
        );
        $channel = $connection->channel();

        $channel->queue_declare('live_zizi_task_queue', false, true, false, false);

        if (empty($data)) {
            $data = "empty";
        }

        $data = json_encode($data);
        error_log('------QUEUE DATE-------');
        error_log($data);
        $msg = new AMQPMessage($data,
            array('delivery_mode' => 2) # make message persistent
        );

        $channel->basic_publish($msg, '', 'live_zizi_task_queue');
        $channel->close();
        $connection->close();

    }

}
