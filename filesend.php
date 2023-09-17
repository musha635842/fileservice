<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();
$channel->confirm_select();

$channel->set_ack_handler(
    function (AMQPMessage $message){
        echo "Файл получен\n";
    }
);

$channel->queue_declare('filequeue', false, false, false, false);

$opener = fopen(($argv[1]), "r");
$filename_processor = explode('.', $argv[1]);
$wrapper = new AMQPMessage(fread($opener, filesize($argv[1])));
$wrapper->set('type', end($filename_processor));
$channel->basic_publish($wrapper, '', 'filequeue');
echo "Файл отправлен\n";

while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();
?>