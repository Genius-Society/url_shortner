<?php

	header('Content-Type: text/html;charset=utf-8');
	
	$readini = parse_ini_file("../../../sql/conf.ini");
	$domain = $readini['url'];
	
	if(strpos(@$_SERVER['HTTP_REFERER'], $domain) === false)
	{
		exit("no permission");
	}
	
	header('Access-Control-Allow-Origin: ' . $domain);
	header('Access-Control-Allow-Methods:POST');
	header('Access-Control-Allow-Headers:x-requested-with,content-type');
	
	require_once('main.php');	
    $main = new Main($readini['user'], $readini['pass']);
	
	$getip = @$_GET['ip'];
	
	if($main->isIP($getip))
	{
		$b = @$_GET['b'];
		$bv = @$_GET['bv'];
		$os = @$_GET['os'];
		$osv = @$_GET['osv'];
		
		if(!$main->isValid($b, $bv, $os, $osv))
		{
			echo 'Invalid Parameter!';
			return;
		}
		
		echo $main->postMap($getip, $b, $bv, $os, $osv);
		echo $main->getMap();
	}
		
?>