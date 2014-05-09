#!/usr/bin/php
<?php


require_once( "Observe.php" );
require_once( "myCurl.php" );

$plugins = CensusPluginRegister::instance();
$plugins->loadDir( "plugins.d" );

$domains = array(
	"totl.net",
	"ecs.soton.ac.uk",
	"eprints.org",
	"microsoft.com" );

foreach( file( "unis.urls" ) as $line )
{
	$domains []= trim( $line );
}

//require_once( "../arc2/ARC2.php" );
//require_once( "../Graphite/Graphite.php" ); #only used for RDF output.

$ttl = 10;
foreach( $domains as $domain )
{
	$url = "http://$domain/";
		
	$curl = new mycurl( true, 10, 10 );
	$curl->createCurl( $url );

	$result = $plugins->applyTo( $curl );

	print "\n$domain:\n";
	print json_encode($result );
	print "\n";

/*	$graph = new Graphite();
	$graph->ns( "obsacuk", "http://observatory.data.ac.uk/vocab#" );
	$plugins->resultsToGraph( $graph, $result, $domain, date("c") );
	print $graph->serialize("NTriples");
	*/
}



