<?php

class CensusPluginSoftwareWordCount extends CensusPluginRegexpCount
{
	protected $id = "softwareWordCount";	
	protected $regexp = "\bsoftware\b";
	protected $caseSensitive = false;
}
CensusPluginRegister::instance()->register( "CensusPluginSoftwareWordCount" );

