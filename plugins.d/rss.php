<?php

CensusPluginRegister::instance()->register( "CensusPluginRSS" );


class CensusPluginRSS extends CensusPlugin
{
	protected $id = "rss";	
	
	public function applyTo( $curl )
	{
		$dom = new DOMDocument();
		$urls = array();
	
		@$dom->loadHTML( $curl->webpage );
		$xpath = new DOMXPath($dom);

		$links = $xpath->query("//link[@type='application/rss+xml']");
		foreach( $links as $link_tag )
		{
			$url = strtolower($link_tag->getAttribute("href"));
			$urls[$url] = 1;
		}

		$links = $xpath->query("//a[contains(
			translate(., 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'),
			'rss'
		)]");
		foreach( $links as $link_tag )
		{
			$url = strtolower($link_tag->getAttribute("href"));
			$urls[$url] = 1;
		}

		$r = array();
		$base_url = preg_replace('#^(.*)/+$#', '\1', $curl->info['url']);
		
		foreach(array_keys($urls) as $url)
		{
			if(strpos($url, 'http') === 0)
			{
				$r[] = $url;
			}
			elseif(strpos($url, '/') === 0)
			{
				$r[] = $base_url.$url;
			}
			else
			{
				$r[] = $base_url."/".$url;
			}

		}
		return $r; 
	}		
	function resultToGraph( $graph, $urlsesult, $observation_uri )
	{
		foreach( $urlsesult as $key=>$value )
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
