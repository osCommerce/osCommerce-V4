/*
 * charts/chart_multiple.js
 *
 * Demo JavaScript used on charts-page for "Multiple Statistics".
 */

"use strict";

$(document).ready(function(){

	// Sample Data


	// Random data for "Server load"



/*	for (var x = 0; x < 200; x+=13) {
		var y = Math.floor( 50 - 15 + Math.random() * 30 );
		data_server_load.push([x, y]);
	}
	console.log(data_server_load);
	for (var x = 0; x < 200; x+=10) {
		var y = Math.floor( 50 - 15 + Math.random() * 30 );
		data_red.push([x, y]);
	}
	for (var x = 0; x < 200; x+=15) {
		var y = Math.floor( 50 - 15 + Math.random() * 30 );
		data_used_ram.push([x, y]);
	}*/

	var series_multiple = [
		{
			label: "Total orders",
			data: data_blue,
			color: '#0060bf',
			lines: {
				fill: true,
				fillColor: {  colors: ['rgba(148,175,252,0.1)', 'rgba(148,175,252,0.45)'] }
			},
			points: {
				show: true
			},
			yaxis : 1
		},{
			label: "Average order amount",
			data: data_green,
			color: '#1bb901',
			yaxis : 2
		},{
			label: "Total amount",
			data: data_red,
			color: '#f43c11',
			yaxis : 3
		}
	];
var someFunc = function(val, axis){
   return "&pound;" + Math.ceil(val) + '<span class="sep">/</span>';
}
var someFunc1 = function(val, axis){
   return Math.ceil(val);
}
	// Initialize flot
	var plot = $.plot("#chart_multiple", series_multiple, $.extend(true, {}, Plugins.getFlotDefaults(), {
	yaxes: [ {
			position : 'left',
			tickFormatter: someFunc1,
		}, {
			position : 'left',
			alignTicksWithAxis : 1,
			tickFormatter: someFunc,
			color:'transparent',
			axisMargin:100
		},{
			position : 'left',
			alignTicksWithAxis : 1,
			tickFormatter: someFunc,
			color:'transparent'

		} ],
		xaxis: {
			// min: (new Date(2015,1,1)).getTime(),
			// max: (new Date(2016,1,2)).getTime(),
			mode: "time"
/* 			tickSize: [1, "month"],
			monthNames: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
			tickLength: 1 */
		},
		series: {
			lines: { show: true },
			points: { show: true },
			grow: { active: true }
		},
		grid: {
			hoverable: true,
			clickable: true,
			axisMargin: -10
		},
		tooltip: true,
		tooltipOpts: {
			content: '%s: %y'
		}
		
	}));

});