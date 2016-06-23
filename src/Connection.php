<?php

namespace Forseti\SimpleQueue;


use Predis\ClientInterface;

class Connection
{

    /**
     * @var ClientInterface
     */
    private $predis;

    /**
     * Connection constructor.
     * @param ClientInterface $predis
     */
    public function __construct(ClientInterface $predis)
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