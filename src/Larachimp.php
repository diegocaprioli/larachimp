<?php namespace DiegoCaprioli\Larachimp;

use GuzzleHttp\Client;
use Illuminate\Support\Collection;

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
     * Creates a new Larachimp instance
     * 
     * @param string $apikey
     * @param array $clientOptions
     */
    public function __construct($apikey = '', $baseuri = '', $clientOptions = [])
    {
        $this->apikey = $apikey;
        $this->baseuri = $baseuri;
        $this->client = new Client($clientOptions);        
        $this->options['headers'] = [
            'Authorization' => 'apikey ' . $this->apikey
        ];
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
    public function request($resource, $method, $options = [])
    {

    	$options = array_merge($this->options, $options);
    	$response = $this->client->request($method, $this->baseuri . $resource, $options);

    	$collection = new Collection(json_decode($response->getBody()));

        if ($collection->count() == 1) {
            return $collection->collapse();
        }

        return $collection;

    }



}