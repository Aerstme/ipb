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
|   > $Date: 2006-09-22 06:28:31 -0400 (Fri, 22 Sep 2006) $
|   > $Revision: 567 $
|   > $Author: matt $
+---------------------------------------------------------------------------
|
|   > IPS Remote Call thingy
|   > Module written by Matt Mecham
|   > Date started: 17th October 2002
|
|	> Module Version Number: 1.0.0
|   > DBA Checked: Tue 25th May 2004
+--------------------------------------------------------------------------
*/


if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}


class ad_ips {

	var $base_url;
	
	var $colours = array();
	
	var $url = "http://www.google.com/";
	
	var $version = "1.1";

	function auto_run()
	{
		//-----------------------------------------
		
		switch($this->ipsclass->input['code'])
		{
		
		
				
			case 'docs':
				$this->docs();
				break;
				
		
				
			//-----------------------------------------
			default:
				exit();
				break;
		}
		
	}
	


	
	
	
	function docs()
	{
		@header("Location: http://www.google.com");
		exit();
	}
	

}
?>