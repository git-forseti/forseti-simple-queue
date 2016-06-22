<?php

namespace Forseti\SimpleQueue;


use Predis\Client;

class Connection
{

    /**
     * @var Client
     */
    private $predis;

    /**
     * Connection constructor.
     * @param Client $predis
     */
    public function __construct(Client $predis)
    {
        $this->predis = $predis;
    }

    /**
     * @param string $queue
     * @return Queue
     */
    public function queue($queue)
    {
        return new Queue($this->predis, $queue);
    }

}