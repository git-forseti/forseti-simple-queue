<?php

namespace Forseti\SimpleQueue\Test;

use Forseti\SimpleQueue\Connection;
use Forseti\SimpleQueue\Queue;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{

    public function testIsQueue()
    {
        $predis = $this->getMock('Predis\Client');
        $connection = new Connection($predis);
        $queue = $connection->queue('teste');

        $this->assertInstanceOf(Queue::class, $queue, 'Instância não é válida');
    }
}