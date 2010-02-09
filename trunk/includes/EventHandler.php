<?php

# Confirm MW environment
if (!defined('MEDIAWIKI')) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

require_once('C:\Inetpub\wwwroot\mediawiki\extensions\mwCalendar\includes\Database.php');
require_once('C:\Inetpub\wwwroot\mediawiki\extensions\mwcalendar\includes\email.php');

class EventHandler{

	public function EventHandler(){}
	
	public static function CheckForEvents(){
		global $wgUser;
		
		$arr = explode( '&', $_SERVER['REQUEST_URI'] );
		$url = $arr[0]; //clear any previous parameters

		// see if a new event was saved and apply changes to database
		if ( isset($_POST["save"]) ){
			
			//$date = date('D n/j/Y g:i A', 0);
			
			$start = strtotime($_POST["start"]);
			$end = strtotime($_POST["end"]);
			
			$createdby = $wgUser->getName();
			
			$arrEvent = array(	'calendar' => 		$_POST["calendar"],
								'subject' => 		$_POST["subject"],
								'location' => 		$_POST["location"],
								'start' => 			$start,
								'end' => 			$end,
								'allday' => 		($_POST["allday"] == 'on') ? 1:0,
								'text' => 			$_POST["text"],
								'createdby' => 		$createdby,
								'invites' => 		$_POST["invites"] 
						);
						
			$db = new CalendarDatabase();
			
			// are we updating or creating new?
			if($_POST['eventid']){
				$db->updateEvent($arrEvent, $_POST['eventid']);
			}else{
				$db->setEvent($arrEvent);
			}
			
			if( isset($_POST["invites"]) ){
				CalendarEmail::send($_POST["invites"], $arrEvent);
			}
			
			// need to call new URL to clear POST events
			// return to calendar
			header("Location: " . $url);
			
		} ## END SAVE ##
		
		if ( isset($_POST["delete"]) ){
			
			// return to main calendar page
			header("Location: " . $url . "&DeleteEvent");
		}		

		if ( isset($_POST["cancel"]) ){
			
			// return to main calendar page
			header("Location: " . $url);
		}
		
		// timestamp will be populated if any nav butten is clicked
		if ( isset($_POST["timestamp"]) ){
			$month = $_POST['monthSelect'];
			$year = $_POST['yearSelect'];
			$today = getdate( time() );
		
			if( isset($_POST['monthForward']))	{$month +=1;}
			if( isset($_POST['monthBack']))		{$month -=1;}		
			if( isset($_POST['yearForward']))	{$year +=1;}	
			if( isset($_POST['yearBack']))		{$year -=1;}			
			
			$timestamp = mktime(0,0,0,$month,1,$year);
			setcookie($_POST['name'], $timestamp);
			
			header("Location: " . $url);
		}		
	}
}
