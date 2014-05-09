<?php
class CensusPluginFacebookAccounts extends CensusPluginRegexpList
{
	protected $id = "facebookAccounts";	
	protected $caseSensitive = false;
	protected $regexp = "[='\"]https?:\/\/(www\.)?facebook\.com\/([a-z0-9\.]+)[?#'\"]";
	public function addMatch( $matches ) 
	{ 
		// don't match things ending in .php
		if( preg_match( "/\.php$/", $matches[2] ) ) { return false; }

		return $matches[2]; 
	}
}
CensusPluginRegister::instance()->register( "CensusPluginFacebookAccounts" );
