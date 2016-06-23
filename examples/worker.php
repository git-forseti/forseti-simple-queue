<?php
require_once __DIR__ . '/../vendor/autoload.php';

$predis = new Predis\Client();
$connection = new \Forseti\SimpleQueue\Connection($predis);
$queue = $connection->queue('queuename');

while($job = $queue->pull()) {
    print_r($job);
    sleep(2);

    //remove da fila
    $queue->processed($job);
}