# forseti-simple-queue

Projeto tem como objetivo a utilização de controle de filas de forma simplificada. Esse projeto foi inspirado no Laravel Queue (illuminate/queue).

Utilizando
----------

Client
```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

$predis = new Predis\Client();
$queue = (new \Forseti\SimpleQueue\Connection($predis))->queue('queuename');

$job = new \Forseti\SimpleQueue\Job(['data' => 'value']);
$queue->push($job);
```

Worker
```php
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
```

Instalação com Composer
-----------------------
```bash
composer require forseti/simple-queue
```
