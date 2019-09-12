<?php

require "vendor/autoload.php";

ini_set("xdebug.var_display_max_depth", 128);
ini_set("xdebug.var_display_max_children", 128);

$whoops = new \Whoops\Run;
$whoops->prependHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();


class DummySoapClient extends SoapClient
{
	public function __doRequest($a, $b, $c, $d, $e)
	{
		var_dump($a, $b, $c, $d, $e);
		exit;
	}
}

try
{
	$client = new SoapClient("http://subsonic-sonos.local/?wsdl", [
		"cache_wsdl" => WSDL_CACHE_NONE,
		"trace" => true,
		"exceptions" => true,
	]);
	
	$x = $client->getMediaURI([
		"id" => "track:4090",
	]);
	
	var_dump($x, "Success!");
	
	echo "<pre>".htmlentities($client->__getLastResponse())."</pre>";
	exit;
}
catch(SoapFault $e)
{
	echo $client->__getLastResponse();
	exit;
}