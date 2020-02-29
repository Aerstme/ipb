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
|   > $Date: 2007-12-27 14:32:48 -0500 (Thu, 27 Dec 2007) $
|   > $Revision: 1151 $
|   > $Author: bfarber $
+---------------------------------------------------------------------------
|
|   > TASK SCRIPT: Test
|   > Script written by Matt Mecham
|   > Date started: 28th January 2004
|
+--------------------------------------------------------------------------
*/

//+--------------------------------------------------------------------------
// THIS TASKS OPERATIONS:
// Sends out an email if the subspackage is about to expire
//+--------------------------------------------------------------------------

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class task_item
{
	var $class     = "";
	var $root_path = "";
	var $task      = "";
	
	/*-------------------------------------------------------------------------*/
	// Our 'auto_run' function
	// ADD CODE HERE
	/*-------------------------------------------------------------------------*/
	
	function run_task()
	{
		//-----------------------------------------
		// GET EMAIL CLASS
		//-----------------------------------------
		
		require ( $this->root_path."sources/classes/class_email.php" );
		$this->email = new emailer( $this->root_path );
        $this->email->ipsclass =& $this->ipsclass;
        $this->email->email_init();
        
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$members = array();
		$ids     = array();
		$expired = time() + 86400 + 3600; // Tomorrow + 1 hour
		$now     = time() - 3600;
		
		//-----------------------------------------
		// Get members
		//-----------------------------------------
		
		$this->ipsclass->DB->build_query( array( 'select'   => 'st.subtrans_member_id',
												 'from'     => array( 'subscription_trans' => 'st' ),
												 'where'    => "st.subtrans_state='paid' AND st.subtrans_end_date >= $now AND st.subtrans_end_date <= $expired",
												 'add_join' => array( 0 => array( 'select' => 'm.id, m.name, m.members_display_name, m.email, m.sub_end',
																				  'from'   => array( 'members' => 'm' ),
																				  'where'  => 'm.id=st.subtrans_member_id',
																				  'type'   => 'left' ) ) ) );
	
		$this->ipsclass->DB->exec_query();
			
		while( $r = $this->ipsclass->DB->fetch_row() )
		{
			$members[ $r['id'] ] = $r;
			$ids[] = $r['id'];
		}
		
		//-----------------------------------------
		// Get subscription packages
		//-----------------------------------------
		
		if ( count( $ids ) )
		{
			$this->ipsclass->DB->build_query( array( 'select'   => 'st.subtrans_sub_id, st.subtrans_member_id',
													 'from'     => array( 'subscription_trans' => 'st' ),
													 'where'    => 'st.subtrans_member_id IN ('.implode( ",", $ids ) . ") AND st.subtrans_state='paid'",
													 'add_join' => array( 
													 						0 => array( 'select' => 's.sub_title',
																					  'from'   => array( 'subscriptions' => 's' ),
																					  'where'  => 's.sub_id=st.subtrans_sub_id',
																					  'type'   => 'left' 
																					  ),
																			1 => array( 'select' => 'se.subextra_recurring',
																					  'from'   => array( 'subscription_extra' => 'se' ),
																					  'where'  => 'se.subextra_sub_id=s.sub_id',
																					  'type'   => 'left' 
																					  ),
																		) 
											) 		);
		
			$this->ipsclass->DB->exec_query();
			
			while( $r = $this->ipsclass->DB->fetch_row() )
			{
				$members[ $r['subtrans_member_id'] ]['sub_title'] = $r['sub_title'];
				$members[ $r['subtrans_member_id'] ]['subextra_recurring'] = $r['subextra_recurring'];
			}
		
			//-----------------------------------------
			// Send out the EMAILS
			//-----------------------------------------
			
			foreach( $members as $member )
			{
				if( $member['subextra_recurring'] )
				{
					$this->email->get_template("subscription_expires_recurring");
				}
				else
				{
					$this->email->get_template("subscription_expires");
				}
				
				$this->email->build_message( array(
													'PACKAGE'  => $member['sub_title'],
													'EXPIRES'  => $this->ipsclass->get_date( $member['sub_end'], 'DATE' ),
													'LINK'     => $this->ipsclass->vars['board_url'].'/index.'.$this->ipsclass->vars['php_ext'].'?act=paysubs&CODE=index',
										   )     );
				
				$this->email->to = trim( $member['email'] );
				$this->email->send_mail();
			}
		}
		
		//-----------------------------------------
		// Log to log table - modify but dont delete
		//-----------------------------------------
		
		$this->class->append_task_log( $this->task, intval(count($ids)).' Members sent an expiration email' );
		
		//-----------------------------------------
		// Unlock Task: DO NOT MODIFY!
		//-----------------------------------------
		
		$this->class->unlock_task( $this->task );
	}
	
	/*-------------------------------------------------------------------------*/
	// register_class
	// LEAVE ALONE
	/*-------------------------------------------------------------------------*/
	
	function register_class(&$class)
	{
		$this->class     = &$class;
		$this->ipsclass  =& $class->ipsclass;
		$this->root_path = $this->class->root_path;
	}
	
	/*-------------------------------------------------------------------------*/
	// pass_task
	// LEAVE ALONE
	/*-------------------------------------------------------------------------*/
	
	function pass_task( $this_task )
	{
		$this->task = $this_task;
	}
	
	
}
?>