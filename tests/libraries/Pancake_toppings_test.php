<?php

/**
 * Pancake Toppings Test
 *
 * @package   Pancake Toppings
 * @copyright Copyright (c) 2012, Eclarian, LLC.
 * @license   MIT <LICENSE.md>
 * @author    Eclarian LLC <hello@eclarian.com>
 */
class Pancake_toppings_test extends Eclarian_TestCase {

	/**
	 * Hammer Server
	 *
	 * If you are doing active testing on other areas, you may
	 * want to set this to false as it will cause a number of 
	 * requests to be made to your site.
	 * 
	 * @var boolean
	 */
	private $hammer_server = TRUE;

	/**
	 * Pancake
	 * @var object
	 */
	private $pancake;

	/**
	 * Enter Pancake API info to setup
	 *
	 * You must enter your specific info into the test.
	 * 
	 * @var string
	 */
	private $pancake_api_key = '';
	private $pancake_base_url = '';
	private $pancake_version = '1';

	/**
	 * Test Error MSG
	 * @var string
	 */
	private $test_error_msg = 'This is a test error message';

	/**
	 * Load the files
	 */
	public function setUp()
	{
		if ( ! class_exists('Pancake_toppings'))
		{
			require PROJECT_BASE . 'libraries/Pancake_toppings.php';
		}
		
		$this->pancake = new Pancake_toppings();
	}

	// -----------------------------------------------------------------------------
	
	/**
	 * Setup Pancake
	 *
	 * Not a testing function, just sets the info
	 */
	protected function setupPancake($version = NULL)
	{
		if ($version === NULL)
		{
			$version = $this->pancake_version;
		}

		if (empty($this->pancake_base_url))
		{
			exit(PHP_EOL.'FAILURE'.PHP_EOL.'Setup your pancake_base_url in the Pancake_toppings_test.php file');
		}

		if (empty($this->pancake_api_key))
		{
			exit(PHP_EOL.'FAILURE'.PHP_EOL.'Setup your pancake_api_key in the Pancake_toppings_test.php file');
		}

		$this->pancake->set_pancake_api_key($this->pancake_api_key);
        $this->pancake->set_pancake_base_url($this->pancake_base_url);
        $this->pancake->set_pancake_version($version);
	}

	// -----------------------------------------------------------------------------

	/**
	 * Test Set Pancake API Key
	 *
	 * @covers Pancake_toppings::set_pancake_api_key
	 */
	public function testSetPancakeApiKey()
	{
		$return = $this->pancake->set_pancake_api_key($this->pancake_api_key);

		$expected = $this->pancake_api_key;
		$actual = $this->getPrivateProperty($this->pancake, 'pancake_api_key');

		// 1. Make sure that this method is fluent
		// 2. Make sure the method set the property correct
		$this->assertEquals($this->pancake, $return);
		$this->assertEquals($expected, $actual);

		return $this->pancake;
	}

	// -----------------------------------------------------------------------------

	/**
	 * Test Set Pancake Base URL
	 *
	 * @depends testSetPancakeApiKey
	 * @covers Pancake_toppings::set_pancake_base_url
	 */
	public function testSetPancakeBaseUrl($pancake)
	{
		$return = $pancake->set_pancake_base_url($this->pancake_base_url);

		// Method will append a trailing slash
		$expected = $this->pancake_base_url . '/';
		$actual = $this->getPrivateProperty($pancake, 'pancake_base_url');

		// 1. Make sure that this method is fluent
		// 2. Make sure the method set the property correct
		$this->assertEquals($pancake, $return);
		$this->assertEquals($expected, $actual);

		return $pancake;
	}

	// -----------------------------------------------------------------------------

	/**
	 * Test Set Pancake Version
	 *
	 * @depends testSetPancakeBaseUrl
	 * @covers Pancake_toppings::set_pancake_version
	 */
	public function testSetPancakeVersion($pancake)
	{
		// Set with integer or string (we'll test with integer)
		$return = $pancake->set_pancake_version(1);

		// Method should typecast version to a string
		$expected = '1';
		$actual = $this->getPrivateProperty($pancake, 'pancake_version');
		
		// 1. Make sure that this method is fluent
		// 2. Make sure the method set the property correct
		$this->assertEquals($pancake, $return);
		$this->assertEquals($expected, $actual);

		return $pancake;
	}

	// -----------------------------------------------------------------------------
	
	/**
	 * Test Request
	 *
	 * If this fails, it most likely means that your Pancake URL or API key is wrong.
	 *
	 * @covers Pancake_toppings::request
	 */
	public function testRequest()
	{
		$this->setupPancake();

        $response = $this->pancake->request('clients', array('limit' => 1), 'GET');
        
        // 1. Ensure a successful request
        // 2. Ensure that the request got one record
        // 3. Ensure that the request has clients set appropriately
        $this->assertTrue($response->success);
        $this->assertTrue($response->count === 1);
        $this->assertTrue(isset($response->clients) && is_array($response->clients));
	}

	// -----------------------------------------------------------------------------
	
	/**
	 * Test Execute Request
	 * 
	 * @covers Pancake_toppings::create_request_link
	 */
	public function testCreateRequestLink()
	{
		// Setup for Pancake
		$this->pancake->set_pancake_api_key('APIKEY');
        $this->pancake->set_pancake_base_url('http://example.com');
        $this->pancake->set_pancake_version('1');

        // Test for parameters being passed
        $uri = 'clients/show';
        $params = array('id' => 1);
        $method = 'get';

        // This is added in another method before it is passed to this method.
        $params['X-API-KEY'] = $this->getPrivateProperty($this->pancake, 'pancake_api_key');
        $args = array($uri, $params, $method);

        $actual = $this->callPrivate($this->pancake, 'create_request_link', $args);
		$expected = 'http://example.com/api/1/clients/show?id=1&X-API-KEY=APIKEY';

		$this->assertEquals($expected, $actual);

		// Test for POST type of request
		$method = 'post';
		$args = array($uri, $params, $method);
		
		$actual = $this->callPrivate($this->pancake, 'create_request_link', $args);
		$expected = 'http://example.com/api/1/clients/show';

		$this->assertEquals($expected, $actual);

		// Test if no parameters are passed
		$params = array();
		$method = 'get';
		$args = array($uri, $params, $method);
		
		$actual = $this->callPrivate($this->pancake, 'create_request_link', $args);

		$this->assertEquals($expected, $actual);
	}

	// -----------------------------------------------------------------------------
	
	/**
	 * Test Execute Request
	 *
	 * This uses httpbin.org to evaluate all requests
	 * 
	 * If this test fails, please just rerun the test to see if httpbin failed to respond
	 * 
	 * @covers Pancake_toppings::execute_request
	 */
	public function testExecuteRequest()
	{
		// NOTE: Params only matter on POST requests for the execute_request
		// They will already be added as query strings at this point.

		// GET test
		$pr = new Pancake_response();
		$url = 'http://httpbin.org/get?id=1';
		$params = array();
		$method = 'get';
		$args = array($pr, $url, $params, $method);

		$response = $this->callPrivate($this->pancake, 'execute_request', $args);
		
		// 1. Make sure the returned $response object is the SAME object instance that was passed
		// 2. Make sure the class of the object is as expected
		// 3. Make sure the request was successful
		$this->assertTrue($response === $pr);
		$this->assertTrue(get_class($response) === 'Pancake_response');
		$this->assertTrue($response->success);

		// POST test
		$pr = new Pancake_response();
		$url = 'http://httpbin.org/post';
		$params = array('id' => 1, 'limit' => 5);
		$method = 'post';
		$args = array($pr, $url, $params, $method);

		$response = $this->callPrivate($this->pancake, 'execute_request', $args);
		
		// 1. Make sure the returned $response object is the SAME object instance that was passed
		// 2. Make sure the class of the object is as expected
		// 3. Make sure the request was successful
		// 4. Make sure the POST data was passed properly
		// 5. Make sure the POST data passed was received
		$this->assertTrue($response === $pr);
		$this->assertTrue(get_class($response) === 'Pancake_response');
		$this->assertTrue($response->success);
		$this->assertTrue(isset($response->form));
		$this->assertEquals($params, $response->form);
	}

	// -----------------------------------------------------------------------------
	
	/**
	 * Test Validate Request Parameters
	 *
	 * @covers Pancake_toppings::validate_request_parameters
	 */
	public function testValidateRequestParameters()
	{
		// Test Empty
		$params = array();
		$accepted = array();
		$required = array();

		$args = array( 'testing_uri', $params, $accepted, $required );
		$return = $this->callPrivate($this->pancake, 'validate_request_parameters', $args);
		$this->assertTrue($return);

		// Parameters that are not accepted
		$params = array('id' => 2, 'limit' => 10);

		$args = array( 'testing_uri', $params, $accepted, $required );
		$return = $this->callPrivate($this->pancake, 'validate_request_parameters', $args);
		$this->assertFalse($return);

		// All Parameters that are accepted
		$accepted = array('id', 'limit');

		$args = array( 'testing_uri', $params, $accepted, $required );
		$return = $this->callPrivate($this->pancake, 'validate_request_parameters', $args);
		$this->assertTrue($return);

		// Require Parameter that is passed
		$required = array('id');

		$args = array( 'testing_uri', $params, $accepted, $required );
		$return = $this->callPrivate($this->pancake, 'validate_request_parameters', $args);
		$this->assertTrue($return);

		// Require Parameter that is NOT passed
		$required = array('id', 'user_id');

		$args = array( 'testing_uri', $params, $accepted, $required );
		$return = $this->callPrivate($this->pancake, 'validate_request_parameters', $args);
		$this->assertFalse($return);

	}

	// -----------------------------------------------------------------------------

	/**
	 * Test Log Message
	 *
	 * @covers Pancake_toppings::log_message
	 */
	public function testLogMessage()
	{
		// Set an Error
		$params = array('error', $this->test_error_msg);
		$return = $this->callPrivate($this->pancake, 'log_message', $params);
		
		// Make sure that the error is set
		$expected = array($this->test_error_msg);
		$actual = $this->getPrivateProperty($this->pancake, 'errors');

		// 1. Make sure that this method is fluent
		// 2. Make sure the method set the property correct
		$this->assertEquals($this->pancake, $return);
		$this->assertEquals($expected, $actual);

		// Set an info log (this will log the message in the system but not return)
		$params = array('info', $this->test_error_msg);
		$this->callPrivate($this->pancake, 'log_message', $params);

		// 3. Make sure the last function did not change the errors
		$this->assertEquals($expected, $actual);

		return $this->pancake;	
	}

	// -----------------------------------------------------------------------------

	/**
	 * Test Get Errors
	 * 
	 * @depends testLogMessage
	 * @covers Pancake_toppings::get_errors
	 */
	public function testGetErrors($pancake)
	{
		$expected = array($this->test_error_msg);
		$actual = $pancake->get_errors();

		$this->assertEquals($expected, $actual);
	}

	// -----------------------------------------------------------------------------
	// ==== __call() > Overload Testing Below
	// -----------------------------------------------------------------------------

	/**
	 * Test Get Clients
	 * 
	 * @covers Pancake_toppings::__call
	 */
	public function testGetClients()
	{
		if ( ! $this->hammer_server) return;
		$this->setupPancake();

		// Execute overload call
		$params = array('limit' => 1); // We don't need to fetch all of them.
		$response = $this->pancake->get_clients($params);
		
		// Only checking for a successful request
		$this->assertTrue($response->success);
	}

	// -----------------------------------------------------------------------------

	/**
	 * Test Get Client
	 * 
	 * @covers Pancake_toppings::__call
	 */
	public function testGetClient()
	{
		if ( ! $this->hammer_server) return;
		$this->setupPancake();

		// Execute overload call
		$params = array('id' => 2); // We don't have a client #1
		$response = $this->pancake->get_client($params);
		
		// Only checking for a successful request
		$this->assertTrue($response->success);
	}

	// -----------------------------------------------------------------------------

	/**
	 * Test Create, Update And Delete Client
	 *
	 * We have to do this in the same method because we need the ID that is created
	 * 
	 * @covers Pancake_toppings::__call
	 */
	public function testCreateUpdateDeleteClient()
	{
		if ( ! $this->hammer_server) return;
		$this->setupPancake();

		// Execute overload call
		$params = array(
			'first_name' => 'API Test',
			'last_name' => 'API Test Client',
			'email' => 'hello@eclarian.com',
			'title' => 'Mr.',
			'company' => 'Eclarian LLC',
			'address' => '123 Sesame Street, Sesame Street MI 12345, United States',
			'phone' => '555-555-5555',
			'fax' => '555-555-5555',
			'mobile' => '555-555-5555',
			'website' => 'http://www.eclarian.com',
			'profile' => 'What is this used for?',
		);

		$response = $this->pancake->create_client($params);
		$client_created = (isset($response->id) && is_numeric($response->id));

		// 1. Checking for a successful request
		// 2. Checking for the ID of the new client is appropriately returned
		$this->assertTrue($response->success);
		$this->assertTrue($client_created);
		
		// Stop test from proceeding if we didn't actually create the client
		if ( ! $client_created)
		{
			return;
		}

		$client_id = $response->id;
		

		// Attempt to Update this client
		$params = array(
			'id' => $client_id,
			'first_name' => 'API Test Update',
			'last_name' => 'API Test Client Update',
			'email' => 'eclarian@eclarian.com',
			'title' => 'Mrs.',
			'company' => 'Eclarian LLC UPDATE',
			'address' => '123 Updating...',
			'phone' => '666-666-6666',
			'fax' => '666-666-6666',
			'mobile' => '666-666-6666',
			'website' => 'http://www.eclarian.com/update',
			'profile' => 'This is the profile message and known as "NOTES" on the front end',
		);
		
		// Update Client!
		$response = $this->pancake->update_client($params);
		$this->assertTrue($response->success);

		// Attempt to Delete our test client
		$response = $this->pancake->delete_client(array('id' => $client_id));
		$this->assertTrue($response->success);
	}

	// -----------------------------------------------------------------------------

	/**
	 * Test Get Users
	 * 
	 * @covers Pancake_toppings::__call
	 */
	public function testGetUsers()
	{
		if ( ! $this->hammer_server) return;
		$this->setupPancake();

		// Execute overload call
		$params = array('limit' => 1);
		$response = $this->pancake->get_users($params);
		
		// Only checking for a successful request
		$this->assertTrue($response->success);
	}

	// -----------------------------------------------------------------------------

	/**
	 * Test Get User
	 * 
	 * @covers Pancake_toppings::__call
	 */
	public function testGetUser()
	{
		if ( ! $this->hammer_server) return;
		$this->setupPancake();

		// Execute overload call
		$params = array('id' => 1);
		$response = $this->pancake->get_user($params);
		
		// Only checking for a successful request
		$this->assertTrue($response->success);
	}

	// -----------------------------------------------------------------------------

	/**
	 * Test Update User
	 * 
	 * @covers Pancake_toppings::__call
	 */
	public function testUpdateUser()
	{
		if ( ! $this->hammer_server) return;
		$this->setupPancake();

		// UPDATE: Update this number with the user you want to update
		$id = NULL;
		if (is_null($id))
		{
			return;
		}

		// Execute overload call
		$params = array(
			'id' => $id,
			'first_name' => 'Eclarian ~',
			'last_name' => 'LLC ~',
			'company' => 'Eclarian LLC ~',
			'phone' => '555-555-5555',
		);

		$response = $this->pancake->update_user($params);
		
		// Only checking for a successful request
		$this->assertTrue($response->success);
	}

	// -----------------------------------------------------------------------------

	/**
	 * Test Delete User
	 * 
	 * @covers Pancake_toppings::__call
	 */
	public function testDeleteUser()
	{
		if ( ! $this->hammer_server) return;
		$this->setupPancake();

		// UPDATE: Update this number that you want to test the delete on...
		$id = NULL;
		if (is_null($id))
		{
			return;
		}

		// Execute overload call
		$params = array('id' => $id);

		$response = $this->pancake->delete_user($params);
		
		// Only checking for a successful request
		$this->assertTrue($response->success);
	}

	// -----------------------------------------------------------------------------

	/**
	 * Test Get Projects
	 * 
	 * @covers Pancake_toppings::__call
	 */
	public function testGetProjects()
	{
		if ( ! $this->hammer_server) return;
		$this->setupPancake();

		// Execute overload call
		$params = array('limit' => 1);
		$response = $this->pancake->get_projects($params);
		
		// Only checking for a successful request
		$this->assertTrue($response->success);
	}

	// -----------------------------------------------------------------------------

	/**
	 * Test Get Project
	 * 
	 * @covers Pancake_toppings::__call
	 */
	public function testGetProject()
	{
		if ( ! $this->hammer_server) return;
		$this->setupPancake();

		// UPDATE: Update this number with the user you want to update
		$id = NULL;
		if (is_null($id))
		{
			return;
		}

		// Execute overload call
		$params = array('id' => $id);
		$response = $this->pancake->get_project($params);
		
		// Only checking for a successful request
		$this->assertTrue($response->success);
	}

	// -----------------------------------------------------------------------------

	/**
	 * Test Get Tasks
	 * 
	 * @covers Pancake_toppings::__call
	 */
	public function testGetTasks()
	{
		if ( ! $this->hammer_server) return;
		$this->setupPancake();

		// UPDATE: Update this number with the user you want to update
		$project_id = NULL;
		if (is_null($project_id))
		{
			return;
		}

		// Execute overload call
		$params = array('id' => $project_id);
		$response = $this->pancake->get_tasks($params);
		
		// Only checking for a successful request
		$this->assertTrue($response->success);
	}

	// -----------------------------------------------------------------------------

	/**
	 * Test Get Invoices
	 * 
	 * @covers Pancake_toppings::__call
	 */
	public function testGetInvoices()
	{
		if ( ! $this->hammer_server) return;
		$this->setupPancake();

		// Execute overload call
		$params = array('limit' => 1);
		$response = $this->pancake->get_invoices($params);
		
		// Only checking for a successful request
		$this->assertTrue($response->success);
	}

	// -----------------------------------------------------------------------------

	/**
	 * Test Get Invoice
	 * 
	 * @covers Pancake_toppings::__call
	 */
	public function testGetInvoice()
	{
		if ( ! $this->hammer_server) return;
		$this->setupPancake();

		// UPDATE: Update this number with the user you want to update
		$id = NULL;
		if (is_null($id))
		{
			return;
		}

		// Execute overload call
		$params = array('id' => $id);
		$response = $this->pancake->get_invoice($params);
		
		// Only checking for a successful request
		$this->assertTrue($response->success);
	}

	// -----------------------------------------------------------------------------

}