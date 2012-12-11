<?php

/**
 * Eclarian Test Case
 *
 * Adds callPrivate() and getPrivateProperty() to be able to test
 * the full class without having to set everything as public
 *
 * @package   Eclarian 
 * @copyright Copyright (c) 2012, Eclarian, LLC.
 * @license   MIT <LICENSE.md>
 * @author    Eclarian LLC <hello@eclarian.com>
 */
class Eclarian_TestCase extends PHPUnit_Framework_TestCase {

	/**
	 * Call Private
	 * 
	 * @param  object
	 * @param  string  Method to call on object
	 * @param  array   Arguments to pass to $method
	 * @return mixed   Return of $method
	 */
	public function callPrivate($obj, $method, $args)
	{
		$reflection = new ReflectionObject($obj);
		$m = $reflection->getMethod($method);
		$m->setAccessible(TRUE);
		return $m->invokeArgs($obj, $args);
	}

	// -----------------------------------------------------------------------------
	
	/**
	 * Get Private Property
	 *
	 * Will return the value of any private or protected property 
	 * from the current object.
	 * 
	 * @param  object
	 * @param  string
	 * @return mixed   Value of the property
	 */
	public function getPrivateProperty($obj, $property)
	{
		$reflection = new ReflectionObject($obj);
		$prop = $reflection->getProperty($property);
		$prop->setAccessible(TRUE);
		return $prop->getValue($obj);
	}
}