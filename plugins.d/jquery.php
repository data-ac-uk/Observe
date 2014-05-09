<?php

class CensusPluginJQuery extends CensusPluginRegexp
{
	protected $id = "jquery";	
	protected $regexp = "jquery[\.a-z0-9-_]*\.js";
	protected $caseSensitive = false;
}
CensusPluginRegister::instance()->register( "CensusPluginJQuery" );

