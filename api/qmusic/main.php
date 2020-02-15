<?php
header('Content-Type: text/html;charset=utf-8');

if(strpos($_SERVER['SCRIPT_NAME'], 'index.php') === false)
{
	exit("no permission");
}

class Main
{
	private function httpRequest($sUrl, $aHeader, $aData)
	{
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch, CURLOPT_URL, $sUrl);
    	curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
    	curl_setopt($ch, CURLOPT_POST, true);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($aData));
    	$sResult = curl_exec($ch);
    	if($sError=curl_error($ch)) die($sError); 
    	curl_close($ch);
	
    	return $sResult;
	}
	
	private function Trims($str)
	{
		return str_replace("\"", "", $str);
	}
	
	public function codeMusic($playlid)
	{
		$sUrl = 'https://c.y.qq.com/qzone/fcg-bin/fcg_ucc_getcdinfo_byids_cp.fcg';
		
		$aData = array(
    		'type' => '1',
    		'json' => '1',
			'utf8' => '1', 
			'onlysong' => '0',
			'disstid' => $playlid,
			'format' => 'json',
			'g_tk' => '5381',
			'loginUin' => '0',
			'hostUin' => '0',
			'inCharset' => 'utf8',
			'outCharset' => 'utf-8',
			'notice' => '0',
			'platform' => 'yqq',
			'needNewCode' => '0'
		);
		
		$aHeader = array(
			'referer: https://c.y.qq.com/',
			'host: c.y.qq.com'
		);
		
		$sResult = $this->httpRequest($sUrl, $aHeader, $aData);
		$record = json_decode($sResult, true);
		$jarr = array();
		$songlist = $record["cdlist"][0]["songlist"];
		$top = min(4, json_encode($record["cdlist"][0]["songnum"]));
		
		for($i = 0 ; $i < $top ; $i++)
		{
			$si = $songlist[$i];
			$songid = $this->Trims(json_encode($si["songid"]));
			$songname = $this->Trims(json_encode($si["songname"]));
			$artist = $this->Trims(json_encode($si["singer"][0]["name"]));
			$rows = array("songid" => $songid, "songname" => $songname, "artist" => $artist);
			array_push($jarr, $rows);
		}
		
		return stripslashes(json_encode($jarr));
	}

}
?>