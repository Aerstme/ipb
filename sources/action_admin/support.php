<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board
|   =============================================
|   by Matthew Mecham
|   (c) 2001 - 2006 Invision Power Services, Inc.
|   
|   =============================================
|   
|   
+---------------------------------------------------------------------------
|   > $Date: 2006-03-23 07:34:25 -0500 (Thu, 23 Mar 2006) $
|   > $Revision: 177 $
|   > $Author: brandon $
+---------------------------------------------------------------------------
|
|   > Support Module
|   > Module written by Brandon Farber
|   > Date started: 19th April 2006
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}


class ad_support
{
	var $base_url;
	
	/**
	* Section title name
	*
	* @var	string
	*/
	var $perm_main = "help";
	
	/**
	* Section title name
	*
	* @var	string
	*/
	var $perm_child = "support";
	
	function auto_run()
	{
		$this->ipsclass->admin->nav[] = array( $this->ipsclass->form_code, 'Support' );
		
		//-----------------------------------------

		switch($this->ipsclass->input['code'])
		{
			case 'doctor':
				$this->ipsclass->admin->page_detail = "Please utilize our documentation to discover how to use features found in the software.";
				$this->ipsclass->admin->page_title  = "Documentation";
				
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':view' );
				$this->ipsclass->admin->show_inframe( '' );
				break;
			break;
			
			case 'kb':
				$this->ipsclass->admin->page_detail = "Please utilize our knowledgebase to search for common issues and fixes to those issues.  You can also find documentation on how to use the features found in the software as well.";
				$this->ipsclass->admin->page_title  = "Help & Support";
				
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':view' );
				$this->ipsclass->admin->show_inframe( '' );
				break;
				
			case 'support':
				$this->ipsclass->admin->page_detail = "If you are experiencing an issue with your Invision Power Services software and require official assistance or support, you may utilize our ticketing system to submit a ticket.  Please allow 24-48 hours for a response during normal business hours.<br /><br /><i>You must have an active support contract with us in order to submit a ticket.</i>";
				$this->ipsclass->admin->page_title  = "Help & Support";
							
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':view' );
				$this->ipsclass->admin->show_inframe( '' );
				break;			
				
			case 'resources':
				$this->ipsclass->admin->page_detail = "resources.example.com is a customer-only resource site where you can find helpful articles, modifications, skins, and graphics for your forum.
														If you are looking for additional resources for your board, this is the first place to go.";
				$this->ipsclass->admin->page_title  = "Help & Support";
							
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':view' );
				$this->ipsclass->admin->show_inframe( '' );
				break;
				
			case 'contact':
				$this->ipsclass->admin->page_detail = "If you would like to contact us you can find our current contact information and hours of operation below.";
				$this->ipsclass->admin->page_title  = "Help & Support";
							
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':view' );
				$this->ipsclass->admin->show_inframe( '' );
				break;
				
			case 'features':
				$this->ipsclass->admin->page_detail = "If you would like to request a feature, or see if a particular feature has already been requested, for Invision Power Board this forum is the place to do so.";
				$this->ipsclass->admin->page_title  = "Help & Support";
							
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':view' );
				$this->ipsclass->admin->show_inframe( '' );
				break;
				
			case 'bugs':
				$this->ipsclass->admin->page_detail = "You may submit and track all bugs reported to us by our users for Invision Power Board in the Bugtracker below.";
				$this->ipsclass->admin->page_title  = "Help & Support";
							
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':view' );
				$this->ipsclass->admin->show_inframe( '' );
				break;				
				
				
			//-----------------------------------------
			default:
				$this->ipsclass->admin->page_detail = "If you are experiencing an issue with your Invision Power Services software and require official assistance or support, you may utilize our ticketing system to submit a ticket.  Please allow 24-48 hours for a response during normal business hours.<br /><br /><i>You must have an active support contract with us in order to submit a ticket.</i>";
				$this->ipsclass->admin->page_title  = "Help & Support";
			
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':view' );
				$this->ipsclass->admin->show_inframe( '' );
				break;
		}
	}
	
}


?>