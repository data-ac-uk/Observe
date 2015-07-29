<?php
if(class_exists('ARC2') != true)
{
	require_once( __DIR__."/../lib/arc2/ARC2.php" ); 
	require_once( __DIR__."/../lib/Graphite/Graphite.php" ); 
}




CensusPluginRegister::instance()->register( "CensusPluginOPD" );

class CensusPluginOPD extends CensusPlugin
{
	protected $id = "opd";	
	
	public function applyTo( $curl )
	{
		$dom = new DOMDocument();
		$urls = array();
		@$dom->loadHTML( $curl->webpage );
		$xpath = new DOMXPath($dom);

		$links = $xpath->query("//link[@rel='openorg']");
		
		foreach( $links as $link_tag )
		{
			$url = $link_tag->getAttribute("href");
			break;
		}
		
		$url_info = parse_url($curl->info['url']) ;
		$base_url = "{$url_info['scheme']}://{$url_info['host']}";
		
		if(!isset($url)){
			$tmpcurl = $this->_opd_get("{$base_url}/.well-known/openorg",$base_url);
		
			if($tmpcurl['http_code']==200){
				$url = $tmpcurl['url'];
			}else{
				return false;
			}
		}
		
		
		if(strpos($url, 'http') === 0)
		{
			$r = $url;
		}
		elseif(strpos($url, '/') === 0)
		{
			$r = $base_url.$url;
		}
		else
		{
			$r = $base_url."/".$url;
		}
		

		require_once( __DIR__."/../lib/OPDLib/OrgProfileDocument.php" );
		
		try 
		{
			$opd = new OrgProfileDocument( $r );
		}
		catch( OPD_Discover_Exception $e )
		{
			return false;
		}
		catch( OPD_Load_Exception $e )
		{
			return false;
		}
		catch( OPD_Parse_Exception $e )
		{
			return false;
		}
		catch( Exception $e ) 
		{
			return false;
		}
		
		return $r; 
		
	}	
	
	private function _opd_get($url,$base){
		
		$s = curl_init();

		curl_setopt($s,CURLOPT_URL,$url);
		curl_setopt($s,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($s,CURLOPT_FOLLOWLOCATION,true);
		curl_setopt($s,CURLOPT_USERAGENT,'OPDFinder (http://opd.data.ac.uk/)');
		curl_setopt($s,CURLOPT_REFERER,$base);

		curl_exec($s);
		
		$info = curl_getinfo($s);

		curl_close($s);
		return $info;
	}	
	
}
