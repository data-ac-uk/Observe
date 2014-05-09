<?php

class CensusPluginHTML5Microdata extends CensusPluginRegexp
{
	protected $id = "html5microdata";	
	protected $regexp = "<[^<]+itemtype=";
	protected $caseSensitive = false;
}
CensusPluginRegister::instance()->register( "CensusPluginHTML5Microdata" );

