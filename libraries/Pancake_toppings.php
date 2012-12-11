<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Pancake Toppings
 *
 * Handles API requests to Pancake
 * 
 * PHP methods to call API are all managed via the __call() method using the
 * property $api_calls which has the details for each call.
 *
 * @uses      Pancake_response
 * @version   1.0.0
 * @package   Pancake Toppings
 * @copyright Copyright (c) 2012, Eclarian, LLC.
 * @license   MIT <LICENSE.md>
 * @author    Eclarian LLC <hello@eclarian.com>
 */
class Pancake_toppings {

    /**
     * API Calls
     *
     * Stored by API version
     * ['pancake_magic_method' => [
     *     'uri',
     *     'GET|POST',
     *     ['available_params'],
     *     ['required_params']]
     * ]
     *
     * Supports the following major catogories
     *  - Clients
     *  - Projects
     *  - Tasks
     *  - Invoices
     *  - Users
     * 
     * @var array
     */
    private $api_calls = array(
        'version_1' => array(
            
            // Clients
            'get_clients' => array(
                'clients',
                'GET',
                array('limit', 'start', 'sort_by', 'sort_dir')
            ),
            'get_client' => array(
                'clients/show',
                'GET',
                array('id'),
                array('id')
            ),
            'create_client' => array(
                'clients/new', 
                'POST', 
                array('first_name', 'last_name', 'email', 'title', 'company', 'address', 'phone', 'fax', 'mobile', 'website', 'profile'), 
                array('first_name', 'last_name', 'email')
            ),
            'update_client' => array(
                // DOCUMENTED BY PANCAKE as clients/edit but is actually clients/update...
                //'clients/edit',
                'clients/update',
                'POST',
                array('id', 'first_name', 'last_name', 'email', 'title', 'company', 'address', 'phone', 'fax', 'mobile', 'website', 'profile'), 
                array('id') // Only actually need id
            ),
            'delete_client' => array(
                'clients/delete',
                'POST',
                array('id'),
                array('id')
            ),

            // Projects
            'get_projects' => array(
                'projects',
                'GET',
                array('limit', 'start', 'sort_by', 'sort_dir')
            ),
            'get_project' => array(
                'projects/show',
                'GET',
                array('id'),
                array('id')
            ),
            //'create_project' => array(),
            //'update_project' => array(),
            // TODO: find out if there is an undocumented 'delete_project'

            // Tasks -> All are associated with projects
            'get_tasks' => array(
                'project_tasks',
                'GET',
                array('id'),
                array('id')
            ),
            //'create_task' => array(),
            //'update_task' => array(),
            //'delete_task' => array(),
            //'add_time' => array(),
            //'complete_task' => array(),
            //'reopen_task' => array(),

            // Invoices
            'get_invoices' => array(
                'invoices',
                'GET',
                array('client_id', 'limit', 'start', 'sort_by', 'sort_dir')
            ),
            'get_invoice' => array(
                'invoices/show',
                'GET',
                array('id'),
                array('id')
            ),
            //'create_invoice' => array(),
            //'update_invoice' => array(),
            //'delete_invoice' => array(),
            //'open_invoice' => array(),
            //'close_invoice' => array(),
            //'mark_invoice_paid' => array(),
            //'send_invoice' => array(),

            // Users -> Currently no "create_user" method exists
            'get_users' => array(
                'users',
                'GET',
                array('limit', 'start', 'sort_by', 'sort_dir')
            ),
            'get_user' => array(
                'users/show',
                'GET',
                array('id'),
                array('id')
            ),
            // Create user is undocumented
            // 'create_user' => array(),
            'update_user' => array(
                // DOCUMENTED BY PANCAKE as users/edit but is actually users/update...
                //'users/edit',
                'users/update',
                'POST',
                array('id', 'first_name', 'last_name', 'company', 'phone'),
                array('id')
            ),
            'delete_user' => array(
                'users/delete',
                'POST',
                array('id'),
                array('id')
            ),
        )
    );

    /**
     * Errors
     * 
     * @var array
     */
    private $errors = array();

    /**
     * Request Timeout
     *
     * Max number of seconds to make a request.
     * 
     * @var integer
     */
    private $request_timeout = 5;

    /**
     * Pancake API Key
     * 
     * @var string
     */
    private $pancake_api_key;

    /**
     * Pancake Base URL
     * 
     * @var string
     */
    private $pancake_base_url;

    /**
     * Pancake Pass API Key by Header
     *
     * This is true by default because there are problems with some of the
     * update and create methods which take the entire POST var and 
     * pass it to the method. 
     * 
     * @var boolean
     */
    private $pancake_pass_key_by_header = TRUE;

    /**
     * Pancake Version
     * 
     * @var string
     */
    private $pancake_version = '1';

    /**
     * Prevent Unverified Requests
     * 
     * @var boolean
     */
    protected $prevent_unverified_requests = TRUE;

    // -----------------------------------------------------------------------------
    
    /**
     * Load Pancake_Response
     */
    public function __construct()
    {
        // Make sure that 
        if ( ! class_exists('Pancake_response'))
        {
            require 'Pancake_response.php';
        }
    }

    // -----------------------------------------------------------------------------
    
    /**
     * Call API Methods
     * 
     * @param  string
     * @param  array   Should only have one parameter, which is the $param arg
     * @return object Pancake_Request
     */
    public function __call($name, $args)
    {
        $version = 'version_' . $this->pancake_version;
        if ( ! isset($this->api_calls[$version][$name]))
        {
            $this->log_message('error', "Method $name() is not supported in API $version");
            return new Pancake_response();
        }

        // Get the URI, Method and Accepted Parameters
        list($uri, $method, $accepted, $required) = array_pad($this->api_calls[$version][$name], 4, array());
        $params = (isset($args[0]) && is_array($args[0])) ? $args[0] : array();

        // Validate the parameters are accurate. Incorrect usage will halt the request
        $request_valid = $this->validate_request_parameters($uri, $params, $accepted, $required);
        if ($request_valid === FALSE && $this->prevent_unverified_requests === TRUE)
        {
            // Defaults to failed request
            return new Pancake_response();
        }

        // Perform the Request
        return $this->request($uri, $params, $method);
    }

    // -----------------------------------------------------------------------------
    
    /**
     * Document API Calls
     *
     * This is just a helper method to be able to get a visual idea of how to use 
     * this class.
     * 
     * @param  string  Version Number
     * @return string
     */
    public function document_api_calls($version = '1')
    {
        $pancake_version = $version;
        $version = 'version_' . $version;

        if ( ! isset($this->api_calls[$version]))
        {
            return '';
        }

        $docs = "<h1>Pancake Payments API - Version $pancake_version</h1>";

        // Add to the docs
        foreach ($this->api_calls[$version] as $php_method => $call)
        {
            list($uri, $method, $accepted, $required) = array_pad($call, 4, array());

            $docs .= '<h3>'.$php_method.'(<span style="color:#999">$params</span>)</h3>';
            $docs .= 'URI: api/' . $pancake_version . '/' . $uri;
            $docs .= '<br/>';
            $docs .= 'VERB: ' . $method;
            $docs .= '<br/>';
            $docs .= 'Accepted Params: ' . (empty($accepted) ? 'None' : json_encode($accepted));
            $docs .= '<br/>';
            $docs .= 'Required Params: ' . (empty($required) ? 'None' : json_encode($required));
            $docs .= '<br/>';
            
        }

        return $docs;
    }

    // -----------------------------------------------------------------------------

    /**
     * Get Errors
     * 
     * @return array
     */
    public function get_errors()
    {
        return $this->errors;
    }

    // -----------------------------------------------------------------------------
    
    /**
     * Set Pancake API Key
     *
     * @param  string API key for all requests
     * @return object Pancake
     */
    public function set_pancake_api_key($key)
    {
        $this->pancake_api_key = $key;
        return $this;
    }

    // -----------------------------------------------------------------------------
    
    /**
     * Set Pancake Base URL
     *
     * @param  string Base URL WITHOUT "/api"
     * @return object Pancake
     */
    public function set_pancake_base_url($url)
    {
        $this->pancake_base_url = trim($url, '/') . '/';
        return $this;
    }

    // -----------------------------------------------------------------------------
    
    /**
     * Set Pancake Version
     *
     * @param  string Version Number /api/$version/$uri
     * @return object Pancake
     */
    public function set_pancake_version($version = '1')
    {
        $this->pancake_version = (string) $version;
        return $this;
    }

    // -----------------------------------------------------------------------------
    
    /**
     * Request
     * 
     * @param  string Example: /api/$this->version/$uri
     * @param  array  Parameters, either as a query string or POST data
     * @param  string Type of Request: GET or POST
     * @return object Pancake_response
     */
    public function request($uri, $params = array(), $method = 'GET')
    {
        // Default Response is Failure, so we can just return.
        $response = new Pancake_response();

        if ( ! $this->pancake_base_url OR ! $this->pancake_api_key) {
            $this->log_message('error', 'Pancake::request() - Failed to set the pancake URL or API key.');
            return $response;
        }

        // Make Request and Return the Response
        $this->build_request($response, $uri, $params, $method);
        return $response;
    }

    // -----------------------------------------------------------------------------
    
    /**
     * Build Request
     *
     * @param  object Pancake_response 
     * @param  string Example: /api/$this->version/$uri
     * @param  array  Parameters, either as a query string or POST data
     * @param  string Type of Request: GET or POST
     * @return object Pancake_response
     */
    protected function build_request(Pancake_response $response, $uri, $params, $method)
    {
        $method = strtolower($method);
        if ( ! in_array($method, array('get', 'post')))
        {
            $this->log_message('error', "Pancake::build_request_url() - Method ($method) not supported. Use GET or POST");
            return $response;
        }

        // Add Pancake API Key to every request if not passed by header
        if ($this->pancake_pass_key_by_header !== TRUE)
        {
            $params['X-API-KEY'] = $this->pancake_api_key;
        }

        // Build base Request URL
        $request_url = $this->create_request_link($uri, $params, $method);
        return $this->execute_request($response, $request_url, $params, $method);      
    }

    // -----------------------------------------------------------------------------
    
    /**
     * Execute Request
     *
     * @uses   cURL   http://php.net/manual/en/book.curl.php
     * @param  object Pancake_response 
     * @param  string Example: /api/$this->version/$uri
     * @param  array  Parameters, either as a query string or POST data
     * @param  string Type of Request: GET or POST
     * @return object Pancake_response
     */
    protected function execute_request(Pancake_response $response, $request_url, $params, $method)
    {
        // Setup Request
        $curl = curl_init();

        // POST request will be passed as multipart/form-data
        if ($method === 'post')
        {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        }

        // Add API header
        if ($this->pancake_pass_key_by_header)
        {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-API-KEY: ' . $this->pancake_api_key));
        }

        // Setup Options for Request. Only 5 seconds allowed for the request
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->request_timeout);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_URL, $request_url);

        // Make Request
        $curl_response = curl_exec($curl);

        $response->url = $request_url;
        $response->status_code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $response->request_time = (float) curl_getinfo($curl, CURLINFO_TOTAL_TIME);

        // Request Failed...
        if ($curl_response === FALSE)
        {
            $this->log_message('error', "Pancake::request() - Curl Error #".curl_errno($curl)." - ".curl_error($curl));
            return $response;
        }

        // Request was made, determine if it was successful or not.
        $response->success = ($response->status_code >= 200 && $response->status_code < 300);
        $response->body = $curl_response;
        $response->parse();

        // Double Check API Status Response. If it is not set, it will be NULL
        if ($response->status === FALSE)
        {
            $error = ($response->error) ? $response->error : 'Unkown Pancake API Error';
            $this->log_message('error', $error);
            $response->success = FALSE;
            return $response;
        }

        // Close the connection and return the result
        curl_close($curl);
        return $response;
    }

    // -----------------------------------------------------------------------------
    
    /**
     * Create Request Link
     *
     * @param  string Example: /api/$this->version/$uri
     * @param  array  Parameters, either as a query string or POST data
     * @param  string Type of Request: get or post
     * @return string
     */
    protected function create_request_link($uri, $params, $method)
    {
        // Build base Request URL
        $uri = '/' . trim($uri, '/');
        $request_base_url = $this->pancake_base_url . 'api/' . $this->pancake_version . $uri;

        // Append query string for parameters
        if ($method === 'get' && ! empty($params))
        {
            $request_base_url .= '?' . http_build_query($params);
        }
     
        return $request_base_url;   
    }

    // -----------------------------------------------------------------------------
    
    /**
     * Log Message
     *
     * Wraps CI's log_message and only assigns errors to the errors URI.
     * 
     * @param  string
     * @param  string
     * @return object
     */
    protected function log_message($type, $msg)
    {
        log_message($type, $msg);

        if ($type === 'error')
        {
            $this->errors[] = $msg;
        }

        return $this;
    }

    // -----------------------------------------------------------------------------
    
    /**
     * Validate Request Parameters
     * 
     * @param  string URI Requested. For debugging purposes
     * @param  array  Parameters to be passed with request
     * @param  array  Accepted Parameters by request
     * @param  array  Required Parameters
     * @return boolean
     */
    protected function validate_request_parameters($uri, $params = array(), $accepted = array(), $required = array())
    {
        // If there are no required params and none available, then we are all set
        if (empty($params) && empty($required))
        {
            return TRUE;
        }

        // Check if there are any required that are not present
        foreach ($required as $r)
        {
            if ( ! isset($params[$r]))
            {
                $this->log_message('error', "Pancake::validate_request_parameters() - Required parameter '$r' was not found for request uri '$uri'. Parameters provided: " . json_encode($params));
                return FALSE;
            }
        }

        // Check if there are any parameters set that are not accepted
        foreach ($params as $p => $v)
        {
            if ( ! in_array($p, $accepted))
            {
                $this->log_message('error', "Pancake::validate_request_parameters() - Unaccepted parameter '$p' was passed for request uri '$uri'");
                return FALSE;
            }
        }

        return TRUE;
    }
}
// END of Pancake_toppings.php