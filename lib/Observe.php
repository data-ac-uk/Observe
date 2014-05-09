<?php
	
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
	protected $onlyHeaders = false;

	
	public function applyTo( $curl )
	{
		$opts = "";
		if( $this->caseSensitive == false ) { $opts .= "i"; }
		
		if($this->onlyHeaders){
			$target = $curl->headers;
		}else{
			$target = $this->insideTags?$curl->webpage:$curl->text;
		}
		
		if( preg_match( "/".$this->regexp."/".$opts,  $target) )
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
	protected $onlyHeaders = false;
		
	public function applyTo( $curl )
	{
		$opts = "";
		if( $this->caseSensitive == false ) { $opts .= "i"; }
		
		if($this->onlyHeaders){
			$target = $curl->headers;
		}else{
			$target = $this->insideTags?$curl->webpage:$curl->text;
		}
		
		$parts = preg_split( "/".$this->regexp."/".$opts, $target);
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
	protected $onlyHeaders = false;
	
	public function applyTo( $curl )
	{
		$opts = "";
		if( $this->caseSensitive == false ) { $opts .= "i"; }

		if($this->onlyHeaders){
			$target = $curl->headers;
		}else{
			$target = $this->insideTags?$curl->webpage:$curl->text;
		}
		

		$this->r = array();
		preg_replace_callback( 
			"/".$this->regexp."/".$opts, 
			"self::processMatch",
			$target );
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



	