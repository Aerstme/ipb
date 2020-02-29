<?php

/*
+--------------------------------------------------------------------------
|   Invision Download Manager
|   ========================================
|   by Brandon Farber
|   (c) 2005 - 2006 Invision Power Services
|   
|   ========================================
|   
|   
+---------------------------------------------------------------------------
|
|   > Wrapper Admin Module
|   > Script written by Brandon Farber
|   > Script Started: Oct 24, 2005 5:48 PM EST
|	> Last Updated On: Oct 24, 2005 5:48 PM EST
|
|	> Module Version .01
|
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

define( 'DL_ADMIN_PATH'	, ROOT_PATH.'sources/components_acp/downloads/' );
define( 'DL_PATH'		, ROOT_PATH.'sources/components_public/downloads/' );
define( 'DL_VERSION'	, '<#VERSION#>' );
define( 'DL_RVERSION'	, '<#VERSION_LONG#>'	);
define( 'DL_LINK'		, '' );

class ad_downloads {

	var $ipsclass;
	var $base_url;

		
	var $perm_main	= 'components';
	var $perm_child = 'downloads';		
	
	function auto_run()
	{ 
		//-----------------------------------------
		// Kill globals - globals bad, Homer good.
		//-----------------------------------------
		
		$tmp_in = array_merge( $_GET, $_POST, $_COOKIE );
		
		foreach ( $tmp_in as $k => $v )
		{
			unset($$k);
		}
		
		//-----------------------------------------
		// Did we purchase yet?
		//-----------------------------------------
		
		if ( ! @is_dir( ROOT_PATH.'/sources/components_acp/downloads' ) )
		{
			$this->ipsclass->admin->show_inframe("");
		}
		else
		{
			$this->base_url = $this->ipsclass->base_url."&".$this->ipsclass->form_code."&";
			
			$this->ipsclass->admin->page_title = "IP.Downloads";
			$this->ipsclass->admin->page_detail = "You can configure your Download Manager from this section.";
			$this->ipsclass->admin->nav[] = array( 'section=components&act=downloads', 'IP.Downloads' );
			
			$valid_reqs = array (
									'idx'					=> array( 'ad_downloads_index'	, ''			),
									'settings'				=> array( ''					, ''			),
									'mime'					=> array( 'ad_downloads_mime'	, ''			),
									'customfields'			=> array( 'ad_downloads_cfields', ''			),
									'categories'			=> array( 'ad_downloads_cats'	, ''			),
									'tools'					=> array( 'ad_downloads_tools'	, ''			),
									'stats'					=> array( 'ad_downloads_stats'	, ''			),
									'groups'				=> array( 'ad_downloads_groups'	, ''			),
							 	);

			$req = isset( $valid_reqs[ $this->ipsclass->input['req'] ] ) ? strtolower($this->ipsclass->input['req']) : 'idx';
	
			if ( $req == 'settings' )
			{
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':settings' );
				
				require_once( ROOT_PATH.'sources/action_admin/settings.php' );
				$settings             			=  new ad_settings();
				$settings->ipsclass   			=& $this->ipsclass;
				$settings->get_by_key        	= 'idm';
				$settings->return_after_save 	= $this->ipsclass->form_code.'&req=settings';
				$settings->setting_view();
			}
			else
			{
				// Require and run
		        require( DL_ADMIN_PATH . $valid_reqs[ $req ][0].'.php' );
		        $page = new $valid_reqs[ $req ][0];
		        $page->ipsclass =& $this->ipsclass;
		        $page->auto_run( $this, $valid_reqs[ $req ][1] );
			}
		}		
	}		
}

?>