<?php

CensusPluginRegister::instance()->register( "CensusPluginTextStats" );

$base_dir = dirname(__DIR__);
set_include_path(get_include_path() . PATH_SEPARATOR . "$base_dir/lib/Text-Statistics");
require_once( "TextStatistics.php" );
global $textstatistics;
$textstatistics = new TextStatistics;

class CensusPluginTextStats extends CensusPlugin
{
	protected $id = "textStats";	
	
	public function applyTo( $curl )
	{
		global $textstatistics;

		$r = array();
		// functions in library we're not using:
		//text_length($curl->webpage);
		//dale_chall_difficult_word_count($curl->webpage)
		//spache_difficult_word_count($curl->webpage)
		//words_with_three_syllables($curl->webpage, $blnCountProperNouns = true)
		//percentage_words_with_three_syllables($curl->webpage, $blnCountProperNouns = true)
		$r["flesch_kincaid_reading_ease"] = $textstatistics->flesch_kincaid_reading_ease($curl->webpage);
		$r["flesch_kincaid_grade"] = $textstatistics->flesch_kincaid_grade_level($curl->webpage);
		$r["gunning_fog"] = $textstatistics->gunning_fog_score($curl->webpage);
		$r["coleman_liau"] = $textstatistics->coleman_liau_index($curl->webpage);
		$r["smog"] = $textstatistics->smog_index($curl->webpage);
		$r["automated_readability"] = $textstatistics->automated_readability_index($curl->webpage);
		$r["dale_chall_readability"] = $textstatistics->dale_chall_readability_score($curl->webpage);
		$r["spache_readability"] = $textstatistics->spache_readability_score($curl->webpage);
	
		$r["letters"] = $textstatistics->letter_count($curl->webpage);
		$r["sentences"] = $textstatistics->sentence_count($curl->webpage);
		$r["words"] = $textstatistics->word_count($curl->webpage);
		$r["syllables"] = $textstatistics->total_syllables($curl->webpage);
		
		$r["words_per_sentence"] = $textstatistics->average_words_per_sentence($curl->webpage);
		$r["syllables_per_word"] = $textstatistics->average_syllables_per_word($curl->webpage);

		foreach( $r as $k=>&$v )
		{
			$v = round( $v*100 )/100;
		}
		return $r;
	}		
	function resultToGraph( $graph, $result, $observation_uri )
	{
		foreach( $result as $key=>$value )
		{
			$datatype = "xsd:float";
			if( $key=="letters" ) { $datatype = "xsd:int"; }
			if( $key=="sentences" ) { $datatype = "xsd:int"; }
			if( $key=="words" ) { $datatype = "xsd:int"; }
			if( $key=="syllables" ) { $datatype = "xsd:int"; }
			$graph->t( $observation_uri, "obsacuk:".$key, $value, "xsd:float" );
		}
	}
}
