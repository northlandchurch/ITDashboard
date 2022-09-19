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
	$option = "TEST";
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
	$date = date($h_format, strtotime('-6 minute'));
	$minutes = date('i', strtotime('-6 minute'));
	// Get 6 minutes interval 
	$m_min = intval($minutes / 6) * 6; 	// 0, 6, 12, 18, 24, 30, 36, 42, 48, 54
	if ($m_min == 0)
	{
		$sdate = $date . ":00";
		$edate = $date . ":05:59";
	}
	else if ($m_min == 6)
	{
		$sdate = $date . ":06";
		$edate = $date . ":" . ($m_min+5) . ":59";
	}
	else
	{
		$sdate = $date . ":" . $m_min;
		$edate = $date . ":" . ($m_min+5) . ":59";
	}
}
else if ($option == "INIT")
{
	$sdate = date($h_format, strtotime('-7 day'));

	if ($m_min == 0)
	{
		$sdate = $sdate . ":00";
		$edate = $date . ":05:59";
	}
	else if ($m_min == 6)
	{
		$sdate = $sdate . ":06";
		$edate = $date . ":" . ($m_min+5) . ":59";
	}
	else
	{
		$sdate = $sdate . ":" . $m_min;
		$edate = $date . ":" . ($m_min+5) . ":59";
	}
} else
{
	$sdate = date($h_format, strtotime('-2 day'));

	if ($m_min == 0)
	{
		$sdate = $sdate . ":00";
		$edate = $date . ":05:59";
	}
	else if ($m_min == 6)
	{
		$sdate = $sdate . ":06";
		$edate = $date . ":" . ($m_min+5) . ":59";
	}
	else
	{
		$sdate = $sdate . ":" . $m_min;
		$edate = $date . ":" . ($m_min+5) . ":59";
	}
}

// Return value
$obj = array();

$sql = "SELECT id, deviceid, devicename FROM devices WHERE isactive = 1";
//$sql = "SELECT deviceid, devicename FROM devices WHERE deviceid in ('RPi e7:6d:22')";

// Retrieve actvie devices
if ($result = mysqli_query($link, $sql)) 
{
	if (mysqli_num_rows($result) > 0)
	{
		while ($row = mysqli_fetch_array($result))
		{
			$newDevice = new \stdClass();

//			echo "Starting... " . $row['deviceid'] . " - " . $row['devicename'] . PHP_EOL;
	
			$devId = $row['deviceid'];
			$devName = $row['devicename'];
			$newDevice->{'id'} = $row['id'];
			$newDevice->{'devId'} = $devId;
			$newDevice->{'devName'} = $devName;
			$newDevice->{'data'} = array();

			// Retrieve the highest temperature for each device in the past 90 days
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
//			echo "High: " . $newDevice->{'hightemp'} . " at " . $newDevice->{'datetime'} . PHP_EOL;


			// Retrieve the temperatures between $sdate and $edate
			$sql1 = "SELECT DATE_FORMAT(createdat, '%Y-%m-%d %H:%i') AS formatted, DATE_FORMAT(createdat, '%Y-%m-%d %H') AS h_formatted, temperature FROM activities WHERE deviceid = '$devId' AND createdat >= '$sdate' AND createdat <= '$edate' ORDER BY createdat";
			if ($result1 = mysqli_query($link, $sql1)) 
			{
				if (mysqli_num_rows($result1) > 0)
				{
					// base datetime (with minutes at 00, 06)
					$ssdate = date_create_from_format($format, $sdate);
					$ssdate->add(new DateInterval('PT6M'));
					$hightemp = null;
					
					while ($row1 = mysqli_fetch_array($result1))
					{
						if ($option == "ADD")
						{
							if (is_null($hightemp))
								$hightemp = $row1['temperature'];
							else
							{
								if ($hightemp < $row1['temperature'])
									$hightemp = $row1['temperature'];
							}
						}
						else
						{
							$prevDaylight = $ssdate->format('I');
//							echo "(" . $prevDaylight . ") " . $ssdate->format($format) . " || " . $row1['formatted'] . PHP_EOL;

							// Check the data in DB matches to 6 minutes interval
							if ($ssdate->format($format) >= $row1['formatted'])
							{
								if (is_null($hightemp))
									$hightemp = $row1['temperature'];
								else
								{
									if ($hightemp < $row1['temperature'])
										$hightemp = $row1['temperature'];
								}

							}
							else
							{
								$newItem = new \stdClass();
								$newItem->{'dt'} = $ssdate->format($format); 	
								$newItem->{'temp'} = $hightemp;
								array_push($newDevice->{'data'}, $newItem);
//								print_r($newItem);

								// Increase 6 minutes
								$ssdate->add(new DateInterval('PT6M'));
								$nextDaylight = $ssdate->format('I');
//								echo "Next: " . $nextDaylight . PHP_EOL;
//								echo $ssdate->format($format) . PHP_EOL;
//								echo $row1['formatted'] . PHP_EOL . PHP_EOL;

								if ($prevDaylight == 1 && $nextDaylight == 0)
								{
//									echo "Daylight Savings released... " . PHP_EOL;
									$ssdate->add(new DateInterval('PT1H'));
									$nextDaylight = $ssdate->format('I');
//									echo "Next: " . $nextDaylight . PHP_EOL;
//									echo $ssdate->format($format) . PHP_EOL;
								}

								while ($ssdate->format($format) < $row1['formatted'])
								{
									$newItem = new \stdClass();
									$newItem->{'dt'} = $ssdate->format($format); 	
									$newItem->{'temp'} = null;
									$prevDaylightIn = $ssdate->format('I');
									array_push($newDevice->{'data'}, $newItem);
//									print_r($newItem);

									// Increase 6 minutes
									$ssdate->add(new DateInterval('PT6M'));
									$nextDaylightIn = $ssdate->format('I');
//									echo $prevDaylightIn . " : " . $nextDaylightIn . PHP_EOL;

									if ($prevDaylightIn == 1 && $nextDaylightIn == 0)
									{
//										echo "Daylight Savings released... " . PHP_EOL;
										$ssdate->add(new DateInterval('PT1H'));
									}
								}

								$hightemp = null;
								if (is_null($hightemp))
									$hightemp = $row1['temperature'];
								else
								{
									if ($hightemp < $row1['temperature'])
										$hightemp = $row1['temperature'];
								}
							}
							
						}
					}
					
					if ($option == "ADD")
					{
						$newItem = new \stdClass();
						$newItem->{'dt'} = $ssdate->format($format); 	
						$newItem->{'temp'} = $hightemp;
						array_push($newDevice->{'data'}, $newItem);
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

//			echo "Ending... " . $row['deviceid'] . " - " . $row['devicename'] . PHP_EOL;
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