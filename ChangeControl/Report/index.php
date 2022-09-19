<?php
	require_once '../../config.php';
?>
<?php
	include_once '../../html_open.php';
?>


<div class="row m-4">
	<div class="col-md-12">
		<h1 class="text-center">Change Control - Report</h1>
	</div>
</div>

<div class="col-md-12">
	<div class="card">
	<div class="card-body">
		<h5 class="card-title">Select Filter</h5>
		<form>
			<div class="form-group row">
				<div class="col-sm-4">
					<select class="form-control" id="inputRequestor" name="inputRequestor">
						<option value="">Select Requestor...</option>
						<?php 
							foreach($itMembers as $name => $value)
							{
								echo '<option value="' . $value . '">' . $name . '</option>';
							}
						?>
					</select>
				</div>
				<div class="col-sm-4">
					<select class="form-control" id="inputSystem" name="inputSystem">
						<option value="">Select System...</option>
						<?php 
							foreach($itSystems as $name => $value)
							{
								echo '<option value="' . $value . '">' . $name . '</option>';
							}
						?>
					</select>
				</div>

				<div class="col-sm-4">
					<input type="text" class="form-control" id="inputText" name="inputText" placeholder="Enter Search Text">
				</div>
			</div>
			
			<div class="form-group row">
				<label for="inputFrom" class="col-sm-1 col-form-label">From: </label>	
				<div class="col-sm-3">
					<input type="date" class="form-control" id="inputFrom" name="inputFrom" >
				</div>
				<label for="inputTo" class="col-sm-1 col-form-label">To: </label>
				<div class="col-sm-3">
					<input type="date" class="form-control" id="inputTo" name="inputTo">
				</div>
			</div>			

			<div class="text-center mt-2" >
				<button id="search" class="btn btn-info">Search</button>
				<button type="reset"  class="btn btn-secondary">Clear</button>
			</div>
		</form>
	</div>
	</div>
</div>


<div class="col-md-12">
	<div class="card">
		<div id="updatedtime" class="card-body text-center">
		</div>

		<div class="card-body pt-0 pb-0"  style="display:block">
			<div id="searchWarning" class="alert alert-warning" role="alert" style="display:none">
			</div>
			<div class="alert alert-danger" role="alert" style="display:none">
			</div>
		</div>

		<div class="card-body pt-0 pb-0">
			<table class="table table-striped table-bordered" id="legendTable">
				<thead>
					<tr>
						<th scope="col">Name</th>
						<th scope="col">System</th>
						<th scope="col">Changes</th>
						<th scope="col">Affected Systems</th>
						<th scope="col">Date Changed</th>
					</tr>
				</thead>
				<tbody id="legend">
				</tbody>
			</table>
		</div>
	</div>
</div>
<br>



<script src="https://cdn.jsdelivr.net/npm/handlebars@latest/dist/handlebars.js"></script>

<script id="legend-template" type="text/x-handlebars-template">
	<tr id="change{{id}}" >
		<td>{{requestor}}</td>
		<td>{{system}}</td>
		<td>{{change}}</td>
		<td>{{affected}}</td>
		<td>{{changedt}}</td>
	</tr>				
</script>


<script>
	
$(document).ready(function(){	

	$('#navbar-changecontrol').addClass('active');

	const url = "getHistory.php?action=init";

	// Search the recent records
	getData(url);
	
	// Triggered when 'Search' button clicked or "Enter" pressed
	$('form').submit(function (evt) {
		$(".alert").hide();
		evt.preventDefault();

		const parameters = "action=filter&requestor=" + $("#inputRequestor").val() + "&systems=" + $("#inputSystem").val() + "&from=" + $("#inputFrom").val() + "&to=" + $("#inputTo").val() + "&text=" + $("#inputText").val();
		
		const url = "getHistory.php?" + parameters;

		getData(url);
	});	

	
});

// Handlebars variables
let source = $("#legend-template").html();
let handle_template = Handlebars.compile(source);


async function getData(url) 
{
	console.log("Request URL: " + url);
	const response = await fetch(url);

	if (response.ok) { // if HTTP-status is 200-299
		const json = await response.json();
		let html = "";

//		console.log(json);
	
		if (json.result == 100)
		{
			json.response.forEach(change => {
	//			console.log(change.id, change.requestor);

				// Compose data for table used by Handlebars
				const content = { 
					id: change.id,
					requestor: change.requestor,
					system: change.system,
					change: change.change,
					affected: change.affected,
					changedt: change.changedt,
					reqip: change.reqip,
				};
	//			console.log(content);

				// Put content into Handlebar Template
				html += handle_template(content);

			});
			$("#legend").html(html);
			UpdateResult(json.response.length);
		}
		else 
		{
			console.log(json.response[0].msg);
			$("#searchWarning").html(json.response[0].msg).show();
			$("#legend").html("");			
			UpdateResult(0);
		}			
		

	} else {
		alert("HTTP-Error: " + response.status);
	}
}		

// Display current time on the page
function UpdateResult(count)
{
	const now = moment().format('HH:mm:ss');
	$("#updatedtime").html(count + " returned at " + now.toString());
}
	
	

</script>
</body>

</html>
