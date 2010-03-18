<?php

require_once( mwcalendar_base_path . '/includes/helpers.php');

class CalendarEmail{
	
	public static function send($to, $event, $action='save'){
		global $wgUser,$wgVersion,$wgOutputEncoding,$wgPasswordSender;
			
		# dont send email if current user doesnt have one...?
		//if( $wgUser->getEmail() == '') return;
		$from = ( $wgUser->getEmail() != '') ? new MailAddress($wgUser->getEmail()) : new MailAddress($wgPasswordSender);
	
		$subject = strip_tags($event['subject']);
		
		self::sendIcalEmail($from, $to, $subject, $event['location'], $event['text'], $event['start'], $event['end'], $event);
	}
	
	private static function sendIcalEmail($from,$to,$subject,$location,$body,$start,$end,$event) {

		$from_name = "My Name";
		$from_address = $from;
		$message = '';
		
		$start = date('Ymd',$start).'T'.date('His',$start);
		$end = date('Ymd',$end).'T'.date('His',$end);
		$todaystamp = date('Ymd').'T'.date('His');
		
		//Create unique identifier
		$cal_uid = date('Ymd').'T'.date('His')."-".rand()."@mydomain.com";
		
		//Create Mime Boundry
		$mime_boundary = "----Meeting Booking----".md5(time());
			
		//Create Email Headers
		$headers = "From: ".$from_name." <".$from_address.">\n";
		$headers .= "Reply-To: ".$from_name." <".$from_address.">\n";
		
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary\"\n";
		$headers .= "Content-class: urn:content-classes:calendarmessage\n";
		
		//Create Email Body (HTML)
		$message .= "--$mime_boundary\n";
		$message .= "Content-Type: text/html;charset=iso-8859-1\n"; 
		$message .= "Content-Transfer-Encoding: 8bit\n\n";
		$message .= "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 3.2//EN\">\n";		
		$message .= "<html>\n";
		$message .= "<head>\n";
		$message .= "<META HTTP-EQUIV=3D\"Content-Type\" CONTENT=3D\"text/html; = charset=3Diso-8859-1\">\n";
		$message .= "<META NAME=3D\"Generator\" CONTENT=3D\"MS Exchange Server version = 6.5.7655.7\">\n";
		$message .= "</head>\n";
		$message .= "<body>\n";
		$message .= $body . "\n";
		$message .= "</body>\n";
		$message .= "</html>\n";
		$message .= "--$mime_boundary\n";
			
		$message .= 'Content-Type: text/calendar;name="meeting.ics";method=REQUEST\n';
		//$message .= 'Content-Type: text/html; filename=meeting.ics;\n';
		//$message .= 'Content-Disposition: inline; filename=meeting.ics\n';
		$message .= "Content-Transfer-Encoding: 8bit\n\n";
		$message .= self::build_ical($event);           

		$arr = explode("\n", $to);
		$arr = array_unique($arr); //remove duplicates		
		foreach($arr as $u){
			$username = explode('(',$u);
			$user = User::newFromName(trim($username[0]));
			
			if($user){
				mail( $user->getEmail(), $subject, $message, $headers );
			}
		}
		
		//SEND MAIL
		//$mail_sent = @mail( $email, $subject, $message, $headers );
		
		if($mail_sent)     {
			return true;
		} else {
			return false;
		}   

	}
	
	private static function build_ical($event){
		$from_name = "My Name";
		$from_address = $from;
				
		$start = date('Ymd',$event['start']).'T'.date('His',$event['start']);
		$end = date('Ymd',$event['end']).'T'.date('His',$event['end']);
		$location = $event['location'];
		$subject = $event['subject'];
		$description = $event['text'];
		
		$todaystamp = date('Ymd').'T'.date('His');
		
		//Create unique identifier
		$cal_uid = date('Ymd').'T'.date('His')."-".rand()."@mydomain.com";
			
		$ical =    
			"BEGIN:VCALENDAR\n".
			"PRODID:-//Microsoft Corporation//Outlook 11.0 MIMEDIR//EN\n".
			"VERSION:2.0\n".
			"METHOD:PUBLISH\n".
			"BEGIN:VEVENT\n".
			//"ORGANIZER:MAILTO:$from_address\n".
			"DTSTART:$start\n".
			"DTEND:$end\n".
			"LOCATION:$location\n".
			"TRANSP:OPAQUE\n".
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