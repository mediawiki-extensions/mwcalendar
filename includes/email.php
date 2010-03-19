<?php

require_once( mwcalendar_base_path . '/includes/helpers.php');

class CalendarEmail{
	
	public static function send($to, $event){
		global $wgUser,$wgVersion,$wgOutputEncoding,$wgPasswordSender;

		$userEmail = $wgUser->getEmail();
		$adminEmail = $wgPasswordSender;	
		$from = ( $wgUser->getEmail() != '') ? $userEmail  : $adminEmail;
		
		switch (mwcalendar_email_ical_mode){
			case 0:
				## disabled
				break;
			case 1:
				self::sendIcalAttachement($from,$to,$event);
				break;
			case 2:
				self::sendIcalEmail($from, $to, $event);
				break;
		}
		return;
	}
	
	private static function sendIcalEmail($from, $to, $event) {

		$message = '';
		$body = $event['text'];
		
		$start = date('Ymd',$event['start']).'T'.date('His',$event['start']);
		$end = date('Ymd',$event['end']).'T'.date('His',$event['end']);
		$todaystamp = date('Ymd').'T'.date('His');
				
		//Create Mime Boundry
		$mime_boundary = "----mwcalendar----".md5(time());

		//Create Email Headers
		$headers = "MIME-Version: 1.0\n";
		$headers .= "Content-class: urn:content-classes:message\n";		
		$headers .= "From:$from\n";
		$headers .= "Reply-To:$from\n";
		$headers .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary\"\n";
		
		## html
		$message .= "--$mime_boundary\n";
		$message .= "Content-Type: text/html; charset=iso-8859-1\n"; 
		$message .= "Content-Transfer-Encoding: 8bit\n\n"; 	
		$message .= "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 3.2//EN\">\n";		
		$message .= "<html><head></head><body>" . $body . "</body></html>\n";
		
		## icalendar
		$message .= "--$mime_boundary\n";	
 		$message .= "Content-Type: text/calendar;name=\"event.ics\";method=REQUEST\n";
		$message .= "Content-Transfer-Encoding: 8bit\n\n"; 		
		$message .= self::build_ical($from, $event);   
		$message .= "--$mime_boundary\n";

		## send emails
		$subject = strip_tags($event['subject']);
		self::sendmail($to, $subject, $message, $headers);
	}
	
	private static function sendIcalAttachement($from,$to,$event){
		
		$headers = "MIME-Version: 1.0\n";	
		$headers .= "From:$from\n";
		$headers .= "Reply-To:$from\n"; 		
		$headers .= "Content-class: urn:content-classes:calendarmessage\n";		
		$headers .= "Content-Type: text/plain; method=REQUEST;\n";
		$headers .= "Content-Disposition: attachment; filename=\"event.ics\"\n";
		$headers .= "Content-Transfer-Encoding: 8bit\n\n";
		
		$message = self::build_ical($from, $event);
		
		$subject = strip_tags($event['subject']);
		self::sendmail($to, $subject, $message, $headers);
	}
	
	private static function sendmail($to, $subject, $message, $headers){
		
		$arr = explode("\n", $to);
		$arr = array_unique($arr); //remove duplicates		
		foreach($arr as $u){
			$username = explode('(',$u);
			$user = User::newFromName(trim($username[0]));
			
			if($user){
				mail( $user->getEmail(), $subject, $message, $headers );
			}
		}		
	}
	
	private static function build_ical($from,$event){
		
		if(mwcalendar_email_allday_format ==1){
			## - 20101215-20101216
			## - standard format, but converts to UTC? timezone offset in both attachment and embedded)
			$DTSTART = $event['allday'] ?
				"DTSTART;VALUE=DATE:".date('Ymd',$event['start'])
				:
				"DTSTART:".date('Ymd',$event['start']) . "T" . date('His', $event['start']);
			
			$DTEND = $event['allday'] ?
				"DTEND;VALUE=DATE:".date('Ymd',$event['end'] +86400)
				:
				"DTEND:".date('Ymd',$event['end']) . "T" . date('His', $event['end']);
		}else{
			## - 20101215T000000-20101215T235959
			## - convert to destination timezone in embedded, but not attachment mode
			$DTSTART = $event['allday'] ?
				"DTSTART:".date('Ymd',$event['start']) . "T000000"
				:
				"DTSTART:".date('Ymd',$event['start']) . "T" . date('His', $event['start']);
			
			$DTEND = $event['allday'] ?
				"DTEND:".date('Ymd',$event['end']) . "T235959"
				:
				"DTEND:".date('Ymd',$event['end']) . "T" . date('His', $event['end']);
		}

		$location = $event['location'];
		$subject = $event['subject'];
		$description = $event['text'];
		
		$todaystamp = date('Ymd').'T'.date('His');
		
		//Create unique identifier
		$cal_uid = date('Ymd').'T'.date('His')."-".rand();
		
		$description = str_replace("\r\n","\\n",$description);//make sure 1st char of 1st line is escaped or ical will error
		$description = str_replace("\n","\\n",$description);
		$description = strip_tags($description);
	
		$ical =    
			"BEGIN:VCALENDAR\n".
			"PRODID:-//Microsoft Corporation//Outlook 11.0 MIMEDIR//EN\n".
			"VERSION:2.0\n".
//			"METHOD:REQUEST\n".		/* MEETING (Accept, Decline, etc) */
			"METHOD:PUBLISH\n".		/* APPT */
			"BEGIN:VEVENT\n".
			"ORGANIZER:MAILTO:$from\n".
			"$DTSTART\n".
			"$DTEND\n".
			"LOCATION:$location\n".
//			"TRANSP:OPAQUE\n". 		/* BUSY */
			"TRANSP:TRANSPARENT\n". /* FREE */
			"SEQUENCE:0\n".
			"UID:$cal_uid\n".
			"DTSTAMP:$todaystamp\n".
			"DESCRIPTION:$description\n".
			"SUMMARY:$subject\n".
			"PRIORITY:5\n".
			"CLASS:PUBLIC\n".
			"END:VEVENT\n".
			"END:VCALENDAR\n";
	
		return $ical;
	}
}