<?php
class CensusPluginYouTubeAccounts extends CensusPluginRegexpList
{
	protected $id = "youtubeAccounts";	
	protected $caseSensitive = false;
	protected $regexp = "[='\"]https?:\/\/(www\.)?youtube.com\/user\/([^ >'\"\/\?]+)";
	public function addMatch( $matches ) { return $matches[2]; }
}
CensusPluginRegister::instance()->register( "CensusPluginYouTubeAccounts" );

class CensusPluginYouTubeVideo extends CensusPluginRegexp
{
	protected $id = "youtubeVideo";	
	protected $caseSensitive = false;
	protected $regexp = "<iframe\s[^>]*https?:\/\/(www\.)?youtube.com\/";
}
CensusPluginRegister::instance()->register( "CensusPluginYouTubeVideo" );
