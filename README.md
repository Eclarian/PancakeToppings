# Pancake Toppings - A [Pancake Payments](https://pancakeapp.com/) API Wrapper

This is what we use internally at [Pancake Toppings](http://pancaketopping.com/) for all of our toppings. We will continue to upgrade it as the API is updated and new toppings are added on our site.

**This is not yet comprehensive of the Pancake Payments API.**

## Testing

You must add your API details (URL and Key) to the top of the tests/libraries/Pancake_toppings_test.php file. In order to run all of the tests, some of the API request require specific keys. Search for "UPDATE:" to provide the specific keys you want to test.

Use phpunit to run the tests. This will also help you evaluate errors in your Pancake install API as well.

## API Calls

All api calls are handled through the magic method __call(). The details of the calls are stored in the property $api_calls. 

The array key is the method to be called via PHP. They array values are as follows: ['api_endpoint', 'VERB', ['accepted'], ['required']]

	'get_client' => array(
		'clients/show',
		'GET',
		array('id'),
		array('id')
	)


## Quick Documentation of API Calls

There is a quick documentation method within the class that will allow you to see the accepted & required parameter for each method.

	// Document API Version 1 of the API calls
	$pancake = new Pancake_toppings();
	echo $pancake->document_api_calls('1');


## Example of API Call

	$pancake = new Pancake_toppings();
	$pancake->set_pancake_api_key('XXXXXX')
			->set_pancake_base_url('http://example.com');
	
	// $response is a Pancake_response Object.
	$response = $pancake->get_client(array('id' => 1));

	if ( ! $response->success)
	{
		var_dump($response->status_code);
		var_dump($pancake->get_errors());
		return;
	}

	// Output the client details
	var_dump($response->client);