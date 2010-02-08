<?php

class CalendarEmail{
	
	public static function send($users, $event){

		$arr = explode(',',$users);
		$arr = array_unique($arr); //remove duplicates
		
		$subject = $event['subject'];
		$start = date('D n/j/Y H:i:s', $event['start']);
		$end = date('D n/j/Y H:i:s', $event['end']);
		
		$text = $start . chr(13) . $end . chr(13). $event['text'];
	
		foreach($arr as $u){
			$user = User::newFromName($u);
			
			if($user){
				$user->sendMail($subject, $text);
			}
		}
	}


}