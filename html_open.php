<!DOCTYPE html>
<html lang="en">

<head>

<!--	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">	-->
	<!-- Visit https://bootswatch.com/ for more themes -->
	<link rel="stylesheet" href="/css/bootswatch/dist/slate/bootstrap.min.css">

	<title>IT Dashboard</title>

	<!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>

	<style>
		canvas {
			-moz-user-select: none;
			-webkit-user-select: none;
			-ms-user-select: none;
		}

		.error {color: #FF0000;}

	</style>

</head>




<body class="">

<nav class="navbar navbar-expand-lg fixed-top navbar-dark bg-primary">
	<a class="navbar-brand" href="https://www.northlandchurch.net"><img src="/images/icon_128.png" width="32" height="32"></a>

	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>

	<div class="collapse navbar-collapse" id="navbarSupportedContent">
		<ul class="navbar-nav mr-auto">
			<li class="nav-item">
				<a class="nav-link" href="http://itdashboard.northlandchurch.org/index.php" id="navbar-home">Home</a>
			</li>
			<li class="nav-item dropdown">
				<a class="nav-link dropdown-toggle" href="/ChangeControl" id="navbar-changecontrol" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					Change Control
				</a>
				<div class="dropdown-menu" aria-labelledby="navbar-tempmonitor">
					<a class="dropdown-item" href="/ChangeControl/">Main</a>
				<div class="dropdown-divider"></div>
					<a class="dropdown-item" href="/ChangeControl/Report/">Report</a>
				</div>
			</li>
			<li class="nav-item dropdown">
				<a class="nav-link dropdown-toggle" href="/TempSensor" id="navbar-tempmonitor" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					Temp Monitor
				</a>
				<div class="dropdown-menu" aria-labelledby="navbar-tempmonitor">
					<a class="dropdown-item" href="/TempSensor/">Main</a>
					<a class="dropdown-item" href="/TempSensor/index-simple-dark.php">Dark Mode</a>
				<div class="dropdown-divider"></div>
					<a class="dropdown-item" href="/TempSensor/admin/">Admin</a>
				</div>
			</li>

			<li class="nav-item dropdown">
				<a class="nav-link dropdown-toggle" href="/ITMonitor" id="navbar-itmonitor" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					IT Monitor
				</a>
				<div class="dropdown-menu" aria-labelledby="navbar-itmonitor">
					<a class="dropdown-item" href="/ITMonitor/">IT Room Monitor</a>
					<a class="dropdown-item" href="/ITMonitor/coop.php">Co-Op Monitor</a>
				</div>
			</li>
			<li class="nav-item dropdown">
				<a class="nav-link dropdown-toggle" href="/Access" id="navbar-access" role="button" data-toggle="dropdown" aria-haspopup="true" aria-e>
                                        Access
                                </a>
                                <div class="dropdown-menu" aria-labelledby="navbar-access">
                                        <a class="dropdown-item" href="/Access/">Access</a>
                                        <a class="dropdown-item" href="/Access/insert.php">Access Edit</a>
                                </div>
			</li>
<!--
			<li class="nav-item">
				<a class="nav-link" href="/draft.php" id="navbar-itmonitor2">IT Monitor</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="/buybutton.php" id="navbar-demo">OCC Mockup</a>
			</li>
-->
			<li class="nav-item">
			<a class="nav-link disabled" href="#">Coming...</a>
			</li>
			
			
		</ul>
	</div>
</nav>


<div class="p-4">
</div>
