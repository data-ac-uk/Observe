<?php
class CensusPluginVimeoAccounts extends CensusPluginRegexpList
{
	protected $id = "vimeoAccounts";	
	protected $caseSensitive = false;
	protected $regexp = "[='\"]https?:\/\/(www\.)?vimeo\.com\/([^ >'\"\?\/]+)\/?[\"']";

	public function addMatch( $matches ) 
	{ 
		// all numeric is a video, not an account
		if( preg_match( "/^\d+$/", $matches[2] )) { return false; }
		return $matches[2]; 
	}
}
CensusPluginRegister::instance()->register( "CensusPluginVimeoAccounts" );
