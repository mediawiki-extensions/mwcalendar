<?php

# Confirm MW environment
if (!defined('MEDIAWIKI')) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

require_once( mwcalendar_base_path . '/includes/updates/update.php');

class CalendarDatabase{
	
	var $dbPrefix = '';

	public function CalendarDatabase(){
		global $wgDBprefix;
		$this->dbPrefix = $wgDBprefix;
	}
	
	public function validateVersion(){
		global $wgOut;
		//$wgOut->addHtml('validating...');
		
		$dbr = wfGetDB( DB_SLAVE );	
		$update = new update();
		$table = $this->dbPrefix . 'calendar_version';		
			
		$sql = "SELECT MAX(version) as ver FROM $table;";				
		
		//make sure we have a version table...
		$dbr->ignoreErrors(true);
		$res = $dbr->query($sql);  
		$dbr->ignoreErrors(true);	
		
		if(!$res) {			
			$update->validate("0"); //early beta crud wont have version table
		}else{
			$r = $dbr->fetchObject( $res );

			//if($r->ver < mwcalendar_version) { 
			if(version_compare($r->ver, mwcalendar_version, '<')) {
				$update->validate($r->ver); 
			}				
		}
	}
	
	public function setEvent($arrEvent){
		
		$dbw = wfGetDB( DB_MASTER );
		$dbr = wfGetDB( DB_SLAVE );	
			
		$calendar = $arrEvent['calendar'];	
		
		## protect db data...
		if( !$this->validateEvent($arrEvent)) return;
			
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
	
	private function validateEvent(&$arrEvent){
	
		// min data we need to file a good event...
		if( strlen(trim($arrEvent['subject'])) == 0 ) return false; 
		if( strlen($arrEvent['start']) == 0 ) return false; 
		if( strlen($arrEvent['end']) == 0 ) return false; 	
		
		return true;
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
		
		## protect db data...
		if( !$this->validateEvent($arrEvent)) return;

		$dbw->update( 'calendar_events', 
			array(
//				'calendarid' 		=> $this->getCalendarID($calendar), //this shouldnt change (can cross contaminate)
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

		$arrEvents = array();
		
		$eventtable = $this->dbPrefix . 'calendar_events';
		
		$dbr = wfGetDB( DB_SLAVE );	
		
		$calendarid = $this->getCalendarID($calendar);
		
		$sql = "SELECT *
					FROM $eventtable
					WHERE start >= $timestamp1 AND start <= $timestamp2
					AND calendarid = $calendarid ORDER BY start, subject";

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
		$arrEvent = array();
		
		$eventtable = $this->dbPrefix . 'calendar_events';
		
		$dbr = wfGetDB( DB_SLAVE );	
			
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
			'name' 			=> $name,
			'description' 	=> $description
		) );	
		
		return $dbw->insertid();
	}	
	
	public function getDatabaseUsers(){
		$table = $this->dbPrefix . 'user';
		$dbr = wfGetDB( DB_SLAVE );		

		$sql = "SELECT *
					FROM $table
					WHERE user_email <> ''  LIMIT 0,100;";	

		$res = $dbr->query($sql);    
		while ($r = $dbr->fetchObject( $res )) {					
			
			$arr[$r->user_name] = $r->user_real_name;
		}
		return $arr;
	}

} //end class

