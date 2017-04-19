<?php

namespace Forseti\SimpleQueue\Test;

use Forseti\SimpleQueue\Connection;
use Forseti\SimpleQueue\Job;
use Forseti\SimpleQueue\Queue;
use Predis\Client;
use Predis\ClientInterface;
use Predis\Profile\Factory;

class QueueTest extends \PHPUnit_Framework_TestCase
{
    private $options = ['database'=>12];

    public function testPushStub()
    {
        $job1 = new Job(['name' => 'Novo Job']);
        $job2 = new Job(['name' => 'Novo Job2']);

        $predis = new Client($this->options);
        $connection = new Connection($predis);
        $queue = $connection->queue('test');

        $this->assertEquals(1, $queue->push($job1));
        $this->assertEquals(2, $queue->push($job2));

        $predis->flushdb();
    }

    public function testPullStub()
    {
        $job1 = new Job(['name' => 'Novo Job']);
        $job2 = new Job(['name' => 'Novo Job2']);

        $predis = new Client($this->options);
        $connection = new Connection($predis);
        $queue = $connection->queue('test');
        $queue->push($job1);
        $queue->push($job2);

        $this->assertEquals($job1, $queue->pull());
        $this->assertEquals($job2, $queue->pull());
        $predis->flushdb();
    }

    public function testPushOld()
    {
        $predis = new Client($this->options);
        $connection = new Connection($predis);
        $queue = $connection->queue('test');

        $this->assertEquals(1, $queue->push(new Job(['name' => 'Novo Job'])));
        $predis->flushdb();
    }


    public function testPush()
    {
        $job = new Job(['name' => 'Novo Job']);
        $predis = new Client($this->options);
        $connection = new Connection($predis);
        $queue = $connection->queue('test');

        $this->assertEquals(1, $queue->push($job));
        $this->assertEquals($job, $queue->pull());

        $job2 = new Job(['name'=>'Novo Job 2']);
        $this->assertEquals(1, $queue->push($job2));
        $this->assertEquals($job2, $queue->pull());

        $predis->flushdb();
    }
}