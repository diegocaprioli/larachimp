<?php namespace DiegoCaprioli\Larachimp\Services;

use \GuzzleHttp\Client;
use \Illuminate\Contracts\Logging\Log;
use \Illuminate\Support\Collection;

/**
 * Handles the basic interaction with Mailchimp v3 API
 */
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
     * The connection options
     * @var array
     */
    protected $options;

    /**
     * @var Client
     */
    protected $client;   

    /**
     * The logeer to user
     * @var \Illuminate\Contracts\Logging\Log
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
     * Sets the log attribute
     * 
     * @param Log $log
     */
    public function setLog(Log $log = null)
    {
        $this->log = $log;
    }

    /**
     * Initializes the instance with the proper configuration values
     * 
     * @param  string $apikey The API key to the Mailchimp API
     * @param  string $baseuri The base URI to use for the requests
     * @param  array $clientOptions The options array in the Guzzle Client expected format     
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
     * If there's a logger defined, it logs the string
     * 
     * @param string $string The string to log     
     */
    protected function logInfo($string)
    {
        if (!empty($this->log)) {
            $this->log->info($string);
        }
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
        $this->logInfo('Mailchimp API Request = ' . var_export([
            compact('resource', 'method', 'options')
        ], true));        
    }


    /**
     * If there's a logger defined, it logs the response returned
     * 
     * @param mixed $response
     */
    protected function logResponse($response)
    {
        $this->logInfo('Mailchimp API Response = ' . var_export($response, true));
    }

    /**
     * Makes an API request
     * 
     * @param  string $resource The resource
     * @param  string $method The request method (GET, POST, PATCH, PUT, DELETE)
     * @param  array $options An array of options as accepted by Guzzle Client
     * 
     * @return mixed The json_decode'd version of the response body 
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
                " - Response: " . var_export($e->getResponse()->getBody()->getContents(), true)
            );
            throw $e;            
        }
        
        $responseContents = $response->getBody()->getContents(); 
        $this->logResponse($responseContents);
        $decodedResponse = json_decode($responseContents);
        $this->logInfo('JSON decode error ? ' . json_last_error_msg());
        $this->logInfo('JSON Decoded Response = ' . var_export($decodedResponse, true));

        return $decodedResponse;

    }



}