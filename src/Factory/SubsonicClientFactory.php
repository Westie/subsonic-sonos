<?php


namespace App\Factory;

use \OUTRAGElib\Subsonic\Client as SubsonicClient;


class SubsonicClientFactory
{
	/**
	 *	Constructor
	 */
	public function getClient()
	{
		$client = new SubsonicClient("https://funkwhale.typefish.co.uk", "Westie", "6e37179ff8f52cb6d5be94f83e94d8");
		
		return $client;
	}
}