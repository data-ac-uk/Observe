<?php

class CensusPluginDrupal extends CensusPluginRegexp
{
	protected $id = "drupal";	
	protected $regexp = "\bdrupal\.js\b";
	protected $caseSensitive = false;
}
CensusPluginRegister::instance()->register( "CensusPluginDrupal" );

