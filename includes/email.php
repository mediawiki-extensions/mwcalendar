<?php

require_once( mwcalendar_base_path . '/includes/helpers.php');

class CalendarEmail{
	
	public static function send($users, $event){
		global $wgUser;
		$fromEmail = $wgUser->getEmail();
		
		$arr = explode(',',$users);
		$arr = array_unique($arr); //remove duplicates
		
		$subject = $event['subject'];
		$start = helpers::date($event['start']);
		$end = helpers::date($event['end']);
		
		$arrUrl = explode( '&', $_SERVER['REQUEST_URI'] );
		$urlPath = $arrUrl[0]; //clear any previous parameters	
		
		$text = 'FROM: ' . $start . chr(13) . 'END: ' . $end . chr(13)
			. chr(13) . $event['text'] . chr(13). chr(13) . CalendarEmail::curPageURL();
	

	
		foreach($arr as $u){
			$username = explode('(',$u);
			$user = User::newFromName(trim($username[0]));
			
			if($user){
				$user->sendMail($subject, $text, $fromEmail);
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