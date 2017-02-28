<?php

use Silex\Application as App;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Silex\Provider\SessionServiceProvider;
//extended class of RequestMatcher
use Sms\RequestMatcherIps;

require_once __DIR__.'/../vendor/autoload.php';

$app    = new App();
$client = new Sms\Gateway();

if (in_array(getenv('DEV_ENVIRONEMENT'), ['1', 'true'])) {
	$app['debug'] = true;
}


/**
 * Retrieve all messages
 */
$app->get('/sms', function (Request $request) use ($app, $client) {

    $reqMatcher = new RequestMatcherIps();
    
    //pass the client request (contain the client's ip) to RequestMatcherIps 
    //to run it against the restrict ips list
    //return true (if matched) or false
    $ipIsMatched = $reqMatcher->checkIpsList($request);

    /*$ipIsMatched = RequestMatcherIps::checkIpsList($request);*/
    
    //Matched an ip (Access Granted)
    if ($ipIsMatched) {

    	$messages = array_map(
			function (Sms\MessageReceived $message = null) {
				return $message->flatten();
			},
			$client->getIncoming(
				in_array(
					$request->get('expunge'),
					[
						1, "true"
					]
				)
			)
		);

    }
    //No ips have matched (Access Denied)
    else{

    	$messages = array('Error' => 'Access is denied');

    }

	return $app->json($messages);
});

/**
 * Retrieve all failed messages
 */
$app->get('/sms/failed', function (Request $request) use ($app, $client) {

	$reqMatcher = new RequestMatcherIps();
    
    $ipIsMatched = $reqMatcher->checkIpsList($request);
    
    if ($ipIsMatched) {

    	$message = array_map(		
			function (Sms\MessageFailed $message = null) {
				return $message->flatten();
			},
			$client->getFailed(
				in_array(
					$request->get('fail'),
					[
						1, "true"
					]
				)
			)
		);

    }else{

    	$message = array('Error' => 'Access is denied');

    }

	return $app->json($message);
});

/**
 * Send an sms
 */
$app->post('/sms', function (Request $request) use ($app, $client) {
	
	$reqMatcher = new RequestMatcherIps();
    
    $ipIsMatched = $reqMatcher->checkIpsList($request);
    
    if ($ipIsMatched) {

    	if (
			is_null($request->get('to'))
			|| is_null($request->get('body'))
		) {
			throw new \Exception(
				"Unable to send message: Missing data, this should be checked for in"
				.	"'Sms\Message' class really. Validation methods just need expanding"
			);
		}

		$message = new Sms\Message(
			$request->get('body'),
			$request->get('to')
		);

		$client->send($message);

		return $app->json(
			[
				'success' => 'Message sent successfully'
			],
			200
		);

    }else{

    	return $app->json(
			[
				'Error' => 'Access is denied'
			],
			200
		);

    }

});

/**
 * Handle errors
 */
$app->error(function (\Exception $e, Request $request, $code) use ($app) {

	return $app->json(
		[
			"error" => $e->getMessage(),
			"code"  => $code
		],
		$code
	);
});

$app->run();
