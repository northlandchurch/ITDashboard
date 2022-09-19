<?php
require_once '../config.php';


$link = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);

if (!$link) 
{
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    exit;
}

// Return value
$obj = array();

$sql = "SELECT id, deviceid, devicename, isactive, lastmodified FROM devices";


if ($result = mysqli_query($link, $sql)) 
{
	if (mysqli_num_rows($result) > 0)
	{
		while ($row = mysqli_fetch_array($result))
		{
			$newDevice = new \stdClass();
			$newDevice->{'id'} = $row['id'];
			$newDevice->{'devId'} = $row['deviceid'];
			$newDevice->{'devName'} = $row['devicename'];
			$newDevice->{'active'} = $row['isactive'];
			$newDevice->{'dt'} = $row['lastmodified'];
			
//			var_dump($obj);
//			echo PHP_EOL . PHP_EOL;
			
			array_push($obj, $newDevice);
		}
		mysqli_free_result($result);
	}
	else
	{
		echo "No records matching your query were found." . PHP_EOL;
		echo " => " . $sql . PHP_EOL;	
	}
}
else 
{
	echo "ERROR: Could not able to execute $sql." . PHP_EOL;
	echo " => " . mysqli_error($link) . PHP_EOL;	
}

echo json_encode($obj);

mysqli_close($link);



?>