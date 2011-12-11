<?php

namespace NodeDBDriver;

class Binding
{
    protected $server;
    protected $client;

    /**
     * Instantiates a new binding.
     *
     * @api
     * @param NodeDBDriver\Curl $client
     * @param String $host
     * @param String $port
     */
    public function __construct($client, $host = '127.0.0.1', $port = 8080)
    {
        $this->server = $host . ($port ? sprintf(':%s', $port) : false);
        $this->client = $client;
    }

    /**
     * Executes a raw query. It differs from the command because Congow\Orient defines
     * a query as a SELECT only.
     *
     * @api
     * @param   string $sql           The query
     * @param   string $database
     * @param   Int $limit            Results limit, default 20
     * @param   string $fetchPlan
     * @return  Congow\Orient\Http\Response
     */
    public function query($sql)
    {
        $location = $this->server . '/' . urlencode($sql);

        return $this->getHttpClient()->get($location);
    }

    /**
     * Returns the Httpclient of the binding.
     *
     * @return Contract\Httpclient
     */
    public function getHttpClient()
    {
        return $this->client;
    }

    /**
     * Appends the fetchPlan to the location.
     *
     * @param   string $fetchPlan
     * @param   string $location
     * @return  String
     */
    protected function addFetchPlan($fetchPlan, $location)
    {
        return $location .= '/' . urlencode($fetchPlan);
    }
}
