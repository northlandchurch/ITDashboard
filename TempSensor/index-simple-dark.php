<?php
	include_once '../html_open.php';
?>



<div class="card bg-light it-border" style="height:100vh">
	<div class="card-header text-white bg-danger">Temperature Monitoring</div>
	<div class="card-body ">
		<iframe src="/TempSensor/index-simple-dark.html" 
			style="position:absolute; top:0px; left:0px; width:100%; height:100%; border: none; overflow: hidden;">
		</iframe>
	</div>
</div>




<script>
	
	$(document).ready(function(){	

		$('#navbar-tempmonitor').addClass('active');

	});


</script>
</body>

</html>
