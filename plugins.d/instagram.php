<?php
class CensusPluginInstagramAccounts extends CensusPluginRegexpList
{
	protected $id = "instagramAccounts";	
	protected $caseSensitive = false;
	protected $regexp = "[='\"]https?:\/\/(www\.)?instagram\.com\/?([^ \/>'\"\?]+)";

	public function addMatch( $matches ) 
	{ 
		return $matches[2]; 
	}
}
CensusPluginRegister::instance()->register( "CensusPluginInstagramAccounts" );
