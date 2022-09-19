<?php
require_once '../config.php';

// Return value
$obj = array();
$obj['data'] = array();
$result = new \stdClass();


// Checking input values
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if ($contentType === "application/json") 
{
	//Receive the RAW post data.
	$content = trim(file_get_contents("php://input"));

	$decoded = json_decode($content, true);

	//If json_decode failed, the JSON is invalid.
	if(! is_array($decoded)) 
	{
		$result->{'status'} = "Error";
		$result->{'msg'} = "Data is not array";
		$obj['result'] = $result;
		echo json_encode($obj);
		exit;
	} 
	else 
	{
		$input_id = $decoded['id'];
		$input_devName = $decoded['devName'];
		$input_active = $decoded['active'];
	}
}
else 
{
	$result->{'status'} = "Error";
	$result->{'msg'} = "Content Type not Set";
	$obj['result'] = $result;
	echo json_encode($obj);
	exit;
}


// Connect to DB
$link = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);

if (!$link) 
{
	$msg = "Unable to connect to MySQL => " . mysqli_connect_errno();
 	$result->{'status'} = "Error";
	$result->{'msg'} = $msg;
	$obj['result'] = $result;
	echo json_encode($obj);
    exit;
}


// Processing 
$format = 'Y-m-d H:i:s';
$date = date($format, time());

// Update the device info
$sql = "UPDATE devices SET devicename=?, isactive=?, lastmodified=? WHERE id=? ";
if ($stmt = $link->prepare($sql))
{
	$stmt->bind_param("sisi", $input_devName, $input_active, $date, $input_id);
	$stmt->execute();
	$stmt->close(); 

	$result->{'status'} = "Success";
}
else 
{
	$msg = "Could not prepare UPDATE statement => " . mysqli_error($link);
	$result->{'status'} = "Error";
	$result->{'msg'} = $msg;
}

// Retrieve the device info to return it to the client
if ($stmt = $link->prepare("SELECT id, deviceid, devicename, isactive, lastmodified FROM devices WHERE id=?"))
{
	$stmt->bind_param("i", $input_id);
	$stmt->execute();
	$stmt->bind_result($id, $devId, $devName, $isActive, $lastDT);
	$stmt->fetch();

	$device = new \stdClass();
	$device->{'id'} = $id;
	$device->{'devId'} = $devId;
	$device->{'devName'} = $devName;
	$device->{'active'} = $isActive;
	$device->{'dt'} = $lastDT;
	
	array_push($obj['data'], $device);
	
	$stmt->close(); 
}
else
{
	$msg = "Could not prepare SELECT statement => " . mysqli_error($link);
	$result->{'status'} = "Error";
	$result->{'msg'} = $msg;
}



$obj['result'] = $result;

echo json_encode($obj);

mysqli_close($link);



?>