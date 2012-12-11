<?php

// Errors on full!
ini_set('display_errors', 1);
error_reporting(E_ALL | E_STRICT);

$dir = rtrim(realpath(dirname(__FILE__)), DIRECTORY_SEPARATOR);
$top = rtrim(realpath($dir.'/../'), DIRECTORY_SEPARATOR);

$dir .= DIRECTORY_SEPARATOR;
$top .= DIRECTORY_SEPARATOR;

// BASEPATH constant isn't used, just needs to be in the top of the file for CI
defined('BASEPATH') OR define('BASEPATH', TRUE);
defined('PROJECT_BASE') OR define('PROJECT_BASE', $top);


// CodeIgniter method used internally
if ( ! function_exists('log_message'))
{
	// just create a stub 
	function log_message() {};
}

// Load Eclarian_TestCase Class
require $dir . 'Eclarian_TestCase.php';