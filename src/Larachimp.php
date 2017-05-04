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
    protected $baseuri;

    /**
     * @var string
     */
    protected $apikey;

    /**
     * @var Client
     */
    protected $client;

    /**
     * The logeer to user
     * @var Illuminate\Support\Facades\Log
     */
    protected $log;


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

    /**
     * If there's a logger defined, it logs the request made
     * 
     * @param  string $method
     * @param  string $resource
     * @param  array $options
     */
    protected function logRequest($method, $resource, array $options = [])
    {           
        if (!empty($this->log)) {
            $this->log->info('Mailchimp API Request = ' . var_export([
                compact('resource', 'method', 'options')
            ], true));
        }        
    }

    /**
     * If there's a logger defined, it logs the response returned
     * 
     * @param  Collection $collection
     */
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

        try {
            $response = $this->client->request($method, $resource, $options);
        } catch (\GuzzleHttp\Exception\ClientException $e) {

            $this->log->error(
                "A \GuzzleHttp\Exception\ClientException has been thrown. Request: " .
                var_export($e->getRequest(), true) . 
                " - Response: " . var_export($e->getResponse(), true)
            );
            throw $e;
            
        }    	

    	$collection = new Collection(json_decode($response->getBody()));

        $this->logResponse($collection);

        return $collection;

    }



}