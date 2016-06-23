<?php
require_once __DIR__ . '/../vendor/autoload.php';

$predis = new Predis\Client();
$queue = (new \Forseti\SimpleQueue\Connection($predis))->queue('queuename');

$job = new \Forseti\SimpleQueue\Job(['data' => 'value']);
$queue->push($job);