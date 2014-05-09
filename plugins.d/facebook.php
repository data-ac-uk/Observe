<?php
class CensusPluginFacebookAccounts extends CensusPluginRegexpList
{
	protected $id = "facebookAccounts";	
	protected $caseSensitive = false;
	protected $regexp = "[='\"]https?:\/\/(www\.)?facebook.com\/([^(plugins\/) >'\"]+)";
	public function addMatch( $matches ) { return $matches[2]; }
}
CensusPluginRegister::instance()->register( "CensusPluginFacebookAccounts" );
