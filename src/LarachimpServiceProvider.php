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
            __DIR__ . '/config/larachimp.php' => config_path('diegocaprioli/larachimp/larachimp.php')
        ]);
    }


    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('diegocaprioli_larachimp', function ($app) {
            $config = $app['config']['larachimp'];
            $larachimp = new Larachimp();
            $larachimp->initialize($config['apikey'], $config['baseuri']);
            return $larachimp;
        });
    }


}