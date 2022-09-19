<?php
	include_once '../../html_open.php';
?>
	
	<div class="col-md-12">
		<div class="card">
			<div class="card-body text-center">
				<h1>Northland Temperature Monitoring System Admin</h1>
			</div>

			<div id="updatedtime" class="card-body text-center">
			</div>

			<div class="card-body text-center pt-0 pb-0">
				<table class="table" id="legendTable">
					<thead>
						<tr>
							<th scope="col">Active</th>
							<th scope="col">ID</th>
							<th scope="col">Name</th>
							<th scope="col">Action</th>
							<th scope="col">Last Modified</th>
						</tr>
					</thead>
					<tbody id="legend">
					</tbody>
				</table>
			</div>

			<div class="card-body pt-0 pb-0"  style="display:block">
				<div class="alert alert-success" role="alert" style="display:none">
				</div>
				<div class="alert alert-danger" role="alert" style="display:none">
				</div>
			</div>
				
			<div class="card-body text-center"  style="display:block">
				<button id="refresh" class="btn btn-info">Refresh</button>
				<button id="addDevice" class="btn btn-sm btn-secondary d-none" >Add Device</button>
			</div>
		</div>
	</div>
	<br>


<script src="https://cdn.jsdelivr.net/npm/handlebars@latest/dist/handlebars.js"></script>

<script id="legend-template" type="text/x-handlebars-template">
	<tr id="device{{id}}" >
		<th scope="row">
		{{#if (isDeviceActive active)}}
			<input type="checkbox" id="devActive{{id}}" checked>
		{{else}}
			<input type="checkbox" id="devActive{{id}}">
		{{/if}}
		</th>
		<td id="devId{{id}}">{{devId}}</td>
		<td><input id="devName{{id}}" type="text" class="form-control" value="{{loc}}" required></td>
		<td><button id="{{id}}" type="button" class="btn btn-outline-info btn-sm" onclick="SaveDevice(this);" data-id="{{id}}">Save</button></td>
		<td id="dt{{id}}">{{dt}}</td>
	</tr>				
</script>


<script>
	
	$(document).ready(function(){	

		$('#navbar-tempmonitor').addClass('active');

		GetInitialData();
	
		$("#refresh").click(function() {
			$(".alert").hide();
			GetInitialData();
		});

		$("#addDevice").click(function() {
			$(".alert").hide();
			AddDevice();
		});
	});

	// Handlebars variables
	let source = $("#legend-template").html();
	let handle_template = Handlebars.compile(source);

	let chart;



	async function SaveDevice(el)
	{
		$(".alert").hide();

		// Compose the request data
		const id = $(el).data("id");
		const devId = $("#devId"+id).text();
		const devName = $("#devName"+id).val();
		const devActive = ($("#devActive"+id).prop("checked")? 1:0);


		const url = "saveDevice.php";

		const response = await fetch(url, {
			method: 'POST',
			headers: {
				"Content-Type": "application/json"
			},
			body: JSON.stringify({
				id: id,
				devId, devId,
				devName: devName,
				active: devActive
			})
		});

		if (!response.ok) { // if HTTP-status is 200-299
			alert("HTTP-Error: " + response.status);
			return;
		}
		
		const json = await response.json();
//		console.log(json);

		// Error handling
		if (json.result.status == "Error")
		{
			$(".alert-danger").html(json.result.msg);
			$(".alert-danger").show();
			return;
		}
		
		$(".alert-success").html("Successfully Updated!");
		$(".alert-success").show();

		json.data.forEach(device => {
			console.log(device.devId, device.devName);
/*
			// Compose data for table used by Handlebars
			const content = { 
				id: device.id,
				loc: device.devName, 
				devId: device.devId, 
				active: device.active,
				dt: device.dt
			};
			console.log(content);
*/
			// Update values of the related row
//			$("#devActive"+device.id).prop('checked', (device.active==1 ? true:false));
//			$("#devName"+device.id).val(device.devName);
			$("#dt"+device.id).text(device.dt);
		});

		UpdateTime();
	}
	

	async function AddDevice(el)
	{
		$(".alert").hide();

		// Compose the request data
		const id = 0;
		const devId = $("#devId"+id).text();
		const devName = $("#devName"+id).val();
		const devActive = ($("#devActive"+id).prop("checked")? 1:0);


		const url = "addDevice.php";

		const response = await fetch(url, {
			method: 'POST',
			headers: {
				"Content-Type": "application/json"
			},
			body: JSON.stringify({
				devId, devId,
				devName: devName,
				active: devActive
			})
		});

		if (!response.ok) { // if HTTP-status is 200-299
			alert("HTTP-Error: " + response.status);
			return;
		}
		
		const json = await response.json();
//		console.log(json);

		// Error handling
		if (json.result.status == "Error")
		{
			$(".alert-danger").html(json.result.msg);
			$(".alert-danger").show();
			return;
		}
		
		$(".alert-success").html("Successfully Added!");
		$(".alert-success").show();

		json.data.forEach(device => {
			console.log(device.devId, device.devName);

			// Compose data for table used by Handlebars
			const content = { 
				id: device.id,
				loc: device.devName, 
				devId: device.devId, 
				active: device.active,
				dt: device.dt
			};
			console.log(content);

			// Put content into Handlebar Template
			let html = handle_template(content);
			$("#legend").append(html);
		});

		UpdateTime();
	}
	
		
	async function GetInitialData() 
	{
		const url = "getDevices.php";

		const response = await fetch(url);

		if (response.ok) { // if HTTP-status is 200-299
			const json = await response.json();
			let html = "";
			
			json.forEach(device => {
				console.log(device.devId, device.devName);

				// Compose data for table used by Handlebars
				const content = { 
					id: device.id,
					loc: device.devName, 
					devId: device.devId, 
					active: device.active,
					dt: device.dt
				};
				console.log(content);

				// Put content into Handlebar Template
				html += handle_template(content);


			});
			$("#legend").html(html);

			UpdateTime();
		} else {
			alert("HTTP-Error: " + response.status);
		}
	}		


	document.getElementById('addDevice').addEventListener('click', function() {
	
//		addData();
//		myTimer = setInterval(addData, 6000);
		getAdditionalData();
	});


	// Display current time on the page
	function UpdateTime()
	{
		const now = moment().format('HH:mm:ss');
		$("#updatedtime").html("Updated at " + now.toString());
	}
	
	// Handlebars Helper function
	Handlebars.registerHelper('isDeviceActive', function(value) {
		return value == 1;
	});
	

</script>
</body>

</html>
