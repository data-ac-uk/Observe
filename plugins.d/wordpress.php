<?php

class CensusPluginWordPress extends CensusPluginRegexp
{
	protected $id = "wordpress";	
	protected $regexp = "<meta[^>]+generator[^>]wordpress";
	protected $caseSensitive = false;
}
CensusPluginRegister::instance()->register( "CensusPluginWordPress" );

