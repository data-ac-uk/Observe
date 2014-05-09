<?php

class CensusPluginManifest extends CensusPluginRegexp
{
	protected $id = "manifest";	
	protected $regexp = "<html [^>]*manifest=";
	protected $caseSensitive = false;
}
CensusPluginRegister::instance()->register( "CensusPluginManifest" );

