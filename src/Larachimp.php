<?php namespace DiegoCaprioli\Larachimp;

use \GuzzleHttp\Client;
use \Illuminate\Contracts\Logging\Log;
use \Illuminate\Support\Collection;

class Larachimp {

	/**
     * Base URI for Mailchimp API v3
     *
     * @var string
     */
    private $baseuri;

    /**
     * @var string
     */
    private $apikey;

    /**
     * @var Client
     */
    private $client;

    /**
     * The logeer to user
     * @var Illuminate\Support\Facades\Log
     */
    private $log;


    /**
     * Creates a new Larachimp instance
     * 
     * @param Log The logger instance to use
     */
    public function __construct(Log $log = null)
    {
        $this->log = $log;
    }

    /**
     * Initializes the instance with the proper configuration values
     * 
     * @param  string $apikey The API key to the Mailchimp API
     * @param  string $baseuri The base URI to use for the requests
     * @param  array $clientOptions Te options array in the Guzzle Client expected format     
     */
    public function initialize($apikey = '', $baseuri = '', $clientOptions = [])
    {
        $this->apikey = $apikey;
        $this->baseuri = $baseuri;
        $this->client = new Client($clientOptions);        
        $this->options['headers'] = [
            'Authorization' => 'apikey ' . $this->apikey
        ];
    }

    protected function logRequest($method, $resource, array $options = [])
    {           
        if (!empty($this->log)) {
            $this->log->info('Mailchimp API Request = ' . var_export([
                compact('resource', 'method', 'options')
            ], true));
        }        
    }

    protected function logResponse(Collection $collection)
    {
        if (!empty($this->log)) {
            $array = $collection->toArray();
            $this->log->info('Mailchimp API Response = ' . var_export($array, true));
        }        
    }

    /**
     * Makes an API request
     * 
     * @param  string $resource The resource
     * @param  string $method The request method (GET, POST, PATCH, PUT, DELETE)
     * @param  array $options An array of options as accepted by Guzzle Client
     * 
     * @return Illuminate\Support\Collection
     */
    public function request($method, $resource, array $options = [])
    {

        if (empty($this->apikey)) {
            throw new \Exception("You must initialize the Larachimp instance by calling the initialize() method before attempting any request.");
        }

    	$options = array_merge($this->options, $options);

        $resource = $this->baseuri . $resource;

        $this->logRequest($method, $resource , $options);

    	$response = $this->client->request($method, $resource, $options);
    	$collection = new Collection(json_decode($response->getBody()));

        if ($collection->count() == 1) {
            return $collection->collapse();
        }

        $this->logResponse($collection);

        return $collection;

    }



}