<?php
	include_once 'html_open.php';
?>


<div class="row m-4">
	<div class="col-md-12">
		<h1 class="text-center">IT Dashboard</h1>
	</div>
</div>

<div class="row">
	<div class="col-md-3">
	</div>

	<div class="col-md-6">
		<div class="card">
			<div class="card-body text-center">
				<h3>Many tools will be here!</h3>
			</div>

			<div class="card-body">
				<form>
					<div class="form-group row">
						<label for="inputRequestor" class="col-sm-3 col-form-label">IT Members: </label>
						<div class="col-sm-9">
							<select class="form-control" id="inputRequestor" name="inputRequestor">
								<option value="Dennis Bode">Awesome Dennis Bode</option>
								<option value="Chris Gamble">Fantastic Chris Gamble</option>
								<option value="Johan Gere">Super Johan Gere</option>
								<option value="Kee Pang">Coooool Kee Pang</option>
							</select>
						</div>
					</div>

					<div class="form-group row">
						<label for="inputDate" class="col-sm-3 col-form-label">Date & Time: </label>	
						<div class="col-sm-9">
							<input type="text" class="form-control-plaintext" id="inputDate" name="inputDate" readonly value="None">	
						</div>
					</div>

					<div class="form-group row">
						<label for="inputReqIPAddr" class="col-sm-3 col-form-label">Your IP Address: </label>	
						<div class="col-sm-9">
							<input type="text" class="form-control-plaintext" id="inputReqIPAddr" name="inputReqIPAddr"  readonly value="<?php echo $_SERVER['REMOTE_ADDR']; ?>">
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="col-md-3">
	</div>
</div>



<script>
	
	
	$(document).ready(function(){	

		$('#navbar-home').addClass('active');

		const now = moment().format('MMMM Do YYYY, h:mm:ss a');
		$("#inputDate").val(now.toString());
	});


</script>
</body>

</html>
