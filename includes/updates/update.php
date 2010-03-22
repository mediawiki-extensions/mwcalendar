<?php

class update{

	public function validate($db_ver){
		global $wgOut;
		$wgOut->addHtml("<u><b><big>validating database</big></b></u><br>");
		
		$new_install = $this->createTables(); # always run 1st (create all new tables here too...)	
		
		// no need to run update scripts if its a new db
		if(!$new_install){
			$this->runUpdates($db_ver);
		}
		
		$this->setDBVersion(); # RUN LAST!!!
	}
	
	private function runUpdates($db_ver){
		global $wgOut,$wgDBprefix;
		
		$wgOut->addHtml('<br><b>running database update scripts...</b><br>');
		
		$calendar_header = $wgDBprefix.'calendar_header';
		$calendar_events = $wgDBprefix.'calendar_events';
		$calendar_version = $wgDBprefix.'calendar_version';
		
		$dbw = wfGetDB( DB_MASTER );
		$dbr = wfGetDB( DB_SLAVE );	
		
		require_once( mwcalendar_base_path . '/includes/updates/update_003.php');
		require_once( mwcalendar_base_path . '/includes/updates/update_007.php');
		require_once( mwcalendar_base_path . '/includes/updates/update_033.php');
	}		
	
	// create new tables for new installs and updates
	private function createTables(){
		global $wgDBprefix,$wgOut;
		$wgOut->addHtml("<b>checking tables...</b><br>");
		
		$new_install = false;
		
		$dbw = wfGetDB( DB_MASTER );
		$dbr = wfGetDB( DB_SLAVE );	

		$header =  "`" . $wgDBprefix . "calendar_header" . "`";
		$events =  "`" . $wgDBprefix . "calendar_events" . "`";
		$version =  "`" . $wgDBprefix . "calendar_version" . "`";
		
		$sql[$header] = 
			"CREATE TABLE $header (
				`id` integer NOT NULL auto_increment,
				`name` varchar(255) NOT NULL default '',
				`description` varchar(255) default '',
				`options' mediumtext default '',
				PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1; ";

		$sql[$version] = 
			"CREATE TABLE $version (
				`id` integer NOT NULL auto_increment,
				`version` varchar(255) default '',
				`date` double default '0',
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
			
			//$wgOut->addHtml("...adding table: $table <br>");
			if( !$res ) {	
				if($table == $header) {
					$new_install = true; ## flag to determine if we want to run all db "updates" 
				}
				
				$wgOut->addHtml("...inserting table: $table <br>");
				$dbw->query($sqldata);						
			}		
		}
		
		return $new_install;
	}
	
	private function setDBVersion(){
		global $wgOut;
		
		$dbw = wfGetDB( DB_MASTER );
		
		$calendar_version = array(
			'version' 		=> mwcalendar_version,
			'date'     		=> time()
		);
		
		$dbw->insert('calendar_version',$calendar_version); 
		
		$wgOut->addHtml('<br><br><b>VALIDATION COMPLETED (please refresh)</b>');
	}
}
