# Larachimp

A simple Mailchimp API v3 client for the Laravel Framework.

This is a work in progress, and pretty much NOT done yet... 


# Installation

Add the following to your composer.json

```json
{
    "require": {
        "diegocaprioli/larachimp": "~0.1"
    },
    "repositories": [        
	    {
	        "type": "vcs",
	        "url": "https://github.com/diegocaprioli/laravel-helpers.git"
	    }
	],

}
```

## Development

If you plan on updating this code you might want to inlcude it on on your
project and use it directly from your local repository while developing. This 
way any additions or changes you are making to the package will be directly 
available wihtout the need to push a new package version and updating your 
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

Where the `url` must point to the proper directory where you have cloned 
Larachimp.