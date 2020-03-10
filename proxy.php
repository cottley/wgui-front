<?php
/*
  session_start();

  if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php');
    exit();
  }
  */
  
require_once('util.php');

use Proxy\Proxy;
use Proxy\Adapter\Guzzle\GuzzleAdapter;
use Proxy\Filter\RemoveEncodingFilter;
use Laminas\Diactoros\ServerRequestFactory;


//$request = [ServerRequestFactory::class, 'fromGlobals'];
// Create a PSR7 request based on the current browser request.
$request = ServerRequestFactory::fromGlobals();

// Create a guzzle client
$guzzle = new \GuzzleHttp\Client();

// Create the proxy instance
$proxy = new Proxy(new GuzzleAdapter($guzzle));

// Add a response filter that removes the encoding headers.
//$proxy->filter(new RemoveEncodingFilter());

// Forward the request and get the response.
/*
$response = $proxy
	->forward($request)
	->filter(function ($request, $response, $next) {
		// Manipulate the request object.
		$request = $request->withHeader('User-Agent', 'FishBot/1.0');

		// Call the next item in the middleware.
		$response = $next($request, $response);

		return $response;
	})
	->to('http://192.168.1.10:8080/');
*/

$response = $proxy->forward($request)->to('http://192.168.1.10:8080/');
	

/*
echo 'Test';

flush();
*/
// Output response to the browser.
(new Laminas\HttpHandlerRunner\Emitter\SapiEmitter())->emit($response);

/*

use
    Sabre\HTTP\Sapi,
    Sabre\HTTP\Client;

//header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
//header("Cache-Control: post-check=0, pre-check=0", false);
//header("Pragma: no-cache");

// The url we're proxying to.
$remoteUrl = 'http://192.168.1.10:8080/about';
//$remoteUrl = 'https://www.google.com/';

// The url we're proxying from. Please note that this must be a relative url,
// and basically acts as the base url.
//
// If youre $remoteUrl doesn't end with a slash, this one probably shouldn't
// either.
$myBaseUrl = '/';
// $myBaseUrl = '/~evert/sabre/http/examples/reverseproxy.php/';

$request = Sapi::getRequest();
$request->setBaseUrl($myBaseUrl);

$subRequest = clone $request;

// Removing the Host header.
$subRequest->removeHeader('Host');

// Rewriting the url.
$subRequest->setUrl($remoteUrl . $request->getPath());

$client = new Client();

// Sends the HTTP request to the server
$response = $client->send($subRequest);

// Sends the response back to the client that connected to the proxy.
Sapi::sendResponse($response);

/*
header("X-Forwarded-User: admin");
header('Location: http://192.168.1.10:8080/');
*/
?>