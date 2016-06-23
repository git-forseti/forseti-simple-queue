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

    public function testPushStub()
    {
        $job1 = new Job(['name' => 'Novo Job']);
        $job2 = new Job(['name' => 'Novo Job2']);

        $queueStub = $this->getMockBuilder(Queue::class)
            ->disableOriginalConstructor()
            ->getMock();

        $queueStub
            ->expects($this->at(0))
            ->method('push')
            ->with($job1)
            ->willReturn(1);

        $queueStub
            ->expects($this->at(1))
            ->method('push')
            ->with($job2)
            ->willReturn(1);

        $this->assertEquals(1, $queueStub->push($job1));
        $this->assertEquals(1, $queueStub->push($job2));
    }

    public function testPullStub()
    {
        $job1 = new Job(['name' => 'Novo Job']);
        $job2 = new Job(['name' => 'Novo Job2']);

        $queueStub = $this->getMockBuilder(Queue::class)
            ->disableOriginalConstructor()
            ->getMock();

        $queueStub
            ->expects($this->at(0))
            ->method('pull')
            ->willReturn($job1);

        $queueStub
            ->expects($this->at(1))
            ->method('pull')
            ->willReturn($job2);

        $this->assertEquals($job1, $queueStub->pull());
        $this->assertEquals($job2, $queueStub->pull());
    }

    public function testPushOld()
    {
        $redis = $this->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $redis->method('__call')
            ->willReturn(1);

        $queue = (new Connection($redis))->queue('mock');
        $this->assertEquals(1, $queue->push(new Job(['name' => 'Novo Job'])));
    }


    public function testPush()
    {
        $job = new Job(['name' => 'Novo Job']);
        $profile = Factory::getDefault();
        $rpush = $profile->createCommand('rpush', ['mock', $job->payload()]);

        $connection = $this->getMock('Predis\Connection\ConnectionInterface');
        $connection->expects($this->at(0))
            ->method('executeCommand')
            ->with($rpush)
            ->will($this->returnValue(1));

        $client = new Client($connection);
        $queue = (new Connection($client))->queue('mock');
        $this->assertEquals(1, $queue->push($job));
    }
}