<?php

class AionPlugin 
{
	function activate() 
	{
		//check for updates to this plugin?
		flush_rewrite_rules();
	}
	
	function deactivate() 
	{
		if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
		}

		$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
        check_admin_referer( "deactivate-plugin_{$plugin}" );
	}
	
	function uninstall() 
	{
		
	}
}