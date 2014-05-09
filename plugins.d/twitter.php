<?php
class CensusPluginTwitterAccounts extends CensusPluginRegexpList
{
	protected $id = "twitterAccounts";	
	protected $caseSensitive = false;
	protected $regexp = "[='\"]https?:\/\/(www\.)?twitter.com\/([^ >'\"]+)";
	public function addMatch( $matches ) { return $matches[2]; }
}
CensusPluginRegister::instance()->register( "CensusPluginTwitterAccounts" );
