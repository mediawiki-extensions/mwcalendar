<?php

# Confirm MW environment
if (!defined('MEDIAWIKI')) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

require_once( mwcalendar_base_path . '/includes/Database.php');
require_once( mwcalendar_base_path . '/includes/email.php');
require_once( mwcalendar_base_path . '/includes/helpers.php');

class EventHandler{

	public function EventHandler(){}
	
	public static function CheckForEvents($can_update_db){
		global $wgUser,$wgOut;
		
		$db = new CalendarDatabase();
		
		$arr = explode( '&', $_SERVER['REQUEST_URI'] );
		$url = $arr[0]; //clear any previous parameters
		
		// this is the active user (can be the creator... or the editor)
		$whodidit = $wgUser->getName();

		// see if a new event was saved and apply changes to database
		if ( isset($_POST["save"]) && $can_update_db){

			$start = strtotime($_POST["start"]);
			$end = strtotime($_POST["end"]);
									
			$subject = strip_tags ( $_POST["subject"] );
			
			$arrInvites = helpers::invites_str_to_arr($_POST["invites"]);
			
			$arrEvent = array(	'calendar' => 		$_POST["calendar"],
								'subject' => 		$subject,
								'location' => 		$_POST["location"],
								'start' => 			$start,
								'end' => 			$end,
								'allday' => 		($_POST["allday"] == 'on') ? 1:0,
								'text' => 			$_POST["text"],
								'createdby' => 		$whodidit,
								'editedby' => 		$whodidit,
								'invites' => 		serialize($arrInvites)
						);
						
			// are we updating or creating new?
			if($_POST['eventid']){
				$db->updateEvent($arrEvent, $_POST['eventid']);
			}else{
				$db->setEvent($arrEvent);
			}
			
			if( isset($_POST["invites"]) ){
				CalendarEmail::send($_POST["invites"], $arrEvent);
			}
			
			header("Location: " . $url);
		}

		if ( isset($_POST["savebatch"]) && $can_update_db ){
			self::addFromBatch($db, $whodidit);
			header("Location: " . $url);
		}	
		
		if ( isset($_POST["delete"]) ){
			$db->deleteEvent($_POST['eventid']);
			header("Location: " . $url);
		}		

		if ( isset($_POST["cancel"]) ){	
			header("Location: " . $url);
		}
		
		// timestamp will be populated only if any nav butten is clicked
		if ( isset($_POST["timestamp"]) ){
			$month = $_POST['monthSelect'];
			$year = $_POST['yearSelect'];
		
			if( isset($_POST['monthForward']))	{$month +=1;}
			if( isset($_POST['monthBack']))		{$month -=1;}		
			if( isset($_POST['yearForward']))	{$year +=1;}	
			if( isset($_POST['yearBack']))		{$year -=1;}			
			
			if(isset($_POST['today'])){
				$timestamp = time(); //now
			}else{
				$timestamp = mktime(0,0,0,$month,1,$year); //modified date
			}
			
			$cookie_name = helpers::cookie_name( $_POST['name'] );
			setcookie($cookie_name, $timestamp);
			
			header("Location: " . $url);
		}
	}
	
	private static function addFromBatch($db, $whodidit){
		$arrBatch = explode("\n", $_POST['batchdata']);

		foreach($arrBatch as $batch_event){
			$arr = explode('--',$batch_event);
			
			// add current month if the value is "2", "15", "28", etc
			if(strlen($arr[0])< 3){
				$date = getdate();
				$arr[0] = $date['mon'] . '/'. $arr[0];
			}
					
			$start = $end = strtotime($arr[0]);
			
			//just kludge to make sure we have a valid date
			if( $start < 1000 ) return '';
			
			$arrEvent = array(	'calendar' => 		$_POST["calendar"],
								'subject' => 		$arr[1],
								'location' => 		'',
								'start' => 			$start,
								'end' => 			$end,
								'allday' => 		1,
								'text' => 			'',
								'createdby' => 		$whodidit,
								'editedby' => 		$whodidit,
								'invites' => 		''
						);		
			$db->setEvent($arrEvent);
		}
	}
}
