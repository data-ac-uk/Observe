<?php

class CensusPluginGoogleAnalytics extends CensusPluginRegexp
{
	protected $id = "googleAnalytics";	
	protected $regexp = "google-analytics\.com\/ga\.js";
	protected $caseSensitive = false;
}
CensusPluginRegister::instance()->register( "CensusPluginGoogleAnalytics" );

