<?php
class CensusPluginLinkedinAccounts extends CensusPluginRegexpList
{
	protected $id = "linkedinAccounts";	
	protected $caseSensitive = false;
	protected $regexp = "[='\"]https?:\/\/(www\.|uk\.)?linkedin\.com\/([a-z0-9-]+\/[^ >'\"\?\/]+)\/?[\"']";

	public function addMatch( $matches ) 
	{ 
		return $matches[2]; 
	}
}
CensusPluginRegister::instance()->register( "CensusPluginLinkedinAccounts" );
