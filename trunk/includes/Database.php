<?php

# Confirm MW environment
if (!defined('MEDIAWIKI')) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

class CalendarDatabase{
	
	var $dbPrefix = '';

	public function CalendarDatabase(){
		global $wgDBprefix;
		$this->dbPrefix = $wgDBprefix;
		
		$this->checkTables();
		//$this->createCalendar('test', 'text calendar');
	}
	
	public function setEvent($arrEvent){
		
		$dbw = wfGetDB( DB_MASTER );
		$dbr = wfGetDB( DB_SLAVE );	
			
		$calendar = $arrEvent['calendar'];	

		$dbw->insert('calendar_events', array(
			'calendarid' 		=> $this->getCalendarID($calendar),
			'subject'        	=> $arrEvent['subject'],
			'location'        	=> $arrEvent['location'],
			'start'        		=> $arrEvent['start'],
			'end'        		=> $arrEvent['end'],
			'allday'        	=> $arrEvent['allday'],
			'text'        		=> $arrEvent['text'],
			'createdby'        	=> $arrEvent['createdby'],
			'createddate'       => time(),
			'invites'        	=> $arrEvent['invites'],
			'editedby'       	=> '',
			'editeddate'       	=> null
		));
	}

	public function deleteEvent($eventid){	
		$dbw = wfGetDB( DB_MASTER );
		
		$eventtable = $this->dbPrefix . 'calendar_events';
			
		$sql = "DELETE FROM $eventtable
					WHERE id = $eventid";// LIMIT 0,25";	
		
		$dbw->query($sql);
	}	
	
	public function updateEvent($arrEvent, $eventid){
		
		$dbw = wfGetDB( DB_MASTER );
		$dbr = wfGetDB( DB_SLAVE );	
			
		$calendar = $arrEvent['calendar'];	

		$dbw->update( 'calendar_events', 
			array(
				'calendarid' 		=> $this->getCalendarID($calendar),
				'subject'        	=> $arrEvent['subject'],
				'location'        	=> $arrEvent['location'],
				'start'        		=> $arrEvent['start'],
				'end'        		=> $arrEvent['end'],
				'allday'        	=> $arrEvent['allday'],
				'text'        		=> $arrEvent['text'],
//				'createdby'        	=> $arrEvent['createdby'],
//				'createddate'       => $arrEvent['createddate'],
				'invites'        	=> $arrEvent['invites'],
				'editedby'       	=> $arrEvent['editedby'],
				'editeddate'       	=> time()
			),
			array(
				'id' => $eventid
			)
		);
	}	
	
	public function getEvents($calendar, $timestamp1, $timestamp2){

		$eventtable = $this->dbPrefix . 'calendar_events';
		
		$dbr = wfGetDB( DB_SLAVE );	
		
		$calendarid = $this->getCalendarID($calendar);
		
		$sql = "SELECT *
					FROM $eventtable
					WHERE start >= $timestamp1 AND start <= $timestamp2
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
			$arrEvent['createddate'] = $r->createddate;
			$arrEvent['invites'] = $r->invites;
			$arrEvent['editedby'] = $r->editedby;
			$arrEvent['editeddate'] = $r->editeddate;

			$arrEvents[] = $arrEvent;
		}
		
		return $arrEvents;
	}
	
	public function getEvent($eventid){

		$eventtable = $this->dbPrefix . 'calendar_events';
		
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
			$arrEvent['createddate'] = $r->createddate;
			$arrEvent['text'] = $r->text;
			$arrEvent['invites'] = $r->invites;
			$arrEvent['editedby'] = $r->editedby;
			$arrEvent['editeddate'] = $r->editeddate;

			//$arrEvents[] = $arrEvent;
		}
		
		return $arrEvent;
	}	
	
	public function checkTables(){
		$dbw = wfGetDB( DB_MASTER );
		$dbr = wfGetDB( DB_SLAVE );	

		$header =  "`" . $this->dbPrefix . "calendar_header" . "`";
		$events =  "`" . $this->dbPrefix . "calendar_events" . "`";
		
		$sql[$header] = 
			"CREATE TABLE $header (
				`id` integer NOT NULL auto_increment,
				`name` varchar(255) NOT NULL default '',
				`description` varchar(255) default '',
				PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1; ";
			
		$sql[$events] = 
			"CREATE TABLE $events (
				`id` integer NOT NULL auto_increment,
				`calendarid` integer NOT NULL default '0',
				`subject` varchar(255) default '',
				`location` varchar(255) default '',
				`start` double NOT NULL default '0',
				`end` double NOT NULL default '0',
				`allday` boolean NOT NULL default false,
				`text` longtext default '',
				`createdby` varchar(255) NOT NULL default '',
				`createddate` double NOT NULL default '0',
				`invites` mediumtext default '',
				`editedby` varchar(255) default '',
				`editeddate` double default '0',
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
	
	public function getCalendarID($calendar){
	
		$id = 0;
		$table = $this->dbPrefix . 'calendar_header';
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
		$dbw = wfGetDB( DB_MASTER );
		
		$dbw->insert( 'calendar_header', array(
			'name' 		=> $name,
			'description'        	=> $description) 
		);	
		
		return $dbw->insertid();
	}	

} //end class

