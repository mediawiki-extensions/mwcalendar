<?php

require_once( mwcalendar_base_path . '/includes/helpers.php');

class CalendarEmail{
	
	public static function send($users, $event, $action='save'){
		global $wgUser,$wgVersion,$wgOutputEncoding,$wgPasswordSender;
		
		#todo: might filter out users without an email...
		
		$from = new MailAddress( $wgUser->getEmail() );
		$from_address = ( $wgUser->getEmail() != '') ? $from : new MailAddress( $wgPasswordSender );

		$arr = explode(',',$users);
		$arr = array_unique($arr); //remove duplicates
			
		$start = helpers::date($event['start']) . ' ' . helpers::time($event['start']);
		$end = helpers::date($event['end']) . ' ' . helpers::time($event['end']);
		
		//$subject = "($start) " . $event['subject'];
		$subject = $event['subject'];
		
		$arrUrl = explode( '&', $_SERVER['REQUEST_URI'] );
		$urlPath = $arrUrl[0]; //clear any previous parameters	

		$contentType = 'text/html; charset='.$wgOutputEncoding;
		 
		$body = 
			"<table><tr><td>FROM:</td><td>$start</td></tr>" . 
			"<tr><td>TO:</td><td>$end</td></tr></table>" . 
			"<hr>" .
			"Location: " . $event['location'] .
			"<hr>" .
			"Comments: <br>" . $event['text'] . 
			"<hr><br>" .

		$body = "<html xmlns=\"http://www.w3.org/1999/xhtml\"><head></head><body>$body</body></html>";		

		if($action == 'delete'){
			$subject = '{deleted} ' . $subject;
		}
		
		$arrAddress = array();
		foreach($arr as $u){
			$username = explode('(',$u);
			$user = User::newFromName(trim($username[0]));
			
			if($user){
				$to_address[] = new MailAddress( $user->getEmail() );
			}
		}

		UserMailer::send( $to_address, $from_address, $subject, $body, $from_address, $contentType ) ;

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