<?php
class CensusPluginPinterestAccounts extends CensusPluginRegexpList
{
	protected $id = "pinterestAccounts";	
	protected $caseSensitive = false;
	protected $regexp = "[='\"]https?:\/\/(www\.)?pinterest\.com\/?([^ \/>'\"\?]+)";
	public function addMatch( $matches ) 
	{ 
		return $matches[2]; 
	}
}
CensusPluginRegister::instance()->register( "CensusPluginPinterestAccounts" );
