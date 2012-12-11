<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Pancake Response
 * 
 * @version   1.0.0
 * @package   Pancake Toppings
 * @copyright Copyright (c) 2012, Eclarian, LLC.
 * @license   MIT <LICENSE.md>
 * @author    Eclarian LLC <hello@eclarian.com>
 */
class Pancake_response {

    /**
     * Body
     *
     * This should always be a JSON string
     * 
     * @var string
     */
    public $body = '';

    /**
     * Parsed
     * 
     * @var array
     */
    public $parsed = array();

    /**
     * Request Time
     * 
     * @var float
     */
    public $request_time = 0;

    /**
     * Success
     *
     * @var boolean
     */
    public $success = FALSE;

    /**
     * Status Code
     *
     * FALSE if no request was made to the server
     * 
     * @var integer
     */
    public $status_code = FALSE;

    /**
     * URL
     * 
     * @var string
     */
    public $url = '';

    // -----------------------------------------------------------------------------
    
    /**
     * Get Array
     * 
     * @return array
     */
    public function get_array()
    {
        return $this->parsed;
    }

    // -----------------------------------------------------------------------------

    /**
     * Parse
     * 
     * @return boolean
     */
    public function parse()
    {
        $parsed = json_decode($this->body, TRUE);
        
        // Check if the decoding worked
        if (is_null($parsed))
        {
            // Extend this method and use json_last_error() for detailed reports on failure
            $this->success = FALSE;
            return FALSE;           
        }

        $this->parsed = $parsed;
        return TRUE;
    }

    // -----------------------------------------------------------------------------
    
    /**
     * Get
     *
     * This will parse the response and return the key requested
     * Example: $response->status NULL if nothing found
     * 
     * @param  string
     * @return mixed   NULL if key not found
     */
    public function __get($key)
    {
        return (isset($this->parsed[$key])) ? $this->parsed[$key] : NULL;
    }

    // -----------------------------------------------------------------------------
    
    /**
     * Isset
     *
     * @param  string  Property to check
     * @return boolean
     */
    public function __isset($property)
    {
        return isset($this->parsed[$property]);
    }

    // -----------------------------------------------------------------------------
    
    /**
     * Unset
     *
     * @param  string  Property to unset
     * @return boolean
     */
    public function __unset($property)
    {
        if ( ! isset($this->parsed[$property]))
        {
            return FALSE;
        }

        unset($this->parsed[$property]);
        return TRUE;
    }
}