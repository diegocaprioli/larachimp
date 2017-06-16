<?php namespace DiegoCaprioli\Larachimp\Services;

use DiegoCaprioli\Larachimp\Traits\BasicLogging;
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
     * @var \GuzzleHttp\Client
     */
    protected $client;   

    /**
     * Make this class use a logger and it's basic methods
     */
    use BasicLogging;

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
     * @param  string $baseuri The base URI to use for the requests. Make sure it has a trailing/ending "/"
     * @param  array $clientOptions The options array in the Guzzle Client expected format          *
     * @see http://guzzle3.readthedocs.io/docs.html Guzzle Docs
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
     * Makes a simple API request to Mailchimp. 
     * It uses the base URI used when initializing this service class, and 
     * concatenates the $resource to form the URL of the request.
     * The optional $options array are the standar Guzzle Client request options.
     * The request will automatically include an option setting the 'headers' to
     * use the Authorization API KEY as configured.
     * Finally calls the request method of the Guzzle client using $method, the 
     * generated URL of the request, and the $options.
     * 
     * @param  string $resource The resource
     * @param  string $method The request method (GET, POST, PATCH, PUT, DELETE)
     * @param  array $options An array of request options, as accepted by Guzzle Client request method
     * @return mixed The json_decode'd version of the response body 
     * @see http://developer.mailchimp.com/documentation/mailchimp/guides/get-started-with-mailchimp-api-3/ Mailchimp API v3 Reference
     * @see http://guzzle3.readthedocs.io/docs.html Guzzle Docs
     */
    public function request($method, $resource, array $options = [])
    {

        if (empty($this->apikey)) {
            throw new \Exception("You must initialize the Larachimp instance by calling the initialize() method before attempting any request.");
        }

        $options = array_merge($this->options, $options);

        $resource = $this->baseuri . $resource;

        $this->logRequest($method, $resource, $options);

        try {
            $response = $this->client->request($method, $resource, $options);
        } catch (\GuzzleHttp\Exception\ClientException $e) {

            $this->logError(
                "A \GuzzleHttp\Exception\ClientException has been thrown. Request: " .
                var_export($e->getRequest(), true) . 
                " - Response: " . var_export($e->getResponse()->getBody()->getContents(), true)
            );
            throw $e;            
        }
        
        $responseContents = $response->getBody()->getContents(); 
        $this->logResponse($responseContents);
        $decodedResponse = json_decode($responseContents);
        
        return $decodedResponse;

    }



}