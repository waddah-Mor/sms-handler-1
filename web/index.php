<?php

use Silex\Application as App;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

require_once __DIR__.'/../vendor/autoload.php';

$app    = new App();
$client = new Sms\Gateway();

$app['debug'] = true;

/**
 * Retrieve all messages
 */
$app->get('/sms', function (Request $request) use ($app, $client) {

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

	return $app->json($messages);
});

/**
 * Send an sms
 */
$app->post('/sms', function (Request $request) use ($app, $client) {

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
