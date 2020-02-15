<?php
header('Content-Type: text/html;charset=utf-8');

if(strpos($_SERVER['SCRIPT_NAME'], 'index.php') === false)
{
	exit("no permission");
}

require_once 'vendor/autoload.php';
use GeoIp2\Reader;

class Main
{
	
	private $username;
	private $password;
	
	public function __construct($user = 'root', $pass = '')
	{
		$this->username = $user;
		$this->password = $pass;
	}
	
	private function conn($db)
	{
		$conn = new mysqli('localhost', $this->username, $this->password, $db, '3306');
        $conn->set_charset("utf8");
		
		if ($conn->connect_error) die("Failed to connect to the database: " . $conn->connect_error); 
		return $conn;
    }
 
	private function readSQL($db, $search)
	{

		$conn = $this -> conn($db); 
		$result = $conn -> query($search); 
		
		if (!$result) 
		{
			printf("Error: %s\n", mysqli_error($conn));
			exit();
		}
		else
		{
			$jarr = array();
			
			while ($rows = mysqli_fetch_assoc($result))
			{
				$count = count($rows);  
				for($i = 0 ; $i < $count ; $i++) unset($rows[$i]); 
				array_push($jarr, $rows);
			}
			$str = json_encode($jarr);
			print_r($str);	
		}
		
		mysqli_close($conn);	
	}
	
	private function writeSQL($db, $del, $sql)
	{
		$conn = $this -> conn($db);		
		$result = $conn -> query($del);
		if (!$result) 
		{
			printf("Error: %s\n", mysqli_error($conn));
			exit();
		}
		
		if (!mysqli_query($conn, $sql))
		{
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
		
	}
	
	private function cut_str($str, $sign, $number)
	{
		$array=explode($sign, $str);
		$length=count($array);
		
		if($number < 0)
		{
			$new_array=array_reverse($array);
			$abs_number=abs($number);
			if($abs_number>$length)
			{
				return 'error';
			}
			else
			{
				return $new_array[$abs_number - 1];
			}
		}
		else
		{
			if($number >= $length)
			{
				return 'error';
			}
			else
			{
				return $array[$number];
			}
		}
	}
	
	private function ParseOS($os)
	{
		switch($os)
		{
			case 0: return "iPhone/IOS";
			case 1: return "iPad/IOS";
			case 2: return "iPod/IOS";
			case 3: return "Android";
			case 4: return "Symbian";
			case 5: return "Windows";// Phone";
			case 6: return "Windows";//10/Server 2016";
			case 7: return "Windows";//8.1/Server 2012 R2";
			case 8: return "Windows";//8/Server 2012";
			case 9: return "Windows";//7/Server 2008 R2";
			case 10: return "Windows";//Vista/Server 2008";
			case 11: return "Windows";//64-Bit Edition/Server 2003/Server 2003 R2";
			case 12: return "Windows";//XP";
			case 13: return "Windows";//2000";
			case 14: return "MacOS";
			case 15: return "Linux";
			/*case 16: return "Unix";
			case 17: return "Blackberry";
			case 18: return "FXOS";
			case 19: return "MeeGo";
			case 20: return "TV";*/
			default: return "Unknown";
		}
	}
	
	private function ParseBrowser($b)
	{
		switch($b)
		{
			case 0: return "Opera";
			case 1: return "Firefox";
			case 2: return "Edge";
			/*case 3: return "QQ";
			case 4: return "WeChat";
			case 5: return "QQBrowser";
			case 6: return "UC";
			case 7: return "Alipay";
			case 8: return "QQMail";
			case 9: return "Baidu";
			case 10: return "Maxthon";
			case 11: return "Sogou";
			case 12: return "360";
			case 13: return "2345";
			case 14: return "LieBao";
			case 15: return "Adblock";*/
			case 16: return "Chrome";
			case 17: return "Safari";
			case 18: return "IE"; 
			/*case 19: return "Vivo";
			case 20: return "Zhihu";
			case 21: return "MI";
			case 22: return "163";
			case 23: return "Sina";
			case 24: return "Linkedin";
			case 25: return "Facebook";
			case 26: return "Instagram";
			case 27: return "Ctrip";
			case 28: return "189";
			case 29: return "139";
			case 30: return "YY";
			case 31: return "Douyu";
			case 32: return "Iqiyi";
			case 33: return "ImgoTV";
			case 34: return "Sohu";
			case 35: return "Taobao";
			case 36: return "Jumei";
			case 37: return "Suning";*/
			default: return "Unknown";
		}
	}
	
	private function ParseWin($osv)
	{
		$pver = floor(10.0 * $osv);
		switch($pver)
		{
			case 50: return "2000";
			case 51: return "XP";
			case 52: return "64-Bit Edition/Server 2003/Server 2003 R2";
			case 60: return "Vista/Server 2008";
			case 61: return "7/Server 2008 R2";
			case 62: return "8/Server 2012";
			case 63: return "8.1/Server 2012 R2";
			case 100: return "10/Server 2016";
			default: return "Unknown";
		}
	}
	
	public function isIP($ip)
	{
		return preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip);
	}
	
	public function isValid($b, $bv, $os, $osv)
	{
		return is_numeric($b) && is_numeric($os) && (preg_match('/^[.0-9]*$/', $bv) || ($bv == -1)) && (preg_match('/^[.0-9]*$/', $osv) || ($osv == -1));
	}
	
	private function transLate($word)
	{
		$keyfrom = "xujiangtao";
		$apikey = "1490852988";
		$url_youdao = 'http://fanyi.youdao.com/fanyiapi.do?keyfrom='.$keyfrom.'&key='.$apikey.'&type=data&doctype=json&version=1.1&q='.$word;
		$json = file_get_contents($url_youdao);
		$obj = json_decode($json);
		$errorCode = $obj->errorCode;

		if (isset($errorCode))
		{
			switch ($errorCode)
			{
			case 0:
				$trans = $obj->translation[0];
				break;

			case 20:
				$trans = 'Too long!';
				break;

			case 30:
				$trans = 'Invalid translation!';
				break;

			case 40:
				$trans = 'Unsupport language!';
				break;

			case 50:
				$trans = 'Invalid key!';
				break;

			default:
				$trans = 'Exception!';
				break;
			}
		}
		return trim($trans);
	}
		
	public function postMap($ip, $b, $bv, $os, $osv)
	{
				
		$reader = new Reader('vendor/GeoLite2-City.mmdb');
		$record = $reader->get($ip);
		
		if(empty($record))
		{
			$city = "Unknown";
			$cityzh = "未知";
			$lat = -90;
			$lon = 0;
			$iso = "others";
			$country = "Others";
			$countryzh = "其它";
		}
		else
		{
			if(array_key_exists('city', $record))
			{
				$city = $record["city"]["names"]["en"];
				$cityname = $record["city"]["names"];
			
				if(array_key_exists('zh-CN', $cityname))
				{
					$cityzh = $record["city"]["names"]["zh-CN"];
				}
				else
				{
					$cityzh = $this->transLate($city);
				}
			
			}
			else if(array_key_exists('subdivisions', $record))
			{
				$city = $record["subdivisions"][0]["names"]["en"];			
				$subvname = $record["subdivisions"][0]["names"];
				
				if(array_key_exists('zh-CN', $subvname))
				{
					$cityzh = $record["subdivisions"][0]["names"]["zh-CN"];
				}
				else
				{
					$cityzh = $this->transLate($city);
				}			
			}
			else
			{
				$city = "Unknown";//$this->cut_str($record["location"]["time_zone"], '/', -1);
				$cityzh = "未知";//$city;
			}
		
			$lat = $record["location"]["latitude"];
			$lon = $record["location"]["longitude"];
			$iso = strtolower($record["country"]["iso_code"]);
			$country = $record["country"]["names"]["en"];
			$countryzh = $record["country"]["names"]["zh-CN"];
			
			if($country === 'Hong Kong' || $country === 'Macao' || $country === 'Taiwan')
			{
				$city = $country;
				$cityzh = $countryzh;
				$country = 'China';
				$countryzh = '中国';
				if($iso === 'tw') $iso = 'cn';
			}
			
		}
		
		date_default_timezone_set('PRC');
		$dat = date('Y-m-d H:i:s', time());	
		
		$bn = $this->ParseBrowser($b);
		$osn = $this->ParseOS($os);
		if($os >= 6 && $os <= 13) $osv = $this->ParseWin($osv);
		
		$del = "DELETE FROM `visitor` WHERE IP = '{$ip}'";
		$sql = "INSERT INTO visitor (IP, Latitude, Longitude, datetime, country, city, Browser, BVer, OS, OSVer, BroName, OSName, countryzh, cityzh, isocode) VALUES ('{$ip}', '{$lat}', '{$lon}', '{$dat}', '{$country}', '{$city}', '{$b}', '{$bv}', '{$os}', '{$osv}', '{$bn}', '{$osn}', '{$countryzh}', '{$cityzh}', '{$iso}')";
		
		$this -> writeSQL('map', $del, $sql);		
	}
	
	public function getMap()
	{
		$this -> readSQL("map", "SELECT `IP`, `Latitude`, `Longitude`, `datetime`, `country`, `city`, `Browser`, `OS`, `countryzh`, `cityzh`, `isocode` FROM `visitor`");
	}
	
}
?>