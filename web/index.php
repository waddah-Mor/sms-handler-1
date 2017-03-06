<?php


use Silex\Application as App;
use Silex\Provider\SecurityServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;

require_once __DIR__.'/../vendor/autoload.php';

$app    = new App();
$client = new Sms\Gateway();
//$user = new User('admin', '123', ['ROLE_ADMIN']);

if (in_array(getenv('DEV_ENVIRONEMENT'), ['1', 'true'])) {
	$app['debug'] = true;
}

//access the environment var 'RESTRICT_IPS' which contain the restricted ips
$ips = getenv('RESTRICT_IPS');

//Check if restricted ips are set
if ($ips) {
	
	//filter ips into array
    getIpsArray($ips);

	try{
		if (!in_array($_SERVER['REMOTE_ADDR'], $app['ips'])){
			throw new Exception(
					"Ip address do not match"
				);
		}
	}catch (Exception $e) {
		echo 'error: ',  $e->getMessage();
		exit();
	}
}


//Password Encoder
$salt = null; //is Null because it is ignored in the BCryptPasswordEncoder class (not used)

$encoder = new BCryptPasswordEncoder(5);

$password = $encoder->encodePassword('123', $salt);

//Password Firewall 
$app->register(new SecurityServiceProvider(), array(
    'security.firewalls' => array(
	    'admin' => array(
	        'pattern' => '^/sms',
        	//'pattern' => new RequestMatcher('^/sms', null, null, '192.168.16.33'),
	        'http' => true,
	        'users' => array(
	            'admin' => array('ROLE_ADMIN', $password),
	        ),
	    ),
	)
));


//filter restrict ips into array
function getIpsArray($ips){

	global $app;
    
    $ips = explode(',', trim($ips));
    
    foreach ($ips as $ip) {
        
        $allowedIps []= $ip;
    }

    $app['ips'] = $allowedIps;
}

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
 * Retrieve all failed messages
 */
$app->get('/sms/failed', function (Request $request) use ($app, $client) {

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

	return $app->json($message);
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
