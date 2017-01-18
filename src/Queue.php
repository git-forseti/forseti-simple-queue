<?php

namespace Forseti\SimpleQueue;

use Predis\ClientInterface;

class Queue
{

    /**
     * @var int Tempo de expiração de um job reservado
     */
    private $expire = 3600;

    /**
     * @var int Máximo de tentativas para falhar o job
     */
    private $maxAttempt = 3;

    /**
     * @var ClientInterface
     */
    private $predis;

    /**
     * @var string
     */
    private $queue;

    /**
     * @var string
     */
    private $queueUK;

    /**
     * Queue constructor.
     * @param ClientInterface $predis
     * @param string $queue
     */
    public function __construct(ClientInterface $predis, $queue)
    {
        $this->predis = $predis;
        $this->queue = $queue;
        $this->queueUK = $this->queue . ':uk';
    }

    /**
     * @param Job $job
     * @return int
     */
    public function push(Job $job)
    {
        if (!$this->predis->hexists($this->queueUK, $job->getId())) {
            $this->predis->hset($this->queueUK, $job->getId(), 1);
            return $this->predis->rpush($this->queue, $job->payload());
        }

        return 0;
    }

    /**
     * @param int $timeout
     * @return Job
     * @throws JobNotValidException
     */
    public function pull($timeout = 10)
    {
        $queue = $this->queue;
        $reserved = $queue.':reserved';
        $this->migrateExpiredJobs($reserved, $queue);
        
        $data = $this->predis->blpop($queue, $timeout);

        if ($data === null) {
            return $this->pull($timeout);
        }

        $payload = $data[1];
        $this->predis->zadd($reserved, time() + $this->expire, $payload);

        return Job::load($payload);
    }

    /**
     * Envia um comando de job processado
     * @param Job $job
     * @return bool
     */
    public function processed(Job $job)
    {
        $this->predis->hdel($this->queueUK, [$job->getId()]);
        return $this->predis->zrem($this->queue.':reserved', $job->payload()) ? true : false;
    }

    /**
     * @param $from
     * @param $to
     */
    private function migrateExpiredJobs($from, $to)
    {
        $options = ['cas' => true, 'watch' => $from, 'retry' => 10];
        $this->predis->transaction($options, function ($transaction) use ($from, $to) {
            $time = time();
            $jobs = $this->getExpiredJobs($transaction, $from, $time);

            if (count($jobs) > 0) {
                $this->removeExpiredJobs($transaction, $from, $time);
                $this->pushExpiredJobsOntoNewQueue($transaction, $to, $jobs);
            }
        });
    }

    /**
     * @param $transaction
     * @param $from
     * @param $time
     * @return mixed
     */
    private function getExpiredJobs($transaction, $from, $time)
    {
        return $transaction->zrangebyscore($from, '-inf', $time);
    }

    /**
     * @param $transaction
     * @param $from
     * @param $time
     */
    private function removeExpiredJobs($transaction, $from, $time)
    {
        $transaction->multi();
        $transaction->zremrangebyscore($from, '-inf', $time);
    }

    /**
     * @param $transaction
     * @param $to
     * @param $jobs
     */
    private function pushExpiredJobsOntoNewQueue($transaction, $to, $jobs)
    {
        foreach ($jobs as $jobRaw) {
            $job = Job::load($jobRaw);
            $job->addAttempt();

            $queue = $to;
            if ($job->getAttempts() > $this->maxAttempt) {
                $queue = $to . ':failed';
                $this->predis->hdel($this->queueUK, [$job->getId()]);
            }

            $transaction->rpush($queue, $job->payload());
        }
    }

    /**
     * @param int $expire
     * @return $this
     */
    public function setExpire($expire)
    {
        $this->expire = $expire > 0 ? $expire : 0;
        return $this;
    }


    /**
     * @param int $maxAttemp
     * @return $this
     */
    public function setMaxAttempt($maxAttemp)
    {
        $this->maxAttempt = intval($maxAttemp);
        return $this;
    }
}