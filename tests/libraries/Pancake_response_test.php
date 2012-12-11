<?php

/**
 * Pancake Toppings Test
 *
 * @package   Pancake Toppings
 * @copyright Copyright (c) 2012, Eclarian, LLC.
 * @license   MIT <LICENSE.md>
 * @author    Eclarian LLC <hello@eclarian.com>
 */
class Pancake_response_test extends Eclarian_TestCase {

	/**
	 * Setup Data Group
	 * 
	 * @var array
	 */
	private $setupDataGroup = array(
		'array' => array('this', 'is', 'an', 'array'),
		'int' => 3,
		'float' => 2.3,
		'string' => 'string',
		'bool' => TRUE
	);

	/**
	 * Load the files
	 */
	public function setUp()
	{
		if ( ! class_exists('Pancake_response'))
		{
			require PROJECT_BASE . 'libraries/Pancake_response.php';
		}
	}

	// -----------------------------------------------------------------------------
	
	/**
	 * Test Successful Parse
	 *
	 * Since all the properties are public, assigning values to them isn't something
	 * that really needs to be checked if it worked or not. 
	 *
	 * @covers Pancake_response::get_array
	 * @covers Pancake_response::parse
	 */
	public function testSuccessfulParse()
	{
		$json = json_encode($this->setupDataGroup);

		// Pancake_response
		$response = new Pancake_response();

		$response->url = 'http://example.com';
        $response->status_code = 200;
        $response->request_time = 0.34;

        // Request was made, determine if it was successful or not.
        $response->success = TRUE;
        $response->body = $json;
        $response->parse();
		
        // After the JSON is parsed, it should equal the original array
		$this->assertEquals($this->setupDataGroup, $response->get_array());
		// Success should still be true. If Parse fails, then this will be false
		$this->assertTrue($response->success);

		return $response;
	}

	// -----------------------------------------------------------------------------
	
	/**
	 * Test Magic Get
	 * 
	 * @covers Pancake_response::__get
	 * @depends testSuccessfulParse
	 */
	public function testMagicGet($response)
	{
		$this->assertEquals($this->setupDataGroup['array'], $response->array);
		$this->assertEquals($this->setupDataGroup['int'], $response->int);
		$this->assertEquals($this->setupDataGroup['float'], $response->float);
		$this->assertEquals($this->setupDataGroup['string'], $response->string);
		$this->assertEquals($this->setupDataGroup['bool'], $response->bool);

		// Test an unset property
		$this->assertTrue(is_null($response->not_set_variable));
	}

	// -----------------------------------------------------------------------------
	
	/**
	 * Test Magic Isset
	 * 
	 * @covers Pancake_response::__isset
	 * @depends testSuccessfulParse
	 */
	public function testMagicIsset($response)
	{
		$this->assertTrue(isset($response->array));
	}

	// -----------------------------------------------------------------------------
	
	/**
	 * Test Magic Unset
	 * 
	 * @covers Pancake_response::__unset
	 * @depends testSuccessfulParse
	 */
	public function testMagicUnset($response)
	{
		$this->assertTrue(isset($response->array));
		
		unset($response->array);
		
		$this->assertFalse(isset($response->array));
	}

	// -----------------------------------------------------------------------------
	
	/**
	 * Test Failed Parse
	 *
	 * Pass HTML or something besides JSON
	 *
	 * @covers Pancake_response::prase
	 */
	public function testFailedParse()
	{		
		$badJSON = '<html><head></head><body><h1>Error</h1></body></html>';

		// Pancake_response
		$response = new Pancake_response();

		$response->url = 'http://example.com';
		// Presumes the request responded with incorrect header
		// and with an invalid data type
        $response->status_code = 200; 
        $response->request_time = 0.34;

        // Request was made, determine if it was successful or not.
        $response->success = TRUE;
        $response->body = $badJSON;
        $response->parse();
		
		// Success should be FALSE as the response failed
		$this->assertFalse($response->success);
	}
}
