<?php
header('Content-Type: text/html;charset=utf-8');

if(strpos($_SERVER['SCRIPT_NAME'], 'index.php') === false)
{
	exit("no permission");
}

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

	public function getSong()
	{
		$this -> readSQL("map", "SELECT `id`, `title`, `author`, `duration` FROM `song`");
	}
	
}
?>