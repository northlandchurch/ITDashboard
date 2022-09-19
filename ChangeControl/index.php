<?php
	require_once '/var/www/libs/swiftmailer/vendor/autoload.php';
	require_once '../config.php';
?>
<?php
	include_once '../html_open.php';
?>

<?php
	// define variables and set to empty values
	$nameErr = $systemErr = $changesErr = $affectedErr = $emailErr = $genderErr = $websiteErr = "";
	$name 	= $systems 	= $changes 		= $affected 	= $email 	= $gender = $comment = $website = "";
	$val_insert_id  = $val_requestor = $val_system = $val_changes = $val_affected = $val_changedate = $val_ipaddress = $val_datetime = "";
	$response = $errorMsg= "";
	
	$okay = true; 
	
	if ($_SERVER["REQUEST_METHOD"] == "POST") 
	{
		if (empty(trim($_POST["inputRequestor"]))) {
			$nameErr = "Name is required";
			$okay = false;
		} else {
			$name = test_input($_POST["inputRequestor"]);
			// check if name only contains letters and whitespace
			if (!preg_match("/^[a-zA-Z-' ]*$/", $name)) {
				$nameErr = "Only letters and white space allowed";
				$okay = false;
			}
		}

		if (empty(trim($_POST["inputSystem"]))) {
			$systemErr = "System is required";
			$okay = false;
		} else {
			$systems = test_input($_POST["inputSystem"]);
		}

		if (empty(trim($_POST["inputChanges"]))) {
			$changesErr = "Enter the changes";
			$okay = false;
		} else {
			$changes = test_input($_POST["inputChanges"]);
//			$changes = mysql_real_escape_string(test_input($_POST["inputChanges"]));
		}

		if (empty(trim($_POST["inputAffected"]))) {
			$affectedErr = "Enter the affected system";
			$okay = false;
		} else {
			$affected = test_input($_POST["inputAffected"]);
		}
/*
		if (empty($_POST["email"])) {
			$emailErr = "Email is required";
		} else {
			$email = test_input($_POST["email"]);
			// check if e-mail address is well-formed
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$emailErr = "Invalid email format";
			}
		}

		if (empty($_POST["website"])) {
			$website = "";
		} else {
			$website = test_input($_POST["website"]);
			// check if URL address syntax is valid (this regular expression also allows dashes in the URL)
			if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$website)) {
				$websiteErr = "Invalid URL";
			}
		}

		if (empty($_POST["gender"])) {
			$genderErr = "Gender is required";
		} else {
			$gender = test_input($_POST["gender"]);
		}
*/
		$changeDate = $_POST["inputDate"];
		$reqIP = $_POST["inputReqIPAddr"];

		if ($okay)
		{

			// Connect to DB
			$link = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);

			if (!$link) 
			{
				$errorMsg = "Unable to connect to MySQL => " . mysqli_connect_error();
//				die("Connect Error: " . $errorMsg);
//				exit;
			}

			// Insert the change info
			$sql = "INSERT INTO ChangeControls (requestor, systems, changes, affectedsystems, changedate, reqipaddress) VALUES (?, ?, ?, ?, ?, ?) ";
			if ($stmt = $link->prepare($sql))
			{
				$stmt->bind_param("ssssss", $name, $systems, $changes, $affected, $changeDate, $reqIP);
				
				
//				$stmt->execute();
				if (!$stmt->execute())
				{
					$errorMsg = "<span class='error'>Could not execute INSERT statement => " . mysqli_error($link) . "</span><br>";
				}
				

				$insert_id = $stmt->insert_id;
				$val_insert_id = "<b>Change Control #:</b> " . $insert_id . "<br>";

				$stmt->close(); 
			}
			else 
			{
				$errorMsg = "<span class='error'>Could not prepare INSERT statement => " . mysqli_error($link) . "</span><br>";
			}

			// Retrieve the device info to return it to the client
			if ($stmt = $link->prepare("SELECT requestor, systems, changes, affectedsystems, changedate, reqipaddress, createddatetime FROM ChangeControls WHERE id=?"))
			{
				$stmt->bind_param("i", $insert_id);
				$stmt->execute();
				$stmt->bind_result($r_requestor, $r_system, $r_changes, $r_affected, $r_changedate, $r_ipaddress, $r_datetime);
				$stmt->fetch();

				$val_requestor = "<b>Requestor:</b> " . $r_requestor . "<br>";
				$val_system = "<b>System:</b> " . $r_system . "<br>";
				$val_changes= "<b>Change to be made:</b> <br>" . $r_changes . "<br>";
				$val_affected = "<b>Affected Systems:</b> <br> " . $r_affected . "<br>";
				$val_changedate = "<b>Date Changed:</b> " . $r_changedate . "<br>";
				$val_ipaddress = "<b>Requestor IP Address:</b> " . $r_ipaddress . "<br>";
				$val_datetime = "<b>Requested Date & Time:</b> " . $r_datetime . "<br>";
				
				// Send a notification email 
				$msg = $val_requestor . $val_system . $val_changes . $val_affected . $val_changedate;
				sendNotifyEmail($msg);
				
				$stmt->close(); 
			}
			else
			{
				$errorMsg = "<span class='error'>Could not prepare SELECT statement => " . mysqli_error($link) . "</span><br>";
			}
			
			mysqli_close($link);
		}
	}


	function test_input($data) 
	{
		$data = trim($data);
		$data = stripslashes($data);
//		$data = htmlspecialchars($data);
		return $data;
	}



	function sendNotifyEmail($msg)
	{
		global $response; 
		
		// Create the Transport
		$transport = (new Swift_SmtpTransport(EMAILSERVER, EMAILPORT, 'tls'))->setUsername(EMAILUSER)->setPassword(EMAILPASSWORD);

		// Create the Mailer using your created Transport
		$mailer = new Swift_Mailer($transport);

		// Create a message
		$message = (new Swift_Message(NOTIFYSUBJECT))->setFrom(SENDEREMAIL)->setTo(NOTIFYEMAIL)->setReplyTo(REPLYTOEMAIL)->setCc(TESTEMAIL);

		$message->setBody(returnNotifiyHtmlBody($msg), 'text/html');

		// Add alternative parts with addPart()
		$message->addPart(returnNotifyTextBody($msg), 'text/plain');

		try {
			// Send the message
			$result = $mailer->send($message);
			$response = '<div class="alert alert-success" role="alert">Sent a notification email to adminnotofication@northlandchurch.net </div>' ;
		} 
		catch (Swift_TransportException $STe) 
		{
			$response = '<div class="alert alert-warning" role="alert">Error1 on sending an email: ' . $STe->getMessage() . '</div>';
			return false;
		} 
		catch (Exception $e) 
		{
			$response = '<div class="alert alert-warning" role="alert">Error2 on sending an email: ' . $e->getMessage() . '</div>';
			return false;
		}
	}

	function returnNotifiyHtmlBody($msg)
	{
		$htmlBody = "						
			<p style='font-family:Calibri;'>
			<h3>Changes made and stored in Change Control System: </h3>
			" . $msg . "
			<br><br>

			Thank you,<br>
			IT @ Northland Church<br><br>
			</p>

		";

		return $htmlBody;
	}

	function returnNotifyTextBody($msg)
	{
		$textBody = "
			Changes made and stored in Change Control System: 

			" . $msg . " 


			Thank you,
			IT @ Northland Church 

		";
		
		return $textBody;
	}

	
?>




<div class="row m-4">
	<div class="col-md-12">
		<h1 class="text-center">Change Control</h1>
	</div>
</div>

<div class="row py-2">
	<div class="col-md-2">
	</div>

	<div class="col-md-8">
	<div class="card">
	<div class="card-body">
		<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
			<div class="form-group row">
				<label for="inputRequestor" class="col-sm-2 col-form-label">Requestor: </label>
				<div class="col-sm-10">
					<select class="form-control" id="inputRequestor" name="inputRequestor">
					<?php 
						foreach($itMembers as $name => $value)
						{
							echo '<option value="' . $value . '">' . $name . '</option>';
						}
					?>
					</select>
				</div>
				<span class="error pl-3"><?php echo $nameErr; ?></span>
			</div>

			<div class="form-group row">
				<label for="inputSystem" class="col-sm-2 col-form-label">System: </label>
				<div class="col-sm-10">
					<select class="form-control" id="inputSystem" name="inputSystem">
						<?php 
							foreach($itSystems as $name => $value)
							{
								echo '<option value="' . $value . '">' . $name . '</option>';
							}
						?>
					</select>
				</div>
				<span class="error pl-3" ><?php echo $systemErr; ?></span>
			</div>

			<div class="form-group">
				<label for="inputChanges" class="col-form-label">Change to be made: </label>
				<textarea class="form-control" id="inputChanges" name="inputChanges" rows="3" required></textarea>
				<span class="error"><?php echo $changesErr; ?></span>
			</div>			
			
			<div class="form-group">
				<label for="inputAffected">Affected systems: </label>
				<textarea class="form-control" id="inputAffected" name="inputAffected" rows="3" required></textarea>
				<span class="error"><?php echo $affectedErr; ?></span>
			</div>			
			
			<div class="form-row">
				<div class="col-sm-3">
					<label for="inputDate" class="col-form-label">Date Changed: </label>	
				</div>
				<div class="col-sm-3">
					<input type="date" class="form-control" id="inputDate" name="inputDate" required>
				</div>
				<div class="col-sm-3">
					<label for="inputReqIPAddr" class="col-form-label">Requestor IP address: </label>
				</div>
				<div class="col-sm-3">
					<input type="text" class="form-control-plaintext" id="inputReqIPAddr" name="inputReqIPAddr"  readonly value="<?php echo $_SERVER['REMOTE_ADDR']; ?>">
				</div>
			</div>			

			<div class="text-center mt-2" >
				<button type="submit" class="btn btn-info">Submit</button>
				<button type="reset"  class="btn btn-secondary">Clear</button>
			</div>
		</form>
	
	</div>
	</div>
	</div>
	
	<div class="col-md-2">
	</div>
	
</div>

<div class="row">
	<div class="col-md-2">
	</div>

	<div class="col-md-8">
		<div class="card">
			<div class="card-body text-center">
				<h2>Result</h2>
			</div>

			<div class="card-body pt-0">
			<?php
			if ($errorMsg != "")
			{
				echo $errorMsg;
			}
			else
			{
				echo $val_insert_id;
				echo $val_requestor;
				echo $val_system;
				echo $val_changes;
				echo $val_affected;
				echo $val_changedate;
				echo $val_ipaddress;
				echo $val_datetime;
				
				echo "<br>";
				echo $response;
//				print_r($_POST);
			}
			?>				
			</div>
		</div>
	</div>

	<div class="col-md-2">
	</div>
</div>


<script>
	
	
	$(document).ready(function(){	

		$('#navbar-changecontrol').addClass('active');

		const now = moment().format('YYYY-MM-DD');
		$("#inputDate").val(now.toString());
		console.log("Date: " + now.toString());
	});


	// Display current time on the page
	function UpdateTime()
	{
		const now = moment().format('HH:mm:ss');
		$("#updatedtime").html("Updated at " + now.toString());
	}
	
	

</script>
</body>

</html>
