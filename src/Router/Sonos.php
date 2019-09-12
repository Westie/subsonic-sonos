<?php


namespace App\Router;

use \App\Factory\SubsonicClientFactory;
use \OUTRAGElib\Subsonic\Client as SubsonicClient;


class Sonos
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
	 *	Perform a search
	 */
	public function search($args)
	{
		if($args->id === "artist")
			return [ "searchResult" => $this->searchArtists($args) ];
		elseif($args->id === "album")
			return [ "searchResult" => $this->searchAlbums($args) ];
		elseif($args->id === "track")
			return [ "searchResult" => $this->searchSongs($args) ];
		elseif($args->id === "playlist")
			return [ "searchResult" => $this->searchPlaylists($args) ];
		
		return null;
	}
	
	
	/**
	 *	Perform a search for artists
	 */
	private function searchArtists($args)
	{
		$response = [
			"index" => intval($args->index ?? 0),
			"count" => 0,
			"total" => 0,
			"mediaCollection" => [],
		];
		
		$options = [
			"query" => $args->term ?? null,
			"artistCount" => $args->count,
			"artistOffset" => $args->index * $args->count,
			"albumCount" => 0,
			"albumOffset" => 0,
			"songCount" => 0,
			"songOffset" => 0,
		];
		
		$searchResults = $this->subsonic->search3($options);
		
		foreach($searchResults["artist"] as $result)
			$response["mediaCollection"][] = $this->getArtistMetadata($result["id"]);
		
		$response["count"] = count($response["mediaCollection"]);
		$response["total"] = ($response["count"] == $args->count) ? $response["count"] + 1 : $response["count"];
		
		return $response;
	}
	
	
	/**
	 *	Perform a search for albums
	 */
	private function searchAlbums($args)
	{
		$response = [
			"index" => intval($args->index ?? 0),
			"count" => 0,
			"total" => 0,
			"mediaCollection" => [],
		];
		
		$options = [
			"query" => $args->term ?? null,
			"artistCount" => 0,
			"artistOffset" => 0,
			"albumCount" => $args->count,
			"albumOffset" => $args->index * $args->count,
			"songCount" => 0,
			"songOffset" => 0,
		];
		
		$searchResults = $this->subsonic->search3($options);
		
		foreach($searchResults["album"] as $result)
			$response["mediaCollection"][] = $this->getAlbumMetadata($result["id"]);
		
		$response["count"] = count($response["mediaCollection"]);
		$response["total"] = ($response["count"] == $args->count) ? $response["count"] + 1 : $response["count"];
		
		return $response;
	}
	
	
	/**
	 *	Perform a search for songs
	 */
	private function searchSongs($args)
	{
		$response = [
			"index" => intval($args->index ?? 0),
			"count" => 0,
			"total" => 0,
			"mediaCollection" => [],
		];
		
		$options = [
			"query" => $args->term ?? null,
			"artistCount" => 0,
			"artistOffset" => 0,
			"albumCount" => 0,
			"albumOffset" => 0,
			"songCount" => $args->count,
			"songOffset" => $args->index * $args->count,
		];
		
		$searchResults = $this->subsonic->search3($options);
		
		foreach($searchResults["song"] as $result)
			$response["mediaCollection"][] = $this->getSongMetadata($result["id"]);
		
		$response["count"] = count($response["mediaCollection"]);
		$response["total"] = ($response["count"] == $args->count) ? $response["count"] + 1 : $response["count"];
		
		return $response;
	}
	
	
	/**
	 *	Perform a search for playlists
	 */
	private function searchPlaylists($args)
	{
		$response = [
			"index" => intval($args->index ?? 0),
			"count" => 0,
			"total" => 0,
			"mediaCollection" => [],
		];
		
		$searchResults = $this->subsonic->getPlaylists();
		
		foreach($searchResults["playlist"] as $result)
			$response["mediaCollection"][] = $this->getPlaylistMetadata($result["id"]);
		
		$response["count"] = count($response["mediaCollection"]);
		$response["total"] = ($response["count"] == $args->count) ? $response["count"] + 1 : $response["count"];
		
		return $response;
	}
	
	
	/**
	 *	Get metadata
	 */
	public function getMetadata($args)
	{
		if($args->id === "root")
			return [ "getMetadataResult" => $this->getRootMetadataListing($args) ];
		elseif($args->id === "search")
			return [ "getMetadataResult" => $this->getSearchMetadataListing($args) ];
		
		if(strpos($args->id, ":") !== false)
		{
			list($type, $id) = explode(":", $args->id, 2);
			
			$types = [
				"artist" => "searchArtists",
				"album" => "searchAlbums",
				"track" => "searchSongs",
				"playlist" => "searchPlaylists",
			];
			
			if($type === "browse")
			{
				if(isset($types[$id]))
					return [ "getMetadataResult" => call_user_func([ $this, $types[$id] ], $args) ];
			}
			
			if($type === "artist")
				return [ "getMetadataResult" => $this->getArtistMetadataSubListing($args) ];
			elseif($type === "album")
				return [ "getMetadataResult" => $this->getAlbumMetadataSubListing($args) ];
			elseif($type === "playlist")
				return [ "getMetadataResult" => $this->getPlaylistMetadataSubListing($args) ];
		}

		return null;
	}
	
	
	/**
	 *	Get media metadata
	 */
	public function getMediaMetadata($args)
	{
		list($type, $id) = explode(":", $args->id, 2);
		
		$types = [
			"track" => "getSongMetadata",
		];
		
		if(isset($types[$type]))
			return [ "getMediaMetadataResult" => call_user_func([ $this, $types[$type] ], $id) ];
	}
	
	
	/**
	 *	Get root metadata listing
	 */
	private function getRootMetadataListing($args)
	{
		$response = [
			"index" => intval($args->index ?? 0),
			"count" => 0,
			"total" => 0,
			"mediaCollection" => [],
		];
		
		$types = [
			"artist" => "Artists",
			"album" => "Albums",
			"track" => "Tracks",
			"playlist" => "Playlists",
		];
		
		foreach($types as $type => $title)
		{
			$response["mediaCollection"][] = [
    			"id" => "browse:".$type,
    			"itemType" => "container",
    			"displayType" => "genericGrid",
    			"title" => $title,
    			"canPlay" => false,
    			"containsFavorite" => false,
			];
		}
		
		$response["count"] = count($response["mediaCollection"]);
		$response["total"] = ($response["count"] == $args->count) ? $response["count"] + 1 : $response["count"];
		
		return $response;
	}
	
	
	/**
	 *	Get root metadata listing
	 */
	private function getSearchMetadataListing($args)
	{
		$response = [
			"index" => intval($args->index ?? 0),
			"count" => 0,
			"total" => 0,
			"mediaCollection" => [],
		];
		
		$types = [
			"artist" => "Artists",
			"album" => "Albums",
			"track" => "Tracks",
			"playlist" => "Playlists",
		];
		
		foreach($types as $type => $title)
		{
			$response["mediaCollection"][] = [
    			"id" => $type,
    			"itemType" => "search",
    			"title" => $title,
			];
		}
		
		$response["count"] = count($response["mediaCollection"]);
		$response["total"] = ($response["count"] == $args->count) ? $response["count"] + 1 : $response["count"];
		
		return $response;
	}
	
	
	/**
	 *	Artist metadata sublisting
	 */
	private function getArtistMetadataSubListing($args)
	{
		list($type, $id) = explode(":", $args->id, 2);
		
		$result = $this->subsonic->getArtist([ "id" => $id ]);
		
		$response = [
			"index" => intval($args->index ?? 0),
			"count" => 0,
			"total" => 0,
			"mediaCollection" => [],
		];
		
		foreach($result["album"] as $album)
			$response["mediaCollection"][] = $this->getAlbumMetadata($album["id"]);
		
		$response["count"] = count($response["mediaCollection"]);
		$response["total"] = ($response["count"] == $args->count) ? $response["count"] + 1 : $response["count"];
		
		return $response;
	}
	
	
	/**
	 *	Artist metadata sublisting
	 */
	private function getAlbumMetadataSubListing($args)
	{
		list($type, $id) = explode(":", $args->id, 2);
		
		$result = $this->subsonic->getAlbum([ "id" => $id ]);
		
		$response = [
			"index" => intval($args->index ?? 0),
			"count" => 0,
			"total" => 0,
			"mediaCollection" => [],
		];
		
		foreach($result["song"] as $song)
			$response["mediaCollection"][] = $this->getSongMetadata($song["id"]);
		
		$response["count"] = count($response["mediaCollection"]);
		$response["total"] = ($response["count"] == $args->count) ? $response["count"] + 1 : $response["count"];
		
		return $response;
	}
	
	
	/**
	 *	Get stream URI
	 */
	public function getMediaUri($args)
	{
		list($type, $id) = explode(":", $args->id, 2);
		
		if($type === "track")
			return [ "getMediaURIResult" => $this->buildUrl([ "stream" => $id ]) ];
	}
	
	
	/**
	 *	Get artist metadata
	 */
	private function getArtistMetadata($artistId)
	{
		$result = $this->subsonic->getArtist([ "id" => $artistId ]);
		
		$response = [
        	"id" => "artist:".$result["id"],
			"title" => $result["name"],
        	"itemType" => "artist",
        	"displayType" => "searchResult",
        	"authrequired" => 1,
		];
		
		if(!empty($result["album"]))
		{
			$valid = array_filter($result["album"], function($result) {
				return !empty($result["coverArt"]);
			});
			
			if(!empty($valid))
			{
				$query = [
					"coverArt" => reset($valid)["coverArt"],
				];
				
				$response["albumArtURI"] = $this->buildUrl($query);
			}
		}
		
		return $response;
	}
	
	
	/**
	 *	Get album metadata
	 */
	private function getAlbumMetadata($albumId)
	{
		$result = $this->subsonic->getAlbum([ "id" => $albumId ]);
		
		$response = [
        	"id" => "album:".$result["id"],
			"title" => $result["name"],
        	"itemType" => "album",
        	"artist" => $result["artist"],
        	"artistId" => "artist:".$result["artistId"],
        	"displayType" => "searchResult",
        	"authrequired" => 1,
        	"canPlay" => 1,
		];
		
		if(!empty($result["coverArt"]))
		{
			$query = [
				"coverArt" => $result["coverArt"],
			];
			
			$response["albumArtURI"] = $this->buildUrl($query);
		}
		
		return $response;
	}
	
	
	/**
	 *	Get song metadata
	 */
	private function getSongMetadata($songId)
	{
		$result = $this->subsonic->getSong([ "id" => $songId ]);
		
		$response = [
			"id" => "track:".$result["id"],
			"itemType" => "track",
			"mimeType" => $result["contentType"],
			"title" => $result["title"],
			"displayType" => "searchResult",
			"trackMetadata" => [
				"album" => $result["album"],
				"albumId" => "album:".$result["albumId"],
				"artist" => $result["artist"],
				"artistId" => "artist:".$result["artistId"],
				"duration" => $result["duration"],
				"trackNumber" => $result["track"] * $result["discNumber"],
			],
			"authrequired" => 1,
		];
		
		if(!empty($result["coverArt"]))
		{
			$query = [
				"coverArt" => $result["coverArt"],
			];
			
			$response["albumArtURI"] = $this->buildUrl($query);
		}
		
		return $response;
	}
	
	
	/**
	 *	Get playlist metadata
	 */
	private function getPlaylistMetadata($playlistId)
	{
		$result = $this->subsonic->getPlaylist([ "id" => $playlistId ]);
		
		$response = [
        	"id" => "playlist:".$result["id"],
			"title" => $result["name"],
        	"itemType" => "playlist",
        	"displayType" => "searchResult",
        	"authrequired" => 1,
		];
		
		if(!empty($result["entry"]))
		{
			$valid = array_filter($result["entry"], function($result) {
				return !empty($result["coverArt"]);
			});
			
			if(!empty($valid))
			{
				$query = [
					"coverArt" => reset($valid)["coverArt"],
				];
				
				$response["albumArtURI"] = $this->buildUrl($query);
			}
		}
		
		return $response;
	}
	
	
	/**
	 *	Build url
	 */
	private function buildUrl($query)
	{
		$query = [
			"x-salt" => sha1("hi"),
		] + $query;
		
		return getenv("APPLICATION_ENDPOINT")."?".http_build_query($query);
	}
}