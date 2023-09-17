<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('filequeue', false, false, false, false);

echo "Сервис запущен. CTRL+C для выключения\n";

$filesave = function ($msg) {
    $filecreate = fopen(($msg->get('delivery_tag') . '-' .  date('Ymdhis') . '.' . $msg->get('type')), 'w');
    fwrite($filecreate, $msg->body);
    echo "Файл сохранен \n";
};

$channel->basic_consume('filequeue', '', false, true, false, false, $filesave);

while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();
?>