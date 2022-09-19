<?php
	require_once '../config.php';
?>
<?php
	include_once '../html_open.php';
?>


<div class="row m-4">
	<div class="col-md-12">
		<h1 class="text-center">IT Access</h1>
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
						<th scope="col">id</th>
						<th scope="col">System</th>
						<th scope="col">EntryPoint</th>
						<th scope="col">User</th>
						<th scope="col">Account</th>
						<th scope="col">Password</th>
                                                <th scope="col">EnteredBy</th>
                                                <th scope="col">Notes</th>
                                                <th scope="col">LastChangeDate</th>
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
		<td>{{id}}</td>
		<td>{{System}}</td>
		<td>{{EntryPoint}}</td>
		<td>{{User}}</td>
		<td>{{Account}}</td>
		<td>{{Password}}</td>
                <td>{{EnteredBy}}</td>
		<td>{{Notes}}</td>
		<td>{{LastChangeDate}}</td>
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

		const parameters = "action=filter&text=" + $("#inputText").val();

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
					System: change.System,
					EntryPoint: change.EntryPoint,
					User: change.User,
					Account: change.Account,
					Password: change.Password,
					EnteredBy: change.EnteredBy,
					Notes: change.Notes,
					LastChangeDate: change.LastChangeDate,
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
	$("#updatedtime").html(count + " records returned at " + now.toString());
}



</script>
</body>

</html>
