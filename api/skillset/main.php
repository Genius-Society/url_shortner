<?php

header('Content-Type: text/html;charset=utf-8');

if (strpos($_SERVER['SCRIPT_NAME'], 'index.php') === false)
{
	exit("no permission");
}

class Main
{
	private function transcoding($fileName)
	{
		$fn = iconv('GBK//ignore', 'UTF-8', $fileName);
		return str_replace('/', '\\', $fn);
	}

	private function readRAR($rarpath, &$files = array(0, 0, 0, 0, 0, 0, 0, 0))
	{
		if ($this->hasChinese($rarpath))
		{
			//echo 'RAR file contains Chinese characters.';
			return 0;
		}

		if (!file_exists($rarpath))
		{
			echo 'RAR not exist.';
			return 0;
		}
		else if (!preg_match('/\.(rar)$/i', $rarpath))
		{
			echo 'Unsupported zip type.';
			return 0;
		}

		$type = array('java', 'c|cpp|h', 'cs', 'vb', 'php', 'js', 'html|css', 'm');

		$archive = rar_open($rarpath);

		if ($archive === false)
		{
			echo 'Failed to open rar.';
			return 0;
		}

		$entries = $archive->getEntries();

		if ($entries === false)
		{
			echo 'Failed to get entries.';
			return 0;
		}

		foreach($entries as $e)
		{
			$fname = $e->getName();

			for ($i = 0; $i < count($type); $i++)
			{
				if (preg_match('/\.('.$type[$i] . ')$/i', $fname))
				{
					$files[$i] += $e->getUnpackedSize();
					break;
				}
			}
		}

		$archive->close();
		return $files;
	}

	private function readZIP($zipath, &$files = array(0, 0, 0, 0, 0, 0, 0, 0))
	{
		if (!file_exists($zipath))
		{
			echo 'Zip not exist.';
			return 0;
		}
		else if (!preg_match('/\.(zip)$/i', $zipath))
		{
			echo 'Unsupported zip type.';
			return 0;
		}

		$type = array('java', 'c|cpp|h', 'cs', 'vb', 'php', 'js', 'html|css', 'm');

		$za = new ZipArchive;
		$za->open($zipath);

		for ($i = 0; $i < $za->numFiles; $i++)
		{
			$stat = $za->statIndex($i, ZipArchive::FL_ENC_RAW);

			for ($j = 0; $j < count($type); $j++)
			{
				if (preg_match('/\.('.$type[$j] . ')$/i', $this->transcoding(basename($stat['name']))))
				{
					$files[$j] += $stat['size'];
					break;
				}
			}
		}

		$za->close();
		return $files;
	}

	private function hasChinese($str)
	{
		$pattern = '/[^\x00-\x80]/';
		return preg_match($pattern, $str);
	}

/*
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
*/

	private function getfiles($path, &$files = array(0, 0, 0, 0, 0, 0, 0, 0))
	{
		$type = array('java', 'c|cpp|h', 'cs', 'vb', 'php', 'js', 'html|css', 'm');

		if (!is_dir($path))
		{
			echo 'Path not exist.';
			return 0;
		}

		$handle = opendir($path);
		while (false !== ($file = readdir($handle)))
		{
			if ($file != '.' && $file != '..')
			{
				$path2 = $path . '/'.$file;
				if (is_dir($path2))
				{
					$this->getfiles($path2, $files);
				}
				else if (preg_match('/\.(zip)$/i', $file))
				{
					$files = $this->addArray($files, $this->readZIP($path2));
				}
				else if (preg_match('/\.(rar)$/i', $file))
				{
					$files = $this->addArray($files, $this->readRAR($path2));
				}
				else
				{
					for ($i = 0; $i < count($type); $i++)
					{
						if (preg_match('/\.('.$type[$i] . ')$/i', $path2))
						{
							$files[$i] += filesize($path2);
							break;
						}
					}

				}

			}
		}
		return $files;
	}

	private function calRate($x)
	{
		return (4.0 * $x + 1.0) / 6.0;
	}

	private function addArray($a, $b)
	{
		$c = array();

		for ($i = 0; $i < 8; $i++)
		{
			$c[$i] = $a[$i] + $b[$i];
		}

		return $c;
	}

	public function calJSON($directory)
	{
		$type = array('Java', 'C/C++', 'C#', 'VB', 'PHP', 'JavaScript', 'HTML & CSS', 'Matlab');
		$w_type = $this->getfiles($directory);
		$max = max($w_type);
		$w_json = array();

		for ($i = 0; $i < count($type); $i++)
		{
			$temp = array("language" => $type[$i], "frequency" => $this->calRate($w_type[$i] / $max));
			array_push($w_json, $temp);
		}

		return json_encode($w_json);
	}

}
?>