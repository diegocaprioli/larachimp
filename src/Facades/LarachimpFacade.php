<?php namespace DiegoCaprioli\Larachimp\Facades;

use Illuminate\Support\Facades\Facade;

class LarachimpFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'diegocaprioli_larachimp';
    }
}