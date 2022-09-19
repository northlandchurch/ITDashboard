<?php
	include_once '../html_open.php';
?>



<div class="card bg-light it-border mt-2" style="height:100vh">
	<div class="card-header text-white bg-danger">IT Dashboard</div>
	<div class="card-body ">
		<iframe src="/ITMonitor/draft.html" 
			style="position:absolute; top:0px; left:0px; width:100%; height:100%; border: none; overflow: hidden;">
		</iframe>
	</div>
</div>





<script>
	
	$(document).ready(function(){	

		$('#navbar-itmonitor').addClass('active');

	});


</script>
</body>

</html>
