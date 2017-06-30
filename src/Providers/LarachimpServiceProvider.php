<?php namespace DiegoCaprioli\Larachimp\Providers;

use DiegoCaprioli\Larachimp\Services\Larachimp;
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
            __DIR__ . '../../config/larachimp.php' => config_path('diegocaprioli/larachimp/larachimp.php')
        ]);
    }


    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('diegocaprioli_larachimp', function($app) {
            $config = config('diegocaprioli.larachimp.larachimp');
            $larachimp = new Larachimp($app->make('log'));
            $larachimp->initialize($config['api_key'], $config['base_uri']);
            return $larachimp;
        });
    }


}