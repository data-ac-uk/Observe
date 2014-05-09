<?php
class CensusPluginTwitterAccounts extends CensusPluginRegexpList
{
	protected $id = "twitterAccounts";	
	protected $caseSensitive = false;
	protected $regexp = "[='\"]https?:\/\/(www\.)?twitter.com\/(#!\/)?([^ \/>'\"\?]+)";
	public function addMatch( $matches ) 
	{ 
		// no easy way to spot twitter's utility URLs
		if( $matches[3]=="search" ) { return false; }
		if( $matches[3]=="intent" ) { return false; }
		if( $matches[3]=="share" ) { return false; }
		if( $matches[3]=="statuses" ) { return false; }
		if( $matches[3]=="home" ) { return false; }
		return $matches[3]; 
	}
}
CensusPluginRegister::instance()->register( "CensusPluginTwitterAccounts" );
