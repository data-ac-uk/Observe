<?php

CensusPluginRegister::instance()->register( "CensusPluginLinkStats" );
class CensusPluginLinkStats extends CensusPlugin
{

    protected $id = "linkstats";

    private function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

    public function applyTo( $curl )
    {
        $url_bits = parse_url($curl->info["url"]);
        $hostname = $url_bits["host"];
        $dom = new DOMDocument();
        $urls = array();
        $externals = array();
        $internals = array();
        $scripts = array();
        $tld = implode(".", array_slice(explode(".", $hostname),-3));


        @$dom->loadHTML( $curl->webpage );
        $xpath = new DOMXpath($dom);
        $link_nodes = $xpath->query("//a");
        foreach($link_nodes as $link_node) {
            $href = $link_node->getAttribute("href");
            $url_info = parse_url($href);


            if($url_info["scheme"] == "javascript") {
                $scripts[] = $href;
            }
            else {
                $data = array(
                    'link' => $href,
                    'text' => $link_node->nodeValue,
                    'title' => $link_node->getAttribute("title"),
                    );
                if(!empty($url_info["scheme"])) {
                    if($hostname == $url_info["host"] || $this->endsWith($url_info["host"], $tld))
                    {
                        $internals[] = $data;
                    }
                    else {
                        $externals[] = $data;
                    }
                }
                else {
                    $internals[] = $data;
                }
            }
        }
        $result = array("script" => $scripts, "external" => $externals, "internal" => $internals);
        return $result;
    }
}

