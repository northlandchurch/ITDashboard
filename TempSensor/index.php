<?php
	include_once '../html_open.php';
	require_once 'config.php';
?>


<div class="col-md-12">
	<div class="card">
		<div class="card-body text-center">
			<h1>Temperature Monitoring System</h1>
		</div>
		<div class="card-body pb-0">
			<canvas id="chart1"></canvas>
		</div>
		<div id="updatedtimeDiv" class="card-body text-right pt-0 pb-0">
		<span class="badge badge-dark">Current Temperature</span>
		<br>
		<span id="updatedtime" class="font-weight-bold"></span>
		</div>
		<div class="card-body text-center">
			<table class="table" id="legendTable">
				<thead>
				<tr>
					<th scope="col">Show/Hide</th>
					<th scope="col">ID</th>
					<th scope="col">Name</th>
					<th scope="col">High Temp in 90 days</th>
					<th scope="col">Date</th>
				</tr>
			</thead>
			<tbody id="legend">
			</tbody>
			</table>
		</div>
		<div class="card-body text-center">
			<button id="refresh" class="btn btn-info">Refresh</button>
		</div>
	</div>
</div>
<br>


<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.js"></script>	
<script src="utils.js"></script>

<!--	<script src="https://cdn.jsdelivr.net/gh/mill1000/chartjs-plugin-annotation@v0.5.8/chartjs-plugin-annotation.min.js"></script>	-->
<script src="./js/chartjs-plugin-annotation-0.5.7/chartjs-plugin-annotation.js"></script>

<script src="https://cdn.jsdelivr.net/npm/handlebars@latest/dist/handlebars.js"></script>



<script id="legend-template" type="text/x-handlebars-template">
	<tr>
		<th scope="row"><input type="checkbox" value="{{index}}" checked></th>
		<td><span class="badge" style="background-color: {{color}}" >{{deviceId}}</span></td>
		<td>{{deviceName}}</td>
		<td id="hightemp{{id}}">{{hightemp}}</td>
		<td id="datetime{{id}}">{{datetime}}</td>
	</tr>				
</script>


<script>
	
	$(document).ready(function(){	
	
		$('#navbar-tempmonitor').addClass('active');

		$("#refresh").click(function() {
			$(".alert").hide();

			console.log("Timer: ");
			console.log(myTimer);
			clearInterval(myTimer);

			getInitialData();
		});
	});

	var chart;
	var myTimer;
	var colorNames = Object.keys(window.chartColors);

	var source = $("#legend-template").html();
	var handle_template = Handlebars.compile(source);
	
	getInitialData();
	
	async function getInitialData() 
	{
		const url = "getNewTemperatures.php?option=INIT";

		const response = await fetch(url);
		

		if (response.ok) { // if HTTP-status is 200-299
			$("#legend").html("")
			
			const json = await response.json();
			let tmpDatasets = [];
			var html = "";
			var number = 0;
			
			json.forEach(device => {
				console.log(device.devName + " (" + device.devId +  ")");
				console.log(device);
				// Get a new color
				var colorName = colorNames[tmpDatasets.length % colorNames.length];
				var newColor = window.chartColors[colorName];
				var newDataset = {
					label: device.devName,
					backgroundColor: newColor,
					borderColor: newColor,
					data: [],
					type: 'line',
					pointRadius: 0,
					fill: false,
					lineTension: 0,
					borderWidth: 2
				};

				// Compose data for table used by Handlebars
				let content = { 
					id: device.id,
					index: number, 
					deviceName: device.devName, 
					deviceId: device.devId, 
					color: newColor,
					hightemp: device.hightemp,
					datetime: device.datetime
				};
				html = handle_template(content);
				$("#legend").append(html);
				number++;

				// Parsing for the temperature and datetime
				let counter = 1;
				device.data.forEach(row => {
					const temp = row.temp;
					const datetime = row.dt;
//					console.log(counter++, datetime, temp);
//					let dt = moment(datetime, 'YYYY-MM-DD HH:mm').startOf('minute');
					let dt = moment(datetime, 'YYYY-MM-DD HH:mm');
//					console.log(dt.valueOf(), dt.toString());
					newDataset.data.push({
						t: dt.valueOf(),
						y: temp
					});
				});
				
				tmpDatasets.push(newDataset);
			});

//			console.log(tmpDatasets);
			initializeChart(tmpDatasets);

			// Add Eventhander for checkbox: Show/Hide a chart when checkbox clicked
			$("#legendTable :checkbox").change(function() {
				let index = $(this).val();
//				console.log("Value: " + index);
				let length = $("#legendTable :checkbox:checked").length;
				
				if (length < 1)
				{
					$(this).prop('checked', true);
					alert("You should select at least one!");
					return false;
				}
				
				if (this.checked) {
					chart.config.data.datasets[index].hidden = false;
					chart.update();
					
				} else {
					chart.config.data.datasets[index].hidden = true;
					chart.update();
				}
			});

			// Get Real-time data every 6 minutes
			myTimer = setInterval(getAdditionalData, 360000);

			// Display when updated
			var now = moment().format('HH:mm:ss');
			$("#updatedtime").html("Refreshed at " + now.toString());
//			console.log("Refreshed at " + now.toString());

		} else {
			alert("HTTP-Error: " + response.status);
		}
	}		


	function initializeChart(tmpdatasets)
	{
		var ctx = document.getElementById('chart1').getContext('2d');
		ctx.canvas.width = 1000;
		ctx.canvas.height = 400;

		const tmpData = tmpdatasets[0].data;
		const tmpLen = tmpData.length;

		var color = Chart.helpers.color;
		var cfg = {
			data: {
				datasets: tmpdatasets
			},
			options: {
				legend: {
					position: 'top',
				},
//				responsive: true,
				animation: {
					duration: 0
				},
				scales: {
					xAxes: [{
						type: 'time',
						distribution: 'series', 	// linear/series
						offset: true,
						gridLines: { 
							lineWidth: 1,
//							zeroLineWidth: 4,
//							zeroLineColor: 'rgba(0,0,0,255)',
						},						
						ticks: {
							major: {
								enabled: true,
//								fontSize: 14,
								fontStyle: 'bold',
							},
							minor: {
								enabled: true,
								fontStyle: 'bold'
							},
							source: 'data',
							autoSkip: true,
							autoSkipPadding: 75,
							maxRotation: 0,
							sampleSize: 100
						},
						afterBuildTicks: function(scale, ticks) {
							var majorUnit = scale._majorUnit;
							var firstTick = ticks[0];
							var i, ilen, val, tick, currMajor, lastMajor;
							console.log("majorUnit: " + majorUnit);

							val = moment(ticks[0].value);
//							console.log(ticks);
//							console.log(val.toString());
//							console.log(moment(ticks[ticks.length-1].value).toString());

							if ((majorUnit === 'minute' && val.second() === 0)
									|| (majorUnit === 'hour' && val.minute() === 0)
									|| (majorUnit === 'day' && val.hour() === 0)
									|| (majorUnit === 'month' && val.date() <= 3 && val.isoWeekday() === 1)
									|| (majorUnit === 'year' && val.month() === 0)) {
								firstTick.major = true;
							} else {
								firstTick.major = false;
							}
//							console.log(firstTick);

/*
							lastMajor = val.get(majorUnit);
							console.log("lastMajor: " + lastMajor);
							for (i = 1, ilen = ticks.length; i < ilen; i++) {
								tick = ticks[i];
								val = moment(tick.value);
								currMajor = val.get(majorUnit);
								tick.major = currMajor !== lastMajor;
								if (tick.major)
								{
									console.log(i + ": " + val.toString() + " CM: " + currMajor + "-LM: " + lastMajor);
								}
								lastMajor = currMajor;
							}
*/

							const tlen = ticks.length;
							for (i=tlen-1; i>tlen-12; i--)
							{
								val = moment(ticks[i].value);
//								console.log(i, val.minute());
								if (val.minute() == 0)
								{
									ticks[i]._index = i;
									ticks[i].minor = true;
									break;
								}
							}

							return ticks;
						}
					}],
					yAxes: [{
						gridLines: {
							drawBorder: false,
//							color: 'rgba(0, 0, 0, 255)',
							drawOnChartArea: false,
							lineWidth: 1,
						},
						position: 'right',
						ticks: {
							min: 60,
							max: 90,
							stepSize: 5,
//							callback: function(value, index, values) {
//								return value + '°';
//							}
						},
						scaleLabel: {
							display: true,
							labelString: 'Temperature (F)'
						}
					}, {
						gridLines: {
							drawBorder: false,
//							color: 'rgba(0, 0, 0, 255)',
//							drawOnChartArea: false,
						},
						position: 'left',
						ticks: {
							min: 60,
							max: 90,
							stepSize: 5,
//							callback: function(value, index, values) {
//								return value + '°';
//							}
						},
						scaleLabel: {
							display: true,
							labelString: 'Temperature (F)'
						}
					}]
				},
				tooltips: {
					intersect: false,
					mode: 'index',
					position: 'nearest',
					callbacks: {
						label: function(tooltipItem, myData) {
							var label = myData.datasets[tooltipItem.datasetIndex].label || '';
							if (label) {
								label += ': ';
							}
							label += parseFloat(tooltipItem.value).toFixed(2);
							return label;
						}
					}
				},
				annotation: {
					annotations: [{
						type: 'line',
						mode: 'horizontal',
						scaleID: 'y-axis-0',
//						value: 85,
						value: <?php echo TEMPTHRESHOLD; ?>,
						borderColor: 'rgb(255, 0, 0)',
						borderWidth: 3,
						label: {
							enabled: true,
							position: "center",
							content: 'Alarm Threshold'
						}
					},
					{
						type: 'line',
						mode: 'vertical',
						scaleID: 'x-axis-0',
//						value: '2021-03-04 00:00',
						value: tmpData[tmpLen-1].t,
						borderColor: 'yellow',
						borderWidth: 3,
						label: {
							enabled: false,
							position: "bottom",
							xAdjust: 68,
//							rotation: 90,
							content: 'Current Temperature',
						}
					}]
				}				

			}
		};

		chart = new Chart(ctx, cfg);
	}
	

	async function getAdditionalData() 
	{
		console.log(moment().toString());
		const url = "getNewTemperatures.php?option=ADD";

		const response = await fetch(url);

		if (response.ok) { // if HTTP-status is 200-299
			const json = await response.json();
			
			let counter = 0;
			json.forEach(device => {
				console.log(device);
			
				device.data.forEach(row => {
					console.log(row);
					const temp = row.temp;
					const datetime = row.dt;
					let dt = moment(datetime, 'YYYY-MM-DD HH:mm');

					let datasets = chart.config.data.datasets;
					for (let i=0; i<datasets.length; i++)
					{
						// Search for the right device 
						if (datasets[i].label == device.devName)
						{	
	//						console.log(device.id + ": " + device.devId + " - " + temp + " @ " + datetime);
							// Add a data at the end
							datasets[i].data.push({
								t: dt.valueOf(),
								y: temp
							});
						}
					}
				});	// End for device.data.forEach
				
				// Update high temp and datetime in the table
//				console.log ("High Temp: ", device.hightemp, device.datetime);
				$("#hightemp"+device.id).html(device.hightemp);
				$("#datetime"+device.id).html(device.datetime);
				
			});	// End for json.forEach(
			
			// Remove the first data
			let dtsets = chart.config.data.datasets;
			for (let i=0; i<dtsets.length; i++)
			{
				dtsets[i].data.shift();
			}
			
			chart.options.annotation.annotations[1].value = json[0].data[0].dt;
			console.log(chart.options.annotation.annotations[1].value);
			
//			const updatetime = mdtsets[0].data[0].dt;
		} else {
			alert("HTTP-Error: " + response.status);
		}
		
		chart.update();

		// Display when updated
		var now = moment().format('HH:mm:ss');
		$("#updatedtime").html("Refreshed at " + now.toString());
//		console.log("Refreshed at " + now.toString());
	}		

</script>
</body>

</html>
