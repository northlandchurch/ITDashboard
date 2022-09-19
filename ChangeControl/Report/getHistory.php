<?php
require_once '../../config.php';

// Return value
$obj = array();
$response = array();

////////////////////////////////////////////////////////////////////////////////////////////////
// 	Database Connection
////////////////////////////////////////////////////////////////////////////////////////////////
$link = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);

if (!$link) 
{
	$obj['result'] = 200;
	$change = new \stdClass();
	$change->{'msg'} = "Unable to connect to Database - " . mysqli_connect_errno();
	array_push($response, $change);
	$obj['response'] = $response;
	echo json_encode($obj);
//    echo "Error: Unable to connect to MySQL." . PHP_EOL;
//    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    exit;
}


////////////////////////////////////////////////////////////////////////////////////////////////
// 	Retrieve 'action' parameter to determine the request type
////////////////////////////////////////////////////////////////////////////////////////////////
if (isset($_GET['action'])) {
	$action = $_GET['action'];
} else {
	$action = "None";
}


////////////////////////////////////////////////////////////////////////////////////////////////
// 	Generate SQL statement based on the request
////////////////////////////////////////////////////////////////////////////////////////////////
$sql = "SELECT id, requestor, systems, changes, affectedsystems, changedate, reqipaddress, createddatetime FROM ChangeControls ORDER BY 1 DESC LIMIT 100";

if ($action == "filter")
{
	$requestor = $_GET['requestor'];
	$system = $_GET['systems'];
	$from = $_GET['from'];
	$to = $_GET['to'];
	$text = $_GET['text'];
/*	
	// Sample Test Data
	$requestor = "";
	$system = "Server";
	$from = "2021-03-25";
	$to = "";
*/

	$counter = 0;
	$where = "";
	if ($requestor != "")
	{
		if ($counter > 0) {
			$where .= " AND requestor = '$requestor'";
		} else {
			$where .= " WHERE requestor = '$requestor'";
		}
		$counter++;
	}
	
	if ($system != "")
	{
		if ($counter > 0) {
			$where .= " AND systems = '$system'";
		} else {
			$where .= " WHERE systems = '$system'";
		}
		$counter++;
	}

	if ($from != "")
	{
		if ($counter > 0) {
			$where .= " AND changedate >= '$from'";
		} else {
			$where .= " WHERE changedate >= '$from'";
		}
		$counter++;
	}

	if ($to != "")
	{
		if ($counter > 0) {
			$where .= " AND changedate <= '$to'";
		} else {
			$where .= " WHERE changedate <= '$to'";
		}
		$counter++;
	}

	if ($text != "")
	{
		if ($counter > 0) {
			$where .= " AND (changes like '%" . $text . "%' OR affectedsystems like '%" . $text . "%')";
		} else {
			$where .= " WHERE (changes like '%" . $text . "%'OR affectedsystems like '%" . $text . "%')";
		}
		$counter++;
	}

	if ($counter > 0)
	{
		$sql = "SELECT id, requestor, systems, changes, affectedsystems, changedate, reqipaddress, createddatetime FROM ChangeControls " . $where . " ORDER BY 1 DESC LIMIT 100";
	}
}


////////////////////////////////////////////////////////////////////////////////////////////////
// 	Execute SQL statement and generate success/error messages
////////////////////////////////////////////////////////////////////////////////////////////////
if ($result = mysqli_query($link, $sql)) 
{
	if (mysqli_num_rows($result) > 0)
	{
		while ($row = mysqli_fetch_array($result))
		{
			$change = new \stdClass();
			$change->{'id'} = $row['id'];
			$change->{'requestor'} = $row['requestor'];
			$change->{'system'} = $row['systems'];
			$change->{'change'} = $row['changes'];
			$change->{'affected'} = $row['affectedsystems'];
			$change->{'changedt'} = $row['changedate'];
			$change->{'reqip'} = $row['reqipaddress'];
			$change->{'createdt'} = $row['createddatetime'];
			
//			var_dump($change);
//			echo PHP_EOL . PHP_EOL;
			
			array_push($response, $change);
		}
		mysqli_free_result($result);
		
		$obj['result'] = 100;
	}
	else
	{
		$obj['result'] = 200;
		$change = new \stdClass();
		$change->{'msg'} = "No records were found.";
		array_push($response, $change);
//		echo "No records matching your query were found." . PHP_EOL;
//		echo " => " . $sql . PHP_EOL;	
	}
}
else 
{
	$obj['result'] = 200;
	$change = new \stdClass();
	$change->{'msg'} = "Could not able to execute $sql";
	array_push($response, $change);
//	echo "ERROR: Could not able to execute $sql." . PHP_EOL;
//	echo " => " . mysqli_error($link) . PHP_EOL;	
}

$obj['response'] = $response;

echo json_encode($obj);

mysqli_close($link);



?>