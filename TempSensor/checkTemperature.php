<?php
require_once '/var/www/libs/swiftmailer/vendor/autoload.php';
require_once 'config.php';


$link = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);

if (!$link) 
{
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    exit;
}


$sql = "SELECT deviceid, devicename FROM devices WHERE isactive = 1";

if ($result = mysqli_query($link, $sql)) 
{
	if (mysqli_num_rows($result) > 0)
	{
		while ($row = mysqli_fetch_array($result))
		{
			echo $row['deviceid'] . " - " . $row['devicename'] . PHP_EOL;
			$devId = $row['deviceid'];
			$devName = $row['devicename'];
			
			$sql1 = "SELECT createdat, temperature FROM activities WHERE deviceid = '$devId' ORDER BY 1 desc LIMIT 1";
			if ($result1 = mysqli_query($link, $sql1)) 
			{
				if (mysqli_num_rows($result1) > 0)
				{
					while ($row1 = mysqli_fetch_array($result1))
					{
						echo $row1['temperature'] . " at " . $row1['createdat'] . PHP_EOL;
						$curTemp = $row1['temperature'];
						$curTime = $row1['createdat'];
						if ($curTemp > TEMPTHRESHOLD)
						{
							$msg0 = "High Temperature noticed at $devName - $devId";
							$msg1 = "The Temperature is $curTemp at $curTime";
							sendTempHighEmail($msg0, $msg1);
						}
					}
					mysqli_free_result($result1);
				}
				else
				{
					echo "No records matching your query were found." . PHP_EOL;
					echo " => " . $sql1 . PHP_EOL;	
					$msg = "No tempterature data in $devId - $devName";
					sendErrorEmail($msg);
				}
			}
			else 
			{
				echo "ERROR: can not execute $sql1." . PHP_EOL;
				echo " => " . mysqli_error($link) . PHP_EOL;	
				$msg = "Can not execute a query: $sql1 <br> => " . mysqli_error($link);
				sendErrorEmail($msg);
				exit;
			}
			
		}
		mysqli_free_result($result);
	}
	else
	{
		echo "No records matching your query were found." . PHP_EOL;
		echo " => " . $sql . PHP_EOL;	
		$msg = "No device is active!";
		sendErrorEmail($msg);
	}
}
else 
{
	echo "ERROR: Could not able to execute $sql." . PHP_EOL;
	echo " => " . mysqli_error($link) . PHP_EOL;	
	$msg = "Can not execute a query: $sql <br> => " . mysqli_error($link);
	sendErrorEmail($msg);
}


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


function sendErrorEmail($msg)
{
	// Create the Transport
	$transport = (new Swift_SmtpTransport(EMAILSERVER, EMAILPORT, 'tls'))->setUsername(EMAILUSER)->setPassword(EMAILPASSWORD);
	// Create the Mailer using your created Transport
	$mailer = new Swift_Mailer($transport);

	// Create a message
	$message = (new Swift_Message(ERRORSUBJECT))->setFrom(SENDEREMAIL)->setTo(TESTEMAIL)->setReplyTo(REPLYTOEMAIL)->setCc(TESTEMAIL);


	$message->setBody(returnErrorHtmlBody($msg), 'text/html');

	// Add alternative parts with addPart()
	$message->addPart(returnErrorTextBody($msg), 'text/plain');

	try {
		// Send the message
		$result = $mailer->send($message);
		echo "Success! " . $result . PHP_EOL;
	} 
	catch	(Swift_TransportException $STe) 
	{
		echo "Error1: " . $STe->getMessage() . PHP_EOL;
		return false;
	} 
	catch (Exception $e) 
	{
		echo "Error2: " . $e->getMessage() . PHP_EOL;
		return false;
	}
}

function returnErrorHtmlBody($msg)
{
	$htmlBody = "						
		<p style='font-family:Calibri;'>
		Error ecountered when running temperature check script:
		<br><br>" . $msg . "
		<br><br>

		Thank you,<br>
		Northland Church<br><br>
		</p>

	";

	return $htmlBody;
}

function returnErrorTextBody($msg)
{
	$textBody = "
		Error ecountered when running temperature check script:

		" . $msg . " 


		Thank you,
		Northland Church 

	";
	
	return $textBody;
}


function sendTempHighEmail($msg0, $msg1)
{
	// Create the Transport
	$transport = (new Swift_SmtpTransport(EMAILSERVER, EMAILPORT, 'tls'))->setUsername(EMAILUSER)->setPassword(EMAILPASSWORD);
	// Create the Mailer using your created Transport
	$mailer = new Swift_Mailer($transport);

	// Create a message
	$message = (new Swift_Message(TEMPHIGHSUBJECT))->setFrom(SENDEREMAIL)->setTo(NOTIFYEMAIL)->setReplyTo(REPLYTOEMAIL)->setCc(TESTEMAIL);
//	$message = (new Swift_Message(TEMPHIGHSUBJECT))->setFrom(SENDEREMAIL)->setTo(TESTEMAIL)->setReplyTo(REPLYTOEMAIL)->setCc(TESTEMAIL);


	$message->setBody(returnTempHighHtmlBody($msg0, $msg1), 'text/html');

	// Add alternative parts with addPart()
	$message->addPart(returnTempHighTextBody($msg0, $msg1), 'text/plain');

	try {
		// Send the message
		$result = $mailer->send($message);
		echo "Success! " . $result . PHP_EOL;
	} 
	catch	(Swift_TransportException $STe) 
	{
		echo "Error: " . $STe->getMessage() . PHP_EOL;
		return false;
	} 
	catch (Exception $e) 
	{
		echo "Error: " . $e->getMessage() . PHP_EOL;
		return false;
	}
}

function returnTempHighHtmlBody($msg0, $msg1)
{
	$htmlBody = "						
		<p style='font-family:Calibri;'>" . 
		$msg0 . "<br>" . $msg1 . 
		"<br><br>
		<br><br>

		Thank you,<br>
		Northland Church<br><br>
		</p>

	";

	return $htmlBody;
}

function returnTempHighTextBody($msg0, $msg1)
{
	$textBody = "

		" . $msg0 . " 

		" . $msg1 . "

		Thank you,
		Northland Church 

	";
	
	return $textBody;
}

?>