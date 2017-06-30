# Installation

Add the following to your composer.json

```json
{
    "require": {
        "diegocaprioli/larachimp": "~0.4"
    },
    "repositories": [        
        {
            "type": "vcs",
            "url": "https://github.com/diegocaprioli/larachimp.git"
        }
    ],

}
```

#### Service Provider
You can register our [service provider](http://laravel.com/docs/5.1/providers) in your `app.php` config file.

```php
// config/app.php
'providers' => [
    ...
    DiegoCaprioli\Larachimp\LarachimpServiceProvider::class
]
```

#### Facade
If you prefer [facades](http://laravel.com/docs/5.1/facades), make sure you add this as well:

```php
// config/app.php
'aliases' => [
    ...
    'LarachimpFacade' => DiegoCaprioli\Larachimp\LarachimpFacade::class
]
```

#### Configuration
Publish the config by running:

    php artisan vendor:publish

Now, the config file will be located under `config/larachimp.php`:

```php
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
    'api_key' => '',

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
    'base_uri' => 'https://us1.api.mailchimp.com/3.0/',

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

];
```


## Development

If you plan on updating this code you might want to inlcude it on on your
project and use it directly from your local repository while developing. This 
way any additions or changes you are making to the package will be directly 
available without the need to push a new package version and updating your 
composer dependencies in the client project.

You can achieve that by cloning this project into your own local drive and 
requiring the package like this:

```json
{
    "require": {
        "diegocaprioli/larachimp": "dev-master"
    },
    "repositories": [        
        {
            "type": "path",
            "url": "../diegocaprioli/larachimp"
        }
    ]
}
```

Where the parameter `url` must point to the proper directory where you have 
cloned Larachimp.