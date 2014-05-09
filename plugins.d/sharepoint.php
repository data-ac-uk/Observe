<?php

class CensusSharePoint extends CensusPluginRegexp
{
	protected $id = "sharePoint";	
	protected $regexp = "MicrosoftSharePointTeamServices";
	protected $caseSensitive = false;
	protected $onlyHeaders = true;
}
CensusPluginRegister::instance()->register( "CensusSharePoint" );
