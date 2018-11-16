// jQuery(document).ready(function ($) {

// 	var url = "https://pluginsite.test/";
// 	var plugins = [ 
// 		'testplugin',
// 	];

// 	for ( var count = 0; count < plugins.length; count++ ) {

// 		var json_url = url + plugins[count] + "/" + plugins[count] + ".json";

// 		var req = new XMLHttpRequest();
// 		req.overrideMimeType("application/json");
// 		req.open('GET', json_url, true);
// 		req.onload  = function() {
// 		   var jsonResponse = JSON.parse(req.responseText);
// 		   console.log(jsonResponse);
// 		};
// 		req.send();

// 		var response = file_get_contents(json_url);


// 	}

// });