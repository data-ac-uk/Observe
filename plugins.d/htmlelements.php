<?php

class CensusPluginHTMLElements extends CensusPluginRegexpList
{
	protected $id = "htmlelements";	
	// svg is not really HTML5 but handy to get data on
	protected $regexp = "<(section|nav|article|aside|hgroup|header|footer|time|mark|canvas|svg|iframe|frame|frameset|object|video|embed|audio|command|datalist|details|device|meter|output|progress|summary|menu|menuitem|dialog|marquee|blink|font|keygen|picture|table)";
	protected $caseSensitive = false;
	public function addMatch( $matches ) { return strtolower($matches[1]); }
}
CensusPluginRegister::instance()->register( "CensusPluginHTMLElements" );

