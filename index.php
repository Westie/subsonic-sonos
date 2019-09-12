<?php

require "vendor/autoload.php";

$whoops = new \Whoops\Run;
$whoops->prependHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

# error_reporting(E_ALL);

putenv("APPLICATION_ENDPOINT=http://".$_SERVER["HTTP_HOST"]);

try
{
	if(array_key_exists("wsdl", $_REQUEST))
	{
		$feed = file_get_contents("./Sonos.wsdl");
		$feed = str_replace("http://moapi.sonos.com/Test/TestService.php", getenv("APPLICATION_ENDPOINT"), $feed);
		
		header("Content-Type: application/xml");
		
		echo $feed;
		exit;
	}
	elseif(array_key_exists("coverArt", $_REQUEST))
	{
		$router = new \App\Router\CoverArt();
		$router->handle($_REQUEST["coverArt"]);
	}
	elseif(array_key_exists("stream", $_REQUEST))
	{
		$router = new \App\Router\Stream();
		$router->handle($_REQUEST["stream"]);
	}
	else
	{
		$server = new SoapServer("./Sonos.wsdl", [
			"cache_wsdl" => WSDL_CACHE_NONE,
		]);
		
		$server->setClass(\App\Router\Sonos::class);
		$server->handle();
	}
}
catch(Exception $e)
{
	var_dump($e);
	exit;
}