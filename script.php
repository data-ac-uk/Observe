#!/usr/bin/php
<?php

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


require_once( "arc/ARC2.php" );
require_once( "Graphite/Graphite.php" ); #only used for RDF output.

$ttl = 10;
foreach( $domains as $domain )
{
	$url = "http://$domain/";
		
	$curl = new mycurl( true, 10, 10 );
	$curl->createCurl( $url );

	$result = $plugins->applyTo( $curl );

#	print "\n$domain:\n";
#	print json_encode($result );
#	print "\n";

	$graph = new Graphite();
	$graph->ns( "obsacuk", "http://observatory.data.ac.uk/vocab#" );
	$plugins->resultsToGraph( $graph, $result, $domain, date("c") );
	print $graph->serialize("NTriples");
}



####################

class CensusPluginRegister
{
	private static $instance;

	public static function instance()
	{
		if( !isset( self::$instance ) )
		{
			self::$instance = new CensusPluginRegister();
		}
		return self::$instance;
	}	

	public function loadDir( $dir )
	{
		$dh = opendir( $dir );
		while( $file = readdir( $dh ) )
		{
			if( preg_match( "/^[^\.].*\.php$/", $file ) )
			{
				include_once( "$dir/$file" );
			}
		}
		closedir( $dh );
	}
			

	private $plugins = array();
	public function register( $class )
	{
		$plugin = new $class();
		if( ! is_a( $plugin, "CensusPlugin" ) )
		{
			print "$class is not a CensusPlugin. Can't register it.\n";
			return;
		}

		if( $plugin->id() == null )
		{
			print "$class attempted to register without a valid ID. Skipping.\n";
			return;
		}

		if( !preg_match( "/^[a-z][a-zA-Z0-9]*$/", $plugin->id()  ) )
		{
			print "$class attempted to register as '".$plugin->id() ."', but please use pascal style IDs. eg. fooBar2. Skipping.\n";
			return;
		}

		if( isset( $this->plugins[ $plugin->id() ] )  )
		{
			print "$class attempted to register as '".$plugin->id() ."' but another plugin already registered as that. Skipping.\n";
			return;
		}
		$this->plugins[ $plugin->id() ] = $plugin;

	}

	public function applyTo( $curl )
	{
		$result = array();
		foreach( $this->plugins as $plugin )
		{
			$result[ $plugin->id() ] = $plugin->applyTo( $curl );
		}
		return $result;
	}

	public function resultsToGraph( $graph, $result, $domain, $datestamp )
	{
		$t = array();
		$observation_uri = "http://observatory.data.ac.uk/observation/$domain/$datestamp";
		$domain_uri = "http://observatory.data.ac.uk/domain/$domain";
		$graph->resource( $observation_uri )
			->add( "rdf:type", "obsacuk:Observation" )
			->add( "dct:date", $datestamp, "xsd:datetime" )
			->add( "obsacuk:domain", $domain_uri );
		$graph->resource( $domain_uri )
			->add( "rdf:type", "obsacuk:Domain" )
			->add( "rdfs:label", $domain );
		foreach( $this->plugins as $plugin )
		{
			$result []= $plugin->resultToGraph( $graph, $result[$plugin->id()], $observation_uri );
		}
		return $t;
	}
}

abstract class CensusPlugin 
{
	protected $id = null;
	protected $datatype = "xsd:string";
	function id() { return $this->id; }
	abstract function applyTo( $curl );

	function resultToGraph( $graph, $result, $observation_uri )
	{
		$graph->t( $observation_uri, "obsacuk:".$this->id(), $result, $datatype );
	}
}

#######

abstract class CensusPluginRegexp extends CensusPlugin
{
	protected $regexp = null;
	protected $caseSensitive = true;
	protected $insideTags = true;
	protected $datatype = "xsd:boolean";
	public function applyTo( $curl )
	{
		$opts = "";
		if( $this->caseSensitive == false ) { $opts .= "i"; }
		if( preg_match( "/".$this->regexp."/".$opts, $this->insideTags?$curl->webpage:$curl->text ) )
		{
			return true;
		}
		else
		{
			return false;
		}
	}		
	function resultToGraph( $graph, $result, $observation_uri )
	{
		$graph->t( $observation_uri, "obsacuk:".$this->id(), $result?"true":"false", $this->datatype );
	}
}

abstract class CensusPluginRegexpCount extends CensusPlugin
{
	protected $regexp = null;
	protected $caseSensitive = true;
	protected $insideTags = true;
	protected $datatype = "xsd:integer";
	public function applyTo( $curl )
	{
		$opts = "";
		if( $this->caseSensitive == false ) { $opts .= "i"; }
		
		$parts = preg_split( "/".$this->regexp."/".$opts, $this->insideTags?$curl->webpage:$curl->text );
		return sizeof( $parts )-1;
	}		
	function resultToGraph( $graph, $result, $observation_uri )
	{
		$graph->t( $observation_uri, "obsacuk:".$this->id(), $result, $this->datatype );
	}
}

##############################

abstract class CensusPluginRegexpList extends CensusPlugin
{
	protected $regexp = null;
	protected $caseSensitive = true;
	protected $insideTags = true;
	private $r; # temporary variable to store results of applyTo
	public function applyTo( $curl )
	{
		$opts = "";
		if( $this->caseSensitive == false ) { $opts .= "i"; }

		$this->r = array();
		preg_replace_callback( 
			"/".$this->regexp."/".$opts, 
			"self::processMatch",
			$this->insideTags?$curl->webpage:$curl->text );
		ksort( $this->r );
		return array_keys( $this->r );
	}		
	# takes the $matches array produced by preg_replace and returns
	# false or an item to add to the list of matches. Defaults to 
	# $matches[0] but we might want to do $matches[3].$matches[4] or 
	# some such if there's variations
	public function addMatch( $matches )
	{
		return $matches[0];
	}

	private function processMatch( $matches )
	{
		$toAdd = $this->addMatch( $matches );
		if( $toAdd !== false )
		{
			$this->r[$toAdd] = true;
		}

		# don't actually replace the text!!!
		return $matches[0]; 
	}
	
	function resultToGraph( $graph, $result, $observation_uri )
	{
		foreach( $result as $value )
		{
			$graph->t( $observation_uri, "obsacuk:".$this->id(), $value, $this->datatype );
		}
	}
}


######################


class mycurl {
     protected $_useragent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1';
     protected $_url;
     protected $_followlocation;
     protected $_timeout;
     protected $_maxRedirects;
     protected $_cookieFileLocation = './cookie.txt';
     protected $_post;
     protected $_postFields;
     protected $_referer ="http://www.google.com";

     protected $_session;
     public $webpage;
     public $text;
     protected $_responseHeaders;
     protected $_includeHeader;
     protected $_noBody;
     protected $_status;
     protected $_info;
     protected $_binaryTransfer;
     public    $authentication = 0;
     public    $auth_name      = '';
     public    $auth_pass      = '';

     public function useAuth($use){
       $this->authentication = 0;
       if($use == true) $this->authentication = 1;
     }

     public function setName($name){
       $this->auth_name = $name;
     }
     public function setPass($pass){
       $this->auth_pass = $pass;
     }

     public function __construct($url,$followlocation = true,$timeOut = 30,$maxRedirecs = 4,$binaryTransfer = false,$includeHeader = false,$noBody = false)
     {
         $this->_url = $url;
         $this->_followlocation = $followlocation;
         $this->_timeout = $timeOut;
         $this->_maxRedirects = $maxRedirecs;
         $this->_noBody = $noBody;
         $this->_includeHeader = $includeHeader;
         $this->_binaryTransfer = $binaryTransfer;

         $this->_cookieFileLocation = dirname(__FILE__).'/cookie.txt';

     }

     public function setReferer($referer){
       $this->_referer = $referer;
     }

     public function setCookiFileLocation($path)
     {
         $this->_cookieFileLocation = $path;
     }

     public function setPost ($postFields)
     {
        $this->_post = true;
        $this->_postFields = $postFields;
     }

     public function setUserAgent($userAgent)
     {
         $this->_useragent = $userAgent;
     }

     public function createCurl($url = 'nul')
     {
        if($url != 'nul'){
          $this->_url = $url;
        }

         $s = curl_init();

         curl_setopt($s,CURLOPT_URL,$this->_url);
         curl_setopt($s,CURLOPT_HTTPHEADER,array('Expect:'));
         curl_setopt($s,CURLOPT_TIMEOUT,$this->_timeout);
         curl_setopt($s,CURLOPT_MAXREDIRS,$this->_maxRedirects);
         curl_setopt($s,CURLOPT_RETURNTRANSFER,true);
         curl_setopt($s,CURLOPT_FOLLOWLOCATION,$this->_followlocation);
         curl_setopt($s,CURLOPT_COOKIEJAR,$this->_cookieFileLocation);
         curl_setopt($s,CURLOPT_COOKIEFILE,$this->_cookieFileLocation);

         if($this->authentication == 1){
           curl_setopt($s, CURLOPT_USERPWD, $this->auth_name.':'.$this->auth_pass);
         }
         if($this->_post)
         {
             curl_setopt($s,CURLOPT_POST,true);
             curl_setopt($s,CURLOPT_POSTFIELDS,$this->_postFields);

         }

         if($this->_includeHeader)
         {
               curl_setopt($s,CURLOPT_HEADER,true);
         }

         if($this->_noBody)
         {
             curl_setopt($s,CURLOPT_NOBODY,true);
         }
         /*
         if($this->_binary)
         {
             curl_setopt($s,CURLOPT_BINARYTRANSFER,true);
         }
         */
         curl_setopt($s,CURLOPT_USERAGENT,$this->_useragent);
         curl_setopt($s,CURLOPT_REFERER,$this->_referer);

         $this->webpage = curl_exec($s);
         $this->_status = curl_getinfo($s,CURLINFO_HTTP_CODE);
         $this->_info = curl_getinfo($s);

         $this->text = strip_tags( $this->webpage );
         curl_close($s);

     }

   public function getHttpHeaders()
   {
       return $this->_info;
   }
   public function getHttpStatus()
   {
       return $this->_status;
   }

}

