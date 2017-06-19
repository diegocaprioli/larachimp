<?php namespace DiegoCaprioli\Larachimp\Traits;

use Illuminate\Contracts\Logging\Log;

/**
 * Provides quick and convinient log methods based that used the class 
 * configured Log service and based in the package log_level configuration
 */
trait BasicLogging {

	/**
     * The logger to user
     * @var \Illuminate\Contracts\Logging\Log
     */
    protected $log;


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
     * If there's a logger defined, it logs the string
     * 
     * @param string $string The string to log     
     */
    protected function logInfo($string)
    {
        if (
            !empty($this->log) &&
            config('diegocaprioli.larachimp.larachimp.log_level', 'error') == 'info'
        ) {
            $this->log->info($string);
        }
    }

    /**
     * If there's a logger defined, it logs the string
     * 
     * @param string $string The string to log     
     */
    protected function logError($string)
    {
        if (!empty($this->log)) {
            $this->log->error($string);
        }
    }

}