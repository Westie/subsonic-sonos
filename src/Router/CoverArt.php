<?php


namespace App\Router;

use \App\Factory\SubsonicClientFactory;
use \OUTRAGElib\Subsonic\Client as SubsonicClient;


class CoverArt
{
	/**
	 *	Subsonic API
	 */
	protected $subsonic = null;
	
	
	/**
	 *	Constuctor
	 */
	public function __construct()
	{
		$this->subsonic = SubsonicClientFactory::getClient();
	}
	
	
	/**
	 *	Handle our request
	 */
	public function handle($coverArtId)
	{
		$image = $this->subsonic->getCoverArt([ "id" => $coverArtId ]);
		
		$headers = $image->getHeaders();
		
		unset($headers["Server"]);
		unset($headers["Date"]);
		
		foreach($headers as $type => $values)
		{
			foreach($values as $value)
				header($type.": ".$value);
		}
		
		echo $image->getBody();
		exit;
	}
}