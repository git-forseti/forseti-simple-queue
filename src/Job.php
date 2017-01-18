<?php

namespace Forseti\SimpleQueue;

class Job
{

    /**
     * @var string
     */
    private $id;

    /**
     * @var mixed
     */
    private $body;

    /**
     * @var int
     */
    private $attempts;

    /**
     * Job constructor.
     * @param mixed $body
     */
    public function __construct($body)
    {
        $this->body = $body;
        $this->id = microtime(true) . uniqid(rand());
        $this->setAttempts(1);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getAttempts()
    {
        return $this->attempts;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Soma tentativa
     * @return $this
     */
    public function addAttempt()
    {
        $this->attempts += 1;
        return $this;
    }

    /**
     * @return string
     */
    public function payload()
    {
        return json_encode([
            'id' => $this->id,
            'attempts' => $this->attempts,
            'data' => $this->body,
        ]);
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param int $attempts
     * @return $this
     */
    private function setAttempts($attempts)
    {
        $this->attempts = intval($attempts);
        return $this;
    }

    /**
     * @param string $payload
     * @return Job
     * @throws JobNotValidException
     */
    public static function load($payload)
    {
        $jobRaw = @json_decode($payload, true);
        if ($jobRaw === null) {
            throw new JobNotValidException();
        }
        
        $job = new self($jobRaw['data']);
        $job->setId($jobRaw['id'])
            ->setAttempts($jobRaw['attempts']);

        return $job;
    }
}