<?php namespace DiegoCaprioli\Larachimp;

use DiegoCaprioli\Larachimp\Larachimp;
use Illuminate\Support\ServiceProvider;

class LarachimpServiceProvider extends ServiceProvider {

	/**
     * Register paths to be published by the publish command.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/larachimp.php' => config_path('larachimp.php')
        ]);
    }


    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Larachimp::class, function ($app) {

            $config = $app['config']['larachimp'];
            return new Larachimp($config['apikey'], $config['baseuri']);

        });
    }


}