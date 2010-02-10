<?php

class CalendarEmail{
	
	public static function send($users, $event){
		global $wgUser;
		$fromEmail = $wgUser->getEmail();
		
		$arr = explode(',',$users);
		$arr = array_unique($arr); //remove duplicates
		
		$subject = $event['subject'];
		$start = date('D n/j/Y H:i:s', $event['start']);
		$end = date('D n/j/Y H:i:s', $event['end']);
		
		$text = 'FROM: ' . $start . chr(13) . 'END: ' . $end . chr(13). chr(13) . $event['text'];
	
		foreach($arr as $u){
			$username = explode('(',$u);
			$user = User::newFromName(trim($username[0]));
			
			if($user){
				$user->sendMail($subject, $text, $fromEmail);
			}
		}
	}


}