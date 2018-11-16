<?php

class updatePlugins {
	//create a class for updating themes?
	public function __construct()
    {
    	add_action('init', array( $this, 'getJSON' ) );
    }

    public function getJSON() {
    	$dir = realpath(__DIR__ . DIRECTORY_SEPARATOR . '../..') . '/';

    	//scans the directory for files & removes the periods at the beginning
    	$plugins = array_slice(scandir($dir), 2);
    	
    	for ($count = 0; $count < count($plugins); $count++) {
    		//checks to see if there's a period in the element (aim was to remove files from list so it only gets the plugin directories)
    		if ( preg_match('/\./', $plugins[$count]) ) {
    			//if so, deletes it and changes the key
			    array_splice($plugins, $count, 1);
			}
    	}

		$this->currentPlugin = array();

    	for ($count=0; $count < count($plugins); $count++) { 
    		
			$currentPlugin = get_plugin_data( $dir . $plugins[$count] . '/' . $plugins[$count] . '.php' );
			
			//checks for plugins by RADD Creative
			if ( strpos($currentPlugin["Author"], 'RADD Creative') === false ) {
				//if false, deletes from the array
				array_splice($plugins, $count, 1);
			}
    	}

    	for ($count=0; $count < count($plugins); $count++) {
    		$currentPlugin = get_plugin_data( $dir . $plugins[$count] . '/' . $plugins[$count] . '.php' );

    		//removes Aion from the auto-update list
    		if ( strpos($currentPlugin["Name"], 'Aion') !== false ) {
    			array_splice($plugins, $count, 1);
    		}
    	}

		$url = 'https://pluginsite.test/';

		for ( $count = 0; $count < count($plugins); $count++ ) {

			$jsonUrl = $url . $plugins[$count] . '/' . $plugins[$count] . '.json';
			
			$zipUrl = $url . $plugins[$count] . '/' . $plugins[$count] . '.zip';

			//need this block for SSL/CORS
			$options=array(
			    "ssl"=>array(
			        "verify_peer"=>false,
			        "verify_peer_name"=>false,
			    ),
			);

			try {
			    $response = file_get_contents($jsonUrl, false, stream_context_create($options));

				$json = json_decode($response);

				$this->checkPlugin($json, $zipUrl);
			}
			catch( Exception $error ) {
			    echo $error->getMessage();
			}
		}
	}

	public function checkPlugin($json, $zipUrl) 
	{

		$dir = realpath(__DIR__ . DIRECTORY_SEPARATOR . '../..') . '/';
		$pluginName = strtolower(str_replace(' ','',$json->pluginName));

		$currentPlugin = get_plugin_data( $dir . $pluginName . '/' . $pluginName . '.php' );

		//checking if there's a newer version of the plugin on the remote site
		if ( $currentPlugin['Version'] < $json->version ) {
			//if yes, calls the function to download the new version
			$this->downloadPlugin($json, $currentPlugin, $zipUrl);
		}
		else {
			return;
		}
	}

	public function downloadPlugin($json, $currentPlugin, $zipUrl) 
	{

		//call the license class to check the license here?

		//need this block for SSL/CORS
		$options=array(
		    "ssl"=>array(
		        "verify_peer"=>false,
		        "verify_peer_name"=>false,
		    ),
		); 

		$file = strtolower(str_replace(' ','',$json->pluginName)) . ".zip";

		$remoteFile = file_get_contents($zipUrl, false, stream_context_create($options));
			
		$dir = realpath(__DIR__ . DIRECTORY_SEPARATOR . '../..') . '/';

		if ( !$remoteFile ) {
			return "No update available.";
		}
		else {
			try {
			   //downloads the plugin from the remote site
				file_put_contents($dir . $file, $remoteFile);
			}
			catch( Exception $error ) {
			    echo $error->getMessage();
			}
		}

		if ( !file_exists($dir . $file) ) {
			return "Unable to download plugin update.";
		}
		else
		{
			//if downloaded successfully, proceed with the upgrade
			$this->upgradePlugin($json, $currentPlugin, $dir);
		}
	}

	public function upgradePlugin($json, $currentPlugin, $dir) 
	{
		$pluginName = strtolower(str_replace(' ','',$json->pluginName));
		$newPlugin = $dir . $pluginName . ".zip";

		$upgrader = new WP_Upgrader();
		$args = array(
	        'package' => $dir . $pluginName . ".zip", // Please always pass this (name of the plugin file to install)
	        'destination' => $dir . $pluginName, // And this (where to install it to)
	        'clear_destination' => false,
	        'abort_if_destination_exists' => false, // Abort if the Destination directory exists, Pass clear_destination as false please (default is true; prevents plugin is already installed error)
	        'clear_working' => true,
	        'is_multi' => false,
	        'hook_extra' => array() // Pass any extra $hook_extra args here, this will be passed to any hooked filters.
	    );

		if (headers_sent()) {
		    die("Redirect failed. Please click on this link: <a href=...>");
		}

	    try {
	    	//upgrades plugin
	    	$upgrader->run($args);
	    	//activates plugin
			$activate = activate_plugin( $pluginName . "/" . $pluginName . ".php", null, false, true );
	    } 
	    catch (Exception $error) {
	    	echo $error->getMessage();
	    }

	    if ( !is_wp_error( $activate ) ) {
			$this->cleanup($newPlugin, $dir, $pluginName);
		}
		else {
			return;
		}
	}

	public function cleanup($newPlugin, $dir, $pluginName) 
	{
		//deletes the downloaded zip file
		if ( file_exists( $newPlugin ) ) {
			unlink( $newPlugin );
		}

		//deletes the included json file from the downloaded plugin
		if ( file_exists( $dir . $pluginName . "/" . $pluginName . ".json" ) ) 
		{
			unlink( $dir . $pluginName . "/" . $pluginName . ".json" );
		}
	}
}