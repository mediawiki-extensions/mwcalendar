<?php

# Confirm MW environment
if (!defined('MEDIAWIKI')) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

class CalendarDatabase{

	public function CalendarDatabase(){
		$this->checkTables();
		//$this->createCalendar('test', 'text calendar');
	}
	
	public function setEvent($arrEvent){
		
		$dbw = wfGetDB( DB_MASTER );
		$dbr = wfGetDB( DB_SLAVE );	
			
		$calendar = $arrEvent['calendar'];	
		$subject = $arrEvent['subject'];
		$location = $arrEvent['location'];
		$start = $arrEvent['start'];
		$end = $arrEvent['end'];
		$allday = $arrEvent['allday'];
		$text = $arrEvent['text'];
		$createdby = $arrEvent['createdby'];
		$invites = $arrEvent['invites'];
		
		$calendarid = $this->getCalendarID($calendar);

		$dbw->insert( $wgDBprefix.'mwcalendar_events', array(
			'calendarid' 		=> $calendarid,
			'subject'        	=> $subject,
			'location'        	=> $location,
			'start'        		=> $start,
			'end'        		=> $end,
			'allday'        	=> $allday,
			'text'        		=> $text,
			'createdby'        	=> $createdby,
			'invites'        	=> $invites 
		));
	}

	public function deleteEvent($eventid){
		global $wgDBprefix;
		
		$dbw = wfGetDB( DB_MASTER );
		
		$eventtable = $wgDBprefix . 'mwcalendar_events';
			
		$sql = "DELETE FROM $eventtable
					WHERE id = $eventid";// LIMIT 0,25";	
		
		$dbw->query($sql);
	}	
	
	public function updateEvent($arrEvent, $eventid){
		
		$dbw = wfGetDB( DB_MASTER );
		$dbr = wfGetDB( DB_SLAVE );	
			
		$calendar = $arrEvent['calendar'];	
		$subject = $arrEvent['subject'];
		$location = $arrEvent['location'];
		$start = $arrEvent['start'];
		$end = $arrEvent['end'];
		$allday = $arrEvent['allday'];
		$text = $arrEvent['text'];
		$createdby = $arrEvent['createdby'];
		$invites = $arrEvent['invites'];
		
		$calendarid = $this->getCalendarID($calendar);

		$dbw->update( $wgDBprefix.'mwcalendar_events', 
			array(
				'calendarid' 		=> $calendarid,
				'subject'        	=> $subject,
				'location'        	=> $location,
				'start'        		=> $start,
				'end'        		=> $end,
				'allday'        	=> $allday,
				'text'        		=> $text,
				'createdby'        	=> $createdby,
				'invites'        	=> $invites
			),
			array(
				'id' => $eventid
			)
		);
	}	
	
	public function getEvents($calendar, $timestamp1, $timestamp2){
		global $wgDBprefix;

		$eventtable = $wgDBprefix . 'mwcalendar_events';
		
		$dbr = wfGetDB( DB_SLAVE );	
		
		$calendarid = $this->getCalendarID($calendar);
		
		$sql = "SELECT *
					FROM $eventtable
					WHERE start >= $timestamp1 AND end <= $timestamp2
					AND calendarid = $calendarid";// LIMIT 0,25";

		$res = $dbr->query($sql);    
		while ($r = $dbr->fetchObject( $res )) {
			$arrEvent['id'] = $r->id;
			$arrEvent['calendarid'] = $r->calendarid;	
			$arrEvent['subject'] = $r->subject;
			$arrEvent['location'] = $r->location;
			$arrEvent['start'] = $r->start;
			$arrEvent['end'] = $r->end;
			$arrEvent['allday'] = $r->allday;
			$arrEvent['text'] = $r->text;
			$arrEvent['createdby'] = $r->createdby;
			$arrEvent['text'] = $r->text;
			$arrEvent['invites'] = $r->invites;

			$arrEvents[] = $arrEvent;
		}
		
		return $arrEvents;
	}
	
	public function getEvent($eventid){
		global $wgDBprefix;

		$eventtable = $wgDBprefix . 'mwcalendar_events';
		
		$dbr = wfGetDB( DB_SLAVE );	
		
		//$calendarid = $this->getCalendarID($calendar);
		
		$sql = "SELECT *
					FROM $eventtable
					WHERE id = $eventid";// LIMIT 0,25";

		$res = $dbr->query($sql);    
		if ($r = $dbr->fetchObject( $res )) {
			$arrEvent['id'] = $r->id;
			$arrEvent['calendarid'] = $r->calendarid;	
			$arrEvent['subject'] = $r->subject;
			$arrEvent['location'] = $r->location;
			$arrEvent['start'] = $r->start;
			$arrEvent['end'] = $r->end;
			$arrEvent['allday'] = $r->allday;
			$arrEvent['text'] = $r->text;
			$arrEvent['createdby'] = $r->createdby;
			$arrEvent['text'] = $r->text;
			$arrEvent['invites'] = $r->invites;

			//$arrEvents[] = $arrEvent;
		}
		
		return $arrEvent;
	}	
	
	public function checkTables(){
		global $wgDBprefix;
		$dbw = wfGetDB( DB_MASTER );
		$dbr = wfGetDB( DB_SLAVE );		
		
		$sql[$wgDBprefix.'mwcalendar_calendars'] = 
			"CREATE TABLE `" . $wgDBprefix . "mwcalendar_calendars` (
				`id` integer NOT NULL auto_increment,
				`name` varchar(255) NOT NULL default '',
				`description` varchar(255) default '',
				PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1; ";
			
		$sql[$wgDBprefix.'mwcalendar_events'] = 
			"CREATE TABLE `" . $wgDBprefix . "mwcalendar_events` (
				`id` integer NOT NULL auto_increment,
				`calendarid` integer NOT NULL default '0',
				`subject` varchar(255) default '',
				`location` varchar(255) default '',
				`start` double NOT NULL default '0',
				`end` double NOT NULL default '0',
				`allday` boolean NOT NULL default false,
				`text` longtext default '',
				`createdby` varchar(255) NOT NULL default '',
				`invites` mediumtext default '',
				PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1; ";			
		
		
		// create tables if they do not exist
		while (list($table, $sqldata) = each($sql)){
			$dbr->ignoreErrors(true);
			$res = $dbr->query( "SELECT 1 FROM $table LIMIT 0,1" );
			$dbr->ignoreErrors(false);
			if( !$res ) {
				$dbw->query($sqldata); 
			}				
		}
	}
	
	private function getCalendarID($calendar){
		global $wgDBprefix;
		
		$id = 0;
		$table = $wgDBprefix . 'mwcalendar_calendars';
		$dbr = wfGetDB( DB_SLAVE );	
		$sql = "SELECT id FROM $table WHERE name = '$calendar'";		
					
		$res = $dbr->query($sql);	
		if($r = $dbr->fetchObject($res)) {
			$id = $r->id;
		}else{
			$id = $this->createCalendar($calendar, ''); //auto-create calendars
		}
				
		return $id;
	}	
	
	// we're auto creating calendars as needed in the database
	private function createCalendar($name, $description){
		global $wgDBprefix;
		$dbw = wfGetDB( DB_MASTER );
		
		$dbw->insert( $wgDBprefix.'mwcalendar_calendars', array(
			'name' 		=> $name,
			'description'        	=> $description) 
		);	
		
		return $dbw->insertid();
	}	

} //end class

