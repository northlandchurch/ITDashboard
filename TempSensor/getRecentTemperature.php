<?php
require_once 'config.php';


$link = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);

if (!$link) 
{
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    exit;
}

$date = date('Y-m-d H:i', time());
$lastweek = date('Y-m-d H:i', strtotime('-2 hour'));
//echo "Now: " . $date . PHP_EOL;
//echo "Last Week: " . $lastweek . PHP_EOL;

$obj = array();
$obj['labeldate'] = $date;
$obj['values'] = array();
$sql = "SELECT deviceid, devicename FROM devices WHERE isactive = 1";
//$values = array();

if ($result = mysqli_query($link, $sql)) 
{
	if (mysqli_num_rows($result) > 0)
	{
		while ($row = mysqli_fetch_array($result))
		{
			$newDevice = new \stdClass();
//			echo $row['deviceid'] . " - " . $row['devicename'] . PHP_EOL;
			$devId = $row['deviceid'];
			$devName = $row['devicename'];
			$newDevice->{'devId'} = $devId;
			$newDevice->{'devName'} = $devName;
//			$newDevice->{'data'} = array();
			
			$sql1 = "SELECT createdat, temperature FROM activities WHERE deviceid = '$devId' ORDER BY 1 desc LIMIT 1";
			if ($result1 = mysqli_query($link, $sql1)) 
			{
				if (mysqli_num_rows($result1) > 0)
				{
					while ($row1 = mysqli_fetch_array($result1))
					{
//						echo $row1['temperature'] . " at " . $row1['createdat'] . PHP_EOL;
//						$newItem = new \stdClass();
						$newDevice->{'datetime'} = $row1['createdat'];
						$newDevice->{'temperature'} = $row1['temperature'];
						
//						array_push($newDevice->{'data'}, $newItem);
					}
					array_push($obj['values'], $newDevice);
					mysqli_free_result($result1);
				}
				else
				{
					echo "No records matching your query were found." . PHP_EOL;
					echo " => " . $sql1 . PHP_EOL;	
				}
			}
			else 
			{
				echo "ERROR: can not execute $sql1." . PHP_EOL;
				echo " => " . mysqli_error($link) . PHP_EOL;	
				exit;
			}
//			var_dump($obj);
//			echo PHP_EOL . PHP_EOL;
			
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

function calculateAverage($devId)
{
	global $link;
	
}

/*
$devId = 'RPi de:e1:c2';
//$cDate = '2020-05-20 14:22:32';
$temperature = '74.7366';
$sql = "INSERT INTO activities (deviceid, temperature) VALUES (?, ?)";
if ($stmt = $link->prepare($sql)) 
{
	$stmt->bind_param('ss', $devId, $temperature);
	$stmt->execute();
	
	$reqId = $stmt->insert_id;
	$stmt->close();

}
else
{
	$msg = "System Error: Could not prepare statement - " . $link->error;
	$_SESSION['error'] = $msg;
	die($msg);
}

*/

mysqli_close($link);



?>