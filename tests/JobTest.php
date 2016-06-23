<?php

namespace Forseti\SimpleQueue\Test;

use Forseti\SimpleQueue\Job;

class JobTest extends \PHPUnit_Framework_TestCase
{

    public function testNewJob()
    {
        $data = ['testando' => 'valor'];
        $job = new Job($data);
        $this->assertInstanceOf(Job::class, $job);
        $this->assertEquals($data, $job->getBody());
        $this->assertEquals(1, $job->getAttempts());
        $job->addAttempt();
        $this->assertEquals(2, $job->getAttempts());
        $this->assertNotNull($job->getId());
    }

    public function testLoadJob()
    {
        $job = Job::load('{"id":"1466629737.08841942705867576afe6915c25","attempts":10,"data":{"testando":"valor"}}');
        $this->assertInstanceOf(Job::class, $job);
        $this->assertEquals(['testando' => 'valor'], $job->getBody());
        $this->assertEquals(10, $job->getAttempts());
        $this->assertEquals('1466629737.08841942705867576afe6915c25', $job->getId());
    }

}