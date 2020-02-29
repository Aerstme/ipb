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
|   > $Date: 2007-04-18 08:56:02 -0400 (Wed, 18 Apr 2007) $
|   > $Revision: 944 $
|   > $Author: bfarber $
+---------------------------------------------------------------------------
|
|   > Topic Tracker module
|   > Module written by Matt Mecham
|   > Date started: 6th March 2002
|
|	> Module Version Number: 1.0.0
|   > DBA Checked: Mon 24th May 2004
+--------------------------------------------------------------------------
*/


if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class stats {

    var $output    = "";
    var $base_url  = "";
    var $html      = "";
	var $forum     = "";
	
    function auto_run() {
    
    	//-----------------------------------------
    	// $is_sub is a boolean operator.
    	// If set to 1, we don't show the "topic subscribed" page
    	// we simply end the subroutine and let the caller finish
    	// up for us.
    	//-----------------------------------------
    
        $this->ipsclass->load_language('lang_stats');
    	$this->ipsclass->load_template('skin_stats');
    	
    	$this->base_url = $this->ipsclass->base_url;
    	
    	//-----------------------------------------
    	// What to do?
    	//-----------------------------------------
    	
    	switch($this->ipsclass->input['CODE'])
    	{
    		case 'leaders':
    			$this->show_leaders();
    			break;
    		case '02':
    			//$this->do_search();
    			break;
    			
    		case 'who':
    			$this->who_posted();
    			break;
    			
    		default:
    			$this->show_today_posters();
    			break;
    	}
    	
    	// If we have any HTML to print, do so...
    	
    	$this->ipsclass->print->add_output("$this->output");
        $this->ipsclass->print->do_output( array( 'TITLE' => $this->page_title, 'JS' => 0, 'NAV' => $this->nav ) );
    		
 	}
 	
 	function who_posted()
 	{
		$tid = intval(trim($this->ipsclass->input['t']));
 		
 		$to_print = "";
 		
 		$this->check_access($tid);
 		
 		$this->ipsclass->DB->cache_add_query( 'stats_who_posted', array( 'tid' => $tid ) );
		$this->ipsclass->DB->cache_exec_query();
 		
 		if ( $this->ipsclass->DB->get_num_rows() )
 		{
 		
 			$to_print = $this->ipsclass->compiled_templates['skin_stats']->who_header($this->forum['id'], $tid, $this->forum['topic_title']);
 			
 			while( $r = $this->ipsclass->DB->fetch_row() )
 			{
 				if ($r['author_id'])
 				{
 					$r['author_name'] = $this->ipsclass->compiled_templates['skin_stats']->who_name_link($r['author_id'], $r['author_name']);
 				}
 				
 				$to_print .= $this->ipsclass->compiled_templates['skin_stats']->who_row($r);
 			}
 			
 			$to_print .= $this->ipsclass->compiled_templates['skin_stats']->who_end();
 		}
 		else
 		{
 			$this->ipsclass->Error( array( 'LEVEL' => 1, 'MSG' => 'missing_files') );
 		}
 		
 		$this->ipsclass->print->pop_up_window("",$to_print);
 		
 		exit();
 	}
 	
 	//-----------------------------------------
 	
 	function check_access($tid)
    {
		
 		
		
		//if ( ! $this->ipsclass->member['id'] )
		//{
		//	$this->ipsclass->Error( array( 'LEVEL' => 1, 'MSG' => 'no_permission') );
		//}
		
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => 't.*,t.title as topic_title', 'from' => 'topics t', 'where' => "t.tid=".$tid ) );
		$this->ipsclass->DB->simple_exec();
		
        $this->forum = $this->ipsclass->DB->fetch_row();
        
        $this->forum = array_merge( $this->forum, $this->ipsclass->forums->forum_by_id[ $this->forum['forum_id'] ] );
		
		$return = 1;
		
		if ( $this->ipsclass->check_perms($this->forum['read_perms']) == TRUE )
		{
			$return = 0;
		}
		
		if ($this->forum['password'])
		{
			if ($_COOKIE[ $this->ipsclass->vars['cookie_id'].'iBForum'.$this->forum['id'] ] == $this->forum['password'])
			{
				$return = 0;
			}
		}
		
		if ($return == 1)
		{
			$this->ipsclass->Error( array( 'LEVEL' => 1, 'MSG' => 'no_permission') );
		}
	
	}
 	
 	/*-------------------------------------------------------------------------*/
 	// SHOW FORUM LEADERS
 	/*-------------------------------------------------------------------------*/
 	
 	function show_leaders()
 	{
		//-----------------------------------------
    	// Work out where our super mods / admins/ mods
    	// are.....
    	//-----------------------------------------
    	
    	$group_ids  = array();
    	$member_ids = array();
    	$used_ids   = array();
    	$members    = array();
    	$moderators = array();
    	
		foreach( $this->ipsclass->cache['group_cache'] as $i )
		{
			if ( $i['g_is_supmod'] )
			{
				$group_ids[ $i['g_id'] ] = $i['g_id'];
			}
			
			if ( $i['g_access_cp'] )
			{
				$group_ids[ $i['g_id'] ] = $i['g_id'];
			}
		}
		
		foreach( $this->ipsclass->cache['moderators'] as $i )
		{
			if ( $i['is_group'] )
			{
				$group_ids[ $i['group_id'] ] = $i['group_id'];
			}
			else
			{
				$member_ids[ $i['member_id'] ] = $i['member_id'];
			}
		}
    	
    	//-----------------------------------------
    	// Get all members.. (two is more eff. than 1)
    	//-----------------------------------------
    	
    	if ( count( $member_ids ) )
    	{
			$this->ipsclass->DB->cache_add_query( 'stats_get_all_members', array( 'member_ids' => $member_ids ) );
			$this->ipsclass->DB->cache_exec_query();
			
			while( $r = $this->ipsclass->DB->fetch_row() )
			{
				$members[ strtolower($r['members_display_name']) ] = $r;
			}
    	}
    	
    	//-----------------------------------------
    	// Get all groups.. (two is more eff. than 1)
    	//-----------------------------------------
    	
    	$this->ipsclass->DB->cache_add_query( 'stats_get_all_members_groups', array( 'group_ids' => $group_ids ) );
    	$this->ipsclass->DB->cache_exec_query();
    	
    	while( $r = $this->ipsclass->DB->fetch_row() )
    	{
    		$members[ strtolower($r['members_display_name']) ] = $r;
    	}
    	
    	ksort($members);
    	
    	//-----------------------------------------
    	// PRINT: Admins
    	//-----------------------------------------
    	
    	$this->output .= $this->ipsclass->compiled_templates['skin_stats']->group_strip( $this->ipsclass->lang['leader_admins'] );
    	
    	foreach( $members as $member )
    	{
    		if ( $this->ipsclass->cache['group_cache'][ $member['mgroup'] ]['g_access_cp'] )
    		{
    			$this->output .= $this->ipsclass->compiled_templates['skin_stats']->leader_row( $this->parse_member( $member ), $this->ipsclass->lang['leader_all_forums'] );
    			
    			//-----------------------------------------
    			// Used...
    			//-----------------------------------------
    			
    			$used_ids[] = $member['id'];
    		}
    	}
    	
    	$this->output .= $this->ipsclass->compiled_templates['skin_stats']->close_strip();
    	
    	//-----------------------------------------
    	// PRINT: Super Moderators
    	//-----------------------------------------
    	
    	$tmp_html = "";
    	
    	foreach( $members as $member )
    	{
    		if ( $this->ipsclass->cache['group_cache'][ $member['mgroup'] ]['g_is_supmod'] and ( ! in_array( $member['id'], $used_ids) ) )
    		{
    			$tmp_html .= $this->ipsclass->compiled_templates['skin_stats']->leader_row( $this->parse_member( $member ), $this->ipsclass->lang['leader_all_forums'] );
    			
    			//-----------------------------------------
    			// Used...
    			//-----------------------------------------
    			
    			$used_ids[] = $member['id'];
    		}
    	}
    	
		if ( $tmp_html )
		{
			$this->output .= $this->ipsclass->compiled_templates['skin_stats']->group_strip( $this->ipsclass->lang['leader_global'] );
			$this->output .= $tmp_html;
			$this->output .= $this->ipsclass->compiled_templates['skin_stats']->close_strip();
		}
		
		//-----------------------------------------
    	// GET MODERATORS: Normal
    	//-----------------------------------------
    	
    	$tmp_html = "";
    	
    	foreach( $members as $member )
    	{
    		if ( ! in_array( $member['id'], $used_ids) ) 
    		{
    			foreach( $this->ipsclass->cache['moderators'] as $data )
    			{
    				if ( $data['is_group'] and $data['group_id'] == $member['mgroup'] )
    				{
    					if ( $this->ipsclass->check_perms( $this->ipsclass->forums->forum_by_id[ $data['forum_id'] ]['read_perms'] ) == TRUE )
    					{
    						$moderators[] = array_merge( $member, array( 'forum_id' => $data['forum_id'] ) );
    					}
    					
    					$used_ids[] = $member['id'];
    				}
    				else if ( $data['member_id'] == $member['id'] )
    				{
    					if ( $this->ipsclass->check_perms( $this->ipsclass->forums->forum_by_id[ $data['forum_id'] ]['read_perms'] ) == TRUE )
    					{
    						$moderators[] = array_merge( $member, array( 'forum_id' => $data['forum_id'] ) );
    					}
    					
    					$used_ids[] = $member['id'];
    				}
    			}
    		}
    	}
    	
		//-----------------------------------------
		// Parse moderators
		//-----------------------------------------
    	
    	if ( count($moderators) > 0 )
    	{
    		$mod_array = array();
    		
    		$this->output .= $this->ipsclass->compiled_templates['skin_stats']->group_strip( $this->ipsclass->lang['leader_mods'] );
    		
    		foreach ( $moderators as $i )
    		{
    			if ( ! isset( $mod_array['member'][ $i['id'] ][ 'name' ] ) )
    			{
    				//-----------------------------------------
    				// Member is not already set, lets add the member...
    				//-----------------------------------------
    				
    				$mod_array['member'][ $i['id'] ] = array( 'members_display_name' => $i['members_display_name'],
    														  'email'      => $i['email'],
    														  'hide_email' => $i['hide_email'],
    														  'location'   => $i['location'],
    														  'aim_name'   => $i['aim_name'],
    														  'icq_number' => $i['icq_number'],
    														  'id'         => $i['id']
    														);
    														
    			}
    			
    			//-----------------------------------------
    			// Add forum..	
    			//-----------------------------------------
    			
    			$mod_array['forums'][ $i['id'] ][] = array( $i['forum_id'] , $this->ipsclass->forums->forum_by_id[ $i['forum_id'] ]['name'] );
    		}
    		
    		foreach( $mod_array['member'] as $id => $data )
    		{
    			$fhtml = "";
    			
    			if ( count( $mod_array['forums'][ $id ] ) > 1 )
    			{
    				$cnt   = count( $mod_array['forums'][ $id ] );
    				$fhtml = $this->ipsclass->compiled_templates['skin_stats']->leader_row_forum_start($id, sprintf( $this->ipsclass->lang['no_forums'],  $cnt ) );
    				
    				foreach( $mod_array['forums'][ $id ] as $data )
    				{
    					$fhtml .= $this->ipsclass->compiled_templates['skin_stats']->leader_row_forum_entry($data[0],$data[1]);
    				}
    				
    				$fhtml .= $this->ipsclass->compiled_templates['skin_stats']->leader_row_forum_end();
    			}
    			else
    			{
    				$fhtml = "<a href='{$this->ipsclass->base_url}showforum=".$mod_array['forums'][ $id ][0][0]."'>".$mod_array['forums'][ $id ][0][1]."</a>";
    			}
    					
    					
    			$this->output .= $this->ipsclass->compiled_templates['skin_stats']->leader_row( 
														   $this->parse_member( $mod_array['member'][ $id ] ),
														   $fhtml
														);
    		}
    		
    		$this->output .= $this->ipsclass->compiled_templates['skin_stats']->close_strip();
    		
    	}
    	
    	$this->page_title = $this->ipsclass->lang['forum_leaders'];
    	$this->nav        = array( $this->ipsclass->lang['forum_leaders'] );
 	}
 	
 
 	
 	/*-------------------------------------------------------------------------*/
 	// Top 10 Posters
 	/*-------------------------------------------------------------------------*/
 	
 	function show_today_posters()
 	{
		$this->output .= $this->ipsclass->compiled_templates['skin_stats']->top_poster_header();
 		
 		$time_high = time();
 		$ids       = array();
 		$time_low  = $time_high - (60*60*24);
 		
 		//-----------------------------------------
		// Query the DB
		//-----------------------------------------
	
		foreach( $this->ipsclass->forums->forum_by_id as $id => $data )
		{
			if ( ! $data['inc_postcount'] )
			{
				continue;
			}
		
			$ids[] = $id;
		}

		if( count( $ids ) )
		{
	    	$todays_posts = 0;
	
			$store = array();
	
			$this->ipsclass->DB->build_query( array( 'select' 	=> 'count(*) as cnt',
													 'from' 	=> array( 'posts' => 'p' ),
													 'where' 	=> "p.post_date > {$time_low} AND t.forum_id IN(".implode(",",$ids).")",
													 'add_join' => array( 0 => array( 'from'	=> array( 'topics' => 't' ),
													 								  'where'	=> 't.tid=p.topic_id',
													 								  'type'	=> 'left' )
													 					)
											 ) 		);
			$this->ipsclass->DB->exec_query();
	
			$total_today = $this->ipsclass->DB->fetch_row();
	
			$this->ipsclass->DB->cache_add_query( 'stats_get_todays_posters', array( 'ids' => $ids, 'time_low' => $time_low ) );
			$this->ipsclass->DB->cache_exec_query();
    	
			while ($r = $this->ipsclass->DB->fetch_row())
			{
				$todays_posts += $r['tpost'];
			
				$store[] = $r;
			}
		
			if ( $todays_posts )
			{
				foreach( $store as $info )
				{		
					$info['total_today_posts'] = $todays_posts;
				
					if ($todays_posts > 0 and $info['tpost'] > 0)
					{
						$info['today_pct'] = sprintf( '%.2f',  ( $info['tpost'] / $total_today['cnt'] ) * 100  );
					}
				
					$info['joined']  = $this->ipsclass->get_date( $info['joined'], 'JOINED' );
				
					$info['posts'] = $this->ipsclass->do_number_format($info['posts']);
					$info['tpost'] = $this->ipsclass->do_number_format($info['tpost']);
					
					$info['members_display_name'] = $info['members_display_name'] ? $info['members_display_name'] : $this->ipsclass->lang['global_guestname'];
				
					$this->output .= $this->ipsclass->compiled_templates['skin_stats']->top_poster_row( $info );
				}
			}
			else
			{
				$this->output .= $this->ipsclass->compiled_templates['skin_stats']->top_poster_no_info();
			}
		}
		else
		{
			$this->output .= $this->ipsclass->compiled_templates['skin_stats']->top_poster_no_info();
		}

		
		$this->output .= $this->ipsclass->compiled_templates['skin_stats']->top_poster_footer();
		
		$this->page_title = $this->ipsclass->lang['top_poster_title'];
		
		$this->nav = array( $this->ipsclass->lang['top_poster_title'] );
		
	}
	

		
		
	
//-----------------------------------------

	function parse_member( $member )
	{
		$member['msg_icon'] = "<a href='{$this->ipsclass->base_url}act=Msg&amp;CODE=04&amp;MID={$member['id']}'><{P_MSG}></a>";
			
		if (!$member['hide_email'])
		{
			$member['email_icon'] = "<a href='{$this->ipsclass->base_url}act=Mail&amp;CODE=00&amp;MID={$member['id']}'><{P_EMAIL}></a>";
		}
		else
		{
			$member['email_icon'] = '&nbsp;';
		}
		
		if ($member['icq_number'])
		{
			$member['icq_icon'] = "<a href=\"javascript:PopUp('{$this->ipsclass->base_url}act=ICQ&amp;MID={$member['id']}','Pager','450','330','0','1','1','1')\"><{P_ICQ}></a>";
		}
		else
		{
			$member['icq_icon'] = '&nbsp;';
		}
		
		if ($member['aim_name'])
		{
			$member['aol_icon'] = "<a href=\"javascript:PopUp('{$this->ipsclass->base_url}act=AOL&amp;MID={$member['id']}','Pager','450','330','0','1','1','1')\"><{P_AOL}></a>";
		}
		else
		{
			$member['aol_icon'] = '&nbsp;';
		}
				
			return $member;
		
	}
	

	
}

?>