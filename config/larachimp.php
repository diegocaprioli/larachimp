<?php
return [
    
    /*
    |--------------------------------------------------------------------------
    | Mailchimp API key
    |--------------------------------------------------------------------------
    |
    | To obtain an API key, go to mailchimp.com under your profile
    | you will find Extras -> API keys. Paste the key below.
    |
    */
    'api_key' => env('MAILCHIMP_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Mailchimp API Base URI
    |--------------------------------------------------------------------------
    |
    | This is the base URI for the Mailchimp API. All request will be made to
    | this URI. It ususally is in the following form:
    |
    |   https://<dc>.api.mailchimp.com/3.0
    |
    | The <dc> part of the URL corresponds to the data center for your account. 
    | For example, if the last part of your MailChimp API key is us6, all API 
    | endpoints for your account are available at https://us6.api.mailchimp.com/3.0/.
    |
    | See http://developer.mailchimp.com/documentation/mailchimp/guides/get-started-with-mailchimp-api-3/
    | for more information.
    |
    */
    'base_uri' => env('MAILCHIMP_BASE_URI', 'https://us1.api.mailchimp.com/3.0/'),

    /*
    |--------------------------------------------------------------------------
    | Mailchimp Subscriber List ID
    |--------------------------------------------------------------------------
    |    
    | It contains the List ID of the list that will be used to sync the emails
    | from users
    |
    */
    'list_id' => env('MAILCHIMP_LIST_ID', '123'),

    /*
    |--------------------------------------------------------------------------
    | Log error level
    |--------------------------------------------------------------------------
    |    
    | Specifies which level of messages should be logged by the package.
    | info: logs everything, useful for debugging (API requests, responses and any errors)
    | error: only logs errors
    |
    */
    'log_level' => env('LARACHIMP_LOG_LEVEL', 'error'), // Options available: info, error

];