<?php
class CensusPluginFlickrAccounts extends CensusPluginRegexpList
{
	protected $id = "flickrAccounts";	
	protected $caseSensitive = false;
	protected $regexp = "[='\"]https?:\/\/(www\.)?flickr\.com\/?(photos\/)?([^ \/>'\"\?]+)\/?([\"'])";

	public function addMatch( $matches ) 
	{ 
		return $matches[3]; 
	}
}
CensusPluginRegister::instance()->register( "CensusPluginFlickrAccounts" );

class CensusPluginFlickrGroups extends CensusPluginRegexpList
{
	protected $id = "flickrGroups";	
	protected $caseSensitive = false;
	protected $regexp = "[='\"]https?:\/\/(www\.)?flickr\.com\/groups\/([^ '\"\?\/]+)\/?([\"'])";

	public function addMatch( $matches ) 
	{ 
		return $matches[2]; 
	}
}
CensusPluginRegister::instance()->register( "CensusPluginFlickrGroups" );
