<?php

# Confirm MW environment
if (!defined('MEDIAWIKI')) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

require_once('C:\Inetpub\wwwroot\mediawiki\extensions\mwCalendar\includes\Database.php');

class EventHandler{

	public function EventHandler(){}
	
	public function CheckForEvents(){
		$kenyu73_arr = split( '&', $_SERVER['REQUEST_URI'] );
		$kenyu73_url = $kenyu73_arr[0]; //clear any previous parameters

		// see if a new event was saved and apply changes to database
		if ( isset($_POST["add"]) ){
			
			//$date = date('D n/j/Y g:i A', 0);
			
			$start = strtotime($_POST["start"]);
			$end = strtotime($_POST["end"]);
			
			$arrEvent = array(	'calendar' => 	$_POST["calendar"],
								'subject' => 	$_POST["subject"],
								'location' => 	$_POST["location"],
								'start' => 		$start,
								'end' => 		$end,
								'allday' => 	($_POST["allday"] == 'on') ? 1:0,
								'text' => 		$_POST["text"] 
						);
			
			
			$db = new CalendarDatabase();
			$db->setEvent($arrEvent);
			
			// need to call new URL to clear POST events
			header("Location: " . $kenyu73_url . "&AddEvent");
		}
		
		if ( isset($_POST["delete"]) ){
			
			// return to main calendar page
			header("Location: " . $kenyu73_url . "&DeleteEvent");
		}		

		if ( isset($_POST["cancel"]) ){
			
			// return to main calendar page
			header("Location: " . $kenyu73_url);
		}

		if ( isset($_POST["new"]) ){
			
			// display add event page
			header("Location: " . $kenyu73_url . "&AddEvent");
		}
	}
}
