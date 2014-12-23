<?php

CensusPluginRegister::instance()->register( "CensusPluginCheck404" );

class CensusPluginCheck404 extends CensusPlugin
{
	protected $id = "check404";	
	
	public function applyTo( $curl )
	{

		$base_url = preg_replace('#^(.*)/+$#', '\1', $curl->info['url']);
		
		$tmpcurl = $this->_check404_get("{$base_url}/CheckingThatYourSite404sProperly-".md5($base_url).".html",$base_url);
		if($tmpcurl['http_code']==404){
				return true;
			}else{
				return false;
			}
	}	
	
	private function _check404_get($url,$base){
		
		$s = curl_init();

		curl_setopt($s,CURLOPT_URL,$url);
		curl_setopt($s,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($s,CURLOPT_FOLLOWLOCATION,true);
		curl_setopt($s,CURLOPT_USERAGENT,'UK University Web Observatory 404 Checker (http://observatory.data.ac.uk)');
		curl_setopt($s,CURLOPT_REFERER,$base);

		curl_exec($s);
		
		$info = curl_getinfo($s);
		
		curl_close($s);
		return $info;
	}	
	
}
