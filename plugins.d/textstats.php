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
		$dom = new DOMDocument();
        @$dom->loadHTML( $curl->webpage );

        foreach(array("script", "style") as $tag) {
	        $tag_els = $dom->getElementsByTagName($tag);
	        foreach($tag_els as $tag_el) {
	        	$tag_el->nodeValue = "";
	        }
        }
        $text_data = $dom->textContent;

		$r = array();
		// functions in library we're not using:
		//text_length($curl->webpage);
		//dale_chall_difficult_word_count($curl->webpage)
		//spache_difficult_word_count($curl->webpage)
		//words_with_three_syllables($curl->webpage, $blnCountProperNouns = true)
		//percentage_words_with_three_syllables($curl->webpage, $blnCountProperNouns = true)
		$r["flesch_kincaid_reading_ease"] = $textstatistics->flesch_kincaid_reading_ease($text_data);
		$r["flesch_kincaid_grade"] = $textstatistics->flesch_kincaid_grade_level($text_data);
		$r["gunning_fog"] = $textstatistics->gunning_fog_score($text_data);
		$r["coleman_liau"] = $textstatistics->coleman_liau_index($text_data);
		$r["smog"] = $textstatistics->smog_index($text_data);
		$r["automated_readability"] = $textstatistics->automated_readability_index($text_data);
		$r["dale_chall_readability"] = $textstatistics->dale_chall_readability_score($text_data);
		$r["spache_readability"] = $textstatistics->spache_readability_score($text_data);

		$r["letters"] = $textstatistics->letter_count($text_data);
		$r["sentences"] = $textstatistics->sentence_count($text_data);
		$r["words"] = $textstatistics->word_count($text_data);
		$r["syllables"] = $textstatistics->total_syllables($text_data);

		$r["words_per_sentence"] = $textstatistics->average_words_per_sentence($text_data);
		$r["syllables_per_word"] = $textstatistics->average_syllables_per_word($text_data);

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
