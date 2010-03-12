<?php

require_once( mwcalendar_base_path . '/includes/helpers.php');

class CalendarEmail{
	
	public static function send($users, $event, $action='save'){
		global $wgUser;
		
		#todo: might filter out users without an email...
		$fromEmail = $wgUser->getEmail();
		
		$arr = explode(',',$users);
		$arr = array_unique($arr); //remove duplicates
		
		$subject = $event['subject'];
		$start = helpers::date($event['start']) . ' ' . helpers::time($event['start']);
		$end = helpers::date($event['end']) . ' ' . helpers::time($event['end']);
		
		$arrUrl = explode( '&', $_SERVER['REQUEST_URI'] );
		$urlPath = $arrUrl[0]; //clear any previous parameters	
		
		
		$body = 
			"FROM: $start" . chr(13) . 
			"TO: $end" . chr(13) . chr(13) . 
			"Location: " . $event['location'] . chr(13) . chr(13) .
			"Comments: "  . chr(13) . $event['text'] . chr(13) . chr(13) . 
			"Link: " . CalendarEmail::curPageURL();// . "&Name=" . $event['calendar'] . "&EditEvent=" . $event['id']; #new events dont have an id..

	
		if($action == 'delete'){
			$subject = '{deleted} ' . $subject;
		}

		foreach($arr as $u){
			$username = explode('(',$u);
			$user = User::newFromName(trim($username[0]));
			
			if($user){
				$user->sendMail($subject, $body, $fromEmail);
			}
		}
	}
	
	public static function curPageURL() {
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
			$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}
}