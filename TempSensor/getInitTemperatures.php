<?php
require_once 'config.php';


$link = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);

if (!$link) 
{
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    exit;
}

if (isset($_GET['option'])) {
	$option = $_GET['option'];
} else {
	$option = "INIT";
}
	
$format = 'Y-m-d H:i';
$h_format = 'Y-m-d H';

$date = date($h_format, time());
$minutes = date('i', time());
// Get 6 minutes interval 
$m_min = intval($minutes / 6) * 6; 	// 0, 6, 12, 18, 24, 30, 36, 42, 48, 54
$before90days = date($format, strtotime('-90 day'));

// Assign the base interval time in minutes (00, 06, 12, 18, 24, 30, 36, 42, 48, 54)
if ($option == "ADD")
{
	if ($m_min == 0)
	{
		$sdate = $date . ":00";
		$edate = $date . ":05";
	}
	else if ($m_min == 6)
	{
		$sdate = $date . ":06";
		$edate = $date . ":" . ($m_min+5);
	}
	else
	{
		$sdate = $date . ":" . $m_min;
		$edate = $date . ":" . ($m_min+5);
	}
	
}
else 
{
	$sdate = date($h_format, strtotime('-7 day'));

	if ($m_min == 0)
	{
		$sdate = $sdate . ":00";
		$edate = $date . ":05";
	}
	else if ($m_min == 6)
	{
		$sdate = $sdate . ":06";
		$edate = $date . ":" . ($m_min+5);
	}
	else
	{
		$sdate = $sdate . ":" . $m_min;
		$edate = $date . ":" . ($m_min+5);
	}
}

// Return value
$obj = array();

$sql = "SELECT id, deviceid, devicename FROM devices WHERE isactive = 1";
//$sql = "SELECT deviceid, devicename FROM devices WHERE deviceid in ('RPi e7:6d:22')";

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
			$newDevice->{'id'} = $row['id'];
			$newDevice->{'devId'} = $devId;
			$newDevice->{'devName'} = $devName;
			$newDevice->{'data'} = array();

			// Retrieve the highest temperature in the past 90 days
			$sql2 = "SELECT DATE_FORMAT(createdat, '%Y-%m-%d %H:%i') AS formatted, temperature FROM activities WHERE deviceid = '$devId' AND createdat >= '$before90days' AND createdat <= '$edate' ORDER BY temperature DESC, id DESC LIMIT 1";
			if ($result2 = mysqli_query($link, $sql2)) 
			{
				if (mysqli_num_rows($result2) > 0)
				{
					while ($row2 = mysqli_fetch_array($result2))
					{
						$newDevice->{'hightemp'} = $row2['temperature'];
						$newDevice->{'datetime'} = $row2['formatted'];
					}
					mysqli_free_result($result2);
				}
				else
				{
					$newDevice->{'hightemp'} = "N/A";
					$newDevice->{'datetime'} = "N/A";
				}
			}
			else 
			{
				echo "ERROR: can not execute $sql1." . PHP_EOL;
				echo " => " . mysqli_error($link) . PHP_EOL;	
				exit;
			}

			
			$sql1 = "SELECT DATE_FORMAT(createdat, '%Y-%m-%d %H:%i') AS formatted, DATE_FORMAT(createdat, '%Y-%m-%d %H') AS h_formatted, temperature FROM activities WHERE deviceid = '$devId' AND createdat >= '$sdate' AND createdat <= '$edate'";
			if ($result1 = mysqli_query($link, $sql1)) 
			{
				$counter = 0;

				if (mysqli_num_rows($result1) > 0)
				{
					// base datetime (with minutes at 00, 06
					$ssdate = date_create_from_format($format, $sdate);

					while ($row1 = mysqli_fetch_array($result1))
					{
						$counter++;

						// Check the data in DB matches to 6 minutes interval
						if ($ssdate->format($format) <= $row1['formatted'])
						{
							if ($ssdate->format($h_format) != $row1['h_formatted'])
							{
								// Loop until finding the matching date in DB and adding the lowest temp (50)
								while ($ssdate->format($format) <= $row1['formatted'])
								{
									$newItem = new \stdClass();
									$newItem->{'dt'} = $ssdate->format($format); 	
									$newItem->{'temp'} = 50;
									array_push($newDevice->{'data'}, $newItem);

									// Increase 6 minutes
									$ssdate->add(new DateInterval('PT6M'));
								}
								
								$newItem = new \stdClass();
								$newItem->{'dt'} = $ssdate->format($format); 	
								$newItem->{'temp'} = $row1['temperature'];
								array_push($newDevice->{'data'}, $newItem);
								
								// Increase 6 minutes
								$ssdate->add(new DateInterval('PT6M'));
								
							}
							else
							{
								$newItem = new \stdClass();
								$newItem->{'dt'} = $ssdate->format($format); 	// $row1['formatted'];
								$newItem->{'temp'} = $row1['temperature'];
								array_push($newDevice->{'data'}, $newItem);

								// Increase 6 minutes
								$ssdate->add(new DateInterval('PT6M'));
							}

						}
						
//						if ($counter > 340) exit;
					}
					array_push($obj, $newDevice);
					mysqli_free_result($result1);
//					exit;
				}
				else
				{
//					echo "No records matching your query were found." . PHP_EOL;
//					echo " => " . $sql1 . PHP_EOL;	
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


mysqli_close($link);



?>