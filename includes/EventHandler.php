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
	
	public static function CheckForEvents(){
		global $wgUser,$wgOut;
		
		helpers::debug('Checking for POST events');
		
		$db = new CalendarDatabase();
		
		$arr = explode( '&', $_SERVER['REQUEST_URI'] );
		$url = $arr[0]; //clear any previous parameters
		
		// this is the active user (can be the creator... or the editor)
		$whodidit = $wgUser->getName();

		// see if a new event was saved and apply changes to database
		if ( isset($_POST["save"]) ){
			helpers::debug("POST: Event Saved");
			
			$arrEvent = self::buildEventArray();
					
			// are we updating or creating new?
			if($_POST['eventid']){
				$db->updateEvent($arrEvent, $_POST['eventid']);
			}else{
				$db->setEvent($arrEvent);
			}
			
			if( isset($_POST["invites"]) ){
				CalendarEmail::send($_POST["invites"], $arrEvent, 'save');
			}
			
			header("Location: " . $url);
		}

		if ( isset($_POST["savebatch"]) ){
			helpers::debug("POST: Batch Saved");
			self::addFromBatch($db, $whodidit);
			header("Location: " . $url);
		}	
		
		if ( isset($_POST["delete"])  ){
			helpers::debug("POST: Event Deleted");
			$db->deleteEvent($_POST['eventid']);
			
			$arrEvent = self::buildEventArray();
			
			if( isset($_POST["invites"]) ){
				CalendarEmail::send($_POST["invites"], $arrEvent, 'delete');
			}			
			header("Location: " . $url);
		}		

		if ( isset($_POST["cancel"]) ){	
			helpers::debug("POST: Event Cancelled");
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
						
			$cookie_name = helpers::cookie_name( $_POST['calendar']."_".$_POST['CalendarKey'] );
			setcookie($cookie_name, $timestamp);
			helpers::debug('Setting cookie: '.$cookie_name);
			
			helpers::debug("POST: Navigation Activated: $cookie_name, TIMESTAMP: $timestamp");
			header("Location: " . $url);
		}
	}
	
	## build events based on $_POST
	private static function buildEventArray(){
		global $wgUser;
		
		$whodidit = $wgUser->getName();
		
		$startTime = isset($_POST['timepicker1']) ? $_POST['timepicker1'] : '';
		$endTime = isset($_POST['timepicker2']) ? $_POST['timepicker2'] : '';
			
		$start = strtotime( $_POST["start"] . ' ' . $startTime);
		$end = strtotime($_POST["end"] . ' ' . $endTime);
								
		$subject = strip_tags ( $_POST["subject"], '<b><i><s><u>' );
		//$subject = $_POST["subject"];
		
		//if($subject == '') $subject ='INVALID Event';
		
		$arrInvites = helpers::invites_str_to_arr($_POST["invites"]);
		
		$arrEvent = array(	'id' => 			$_POST["eventid"],
							'calendar' => 		$_POST["calendar"],
							'subject' => 		$subject,
							'location' => 		$_POST["location"],
							'start' => 			$start,
							'end' => 			$end,
							'allday' => 		(isset($_POST["allday"])) ? 1:0,
							'text' => 			$_POST["text"],
							'createdby' => 		$whodidit,
							'editedby' => 		$whodidit,
							'invites' => 		serialize($arrInvites)
					);
					
		return $arrEvent;
	}
	
	private static function addFromBatch($db, $whodidit){
		$arrBatch = explode("\n", $_POST['batchdata']);
		
		$delimiter = $_POST['delimiter'];
		if($delimiter == '') $delimiter = '--';
		if($delimiter == 'tab') $delimiter = chr(9);

		foreach($arrBatch as $batch_event){
			$arr = explode($delimiter, $batch_event);
			
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
