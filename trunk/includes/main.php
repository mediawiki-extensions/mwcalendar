<?php

# Confirm MW environment
if (!defined('MEDIAWIKI')) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

require_once('C:\Inetpub\wwwroot\mediawiki\extensions\mwCalendar\includes\EventHandler.php');
require_once('C:\Inetpub\wwwroot\mediawiki\extensions\mwCalendar\includes\Database.php');

class mwCalendar{
	
	var $month = '';
	var $day = '';
	var $year = '';
	
	var $htmlData = '';
	var $db;
	
	var $calendarName;
	
	public function mwCalendar(){
		global $wgOut;	
		
		$events = new EventHandler();	
		$events->CheckForEvents();		
		
		$this->db = new CalendarDatabase;

		// set the calendar's initial date
		$now = getdate();
		
		$this->month = $now['mon'];
		$this->year = $now['year'];
		$this->day = $now['mday'];	
		
		$wgOut->addScriptFile( '/mediawiki/extensions/mwCalendar/html/DatePicker.js');	
		$wgOut->addStyle( '/mediawiki/extensions/mwCalendar/html/DatePicker.css', 'screen');
		
		$style = file_get_contents("C:\Inetpub\wwwroot\mediawiki\extensions\mwCalendar\html\default.css");
		$wgOut->addHtml($style . chr(13));	
		
		$this->htmlData = file_get_contents("C:\Inetpub\wwwroot\mediawiki\extensions\mwCalendar\html\default.html");
	}
	
	public function begin($name){
		global $wgOut;	
		$html = "";		
		
		$this->calendarName = $name;
		
		//$arr = $this->db->getEvents($name, 0,12654324000);
		//return $arr[0]['subject'];
	
		// determine what we need to display
		$arrUrl = split( '&', $_SERVER['REQUEST_URI'] );
		
		switch( $arrUrl[1] ){
		case 'AddEvent':
			$html = file_get_contents("C:\Inetpub\wwwroot\mediawiki\extensions\mwCalendar\html\AddEvent.html");
			
			// update the 'hidden' input field so we retain the calendar name for the db update
			$html = str_replace('[[CalendarName]]', $this->calendarName, $html);
			
			break;
			
		default:
			$html = $this->createCalendar();
		} //end switch
			
		$wgOut->addHtml($html);
	}
	
	// build main calendar month
	private function createCalendar(){
		$calendarHTML = $this->searchHTML($this->htmlData, 	'<!--Calendar Start-->', 	'<!--Calendar End-->');
		$titleHTML = $this->searchHTML($this->htmlData, 	'<!--Title Start-->', 		'<!--Title End-->');
		$headerHTML = $this->searchHTML($this->htmlData, 	'<!--Header Start-->', 		'<!--Header End-->');
		$weekdayHTML = $this->searchHTML($this->htmlData, 	'<!--Weekday Start-->', 	'<!--Weekday End-->');
		$weekendHTML = $this->searchHTML($this->htmlData, 	'<!--Weekend Start-->', 	'<!--Weekend End-->');
		$emptyHTML = $this->searchHTML($this->htmlData, 	'<!--Empty Start-->', 		'<!--Empty End-->');
		$weekHTML = $this->searchHTML($this->htmlData, 		'<!--Week Start-->', 		'<!--Week End-->');

		$addEventHTML = $this->searchHTML($this->htmlData, 		'<!--AddEvent Start-->', '<!--AddEvent End-->');
	
		$dayOfWeek = date('N', mktime(12, 0, 0, $this->month, 1, $this->year));	// 0-6
	    $daysInMonth = date('t', mktime(12, 0, 0, $this->month, 1, $this->year));  // 28-31
		$weeksInMonth = ceil( ($dayOfWeek + $daysInMonth)/7 ); // 4-6
		
		$first = mktime(12,0,0,$this->month,1,$this->year);
		$last = mktime(12,0,0,$this->month,$daysInMonth,$this->year);
		
		//$arrMonthEvents = $this->db->getEvents($this->calendarName, $first, $last);
		$arrMonthEvents = $this->db->getEvents('test2', $first, $last);
		//return print_r($arrMonthEvents );
		
		$day = (-$dayOfWeek) +1;
		for($week=0; $week < $weeksInMonth; $week++){
			
			$ret .= '<tr>';
			for($i=0; $i<7; $i++){

				for($i=0; $i < 7; $i++){	
					
					if( ($day > $daysInMonth) || ($day < 1) ){
						$temp = $emptyHTML;
					}
					elseif( $i==0 || $i==6 ){
						$events = $this->buildEventList($arrMonthEvents, $this->month, $day, $this->year);
						$temp = str_replace("[[Day]]", $day, $weekendHTML);
						$temp = str_replace("[[EventList]]", $events, $temp);
					}
					else{
						$events = $this->buildEventList($arrMonthEvents, $this->month, $day, $this->year);
						$temp = str_replace("[[Day]]", $day, $weekdayHTML);
						$temp = str_replace("[[EventList]]", $events, $temp);
					}
					$ret .= $temp;
					$day++;
				}
			}
			$ret .= '</tr>';
		}
		
		$weeksHTML = str_replace("[[WEEKS]]", $ret, $weekHTML);
		
		$calendarHTML = str_replace('[[TITLE]]', $titleHTML, $calendarHTML);
		$calendarHTML = str_replace('[[WEEKS]]', $weeksHTML, $calendarHTML);
		
		
		$calendarHTML .= $addEventHTML;
		
		return $calendarHTML;
	}
	
	private function buildEventList($arrEvents, $month, $day, $year){
		$date = mktime(0,0,0,$month,$day,$year);
		//$date2 = mktime(23,59,59,$month,$day,$year);
		
		foreach($arrEvents as $event){
		$i++;
			if( ($date >= $event['start']) && ($date <= $event['end']) ){
				$links .= $event['subject'] . '<br>';
			}
		}

		return $links;
	}
		
    function searchHTML($html, $beginString, $endString) {
	
    	$temp = split($beginString, $html);
    	if (count($temp) > 1) {
			$temp = split($endString, $temp[1]);
			return $temp[0];
    	}
    	return "";
    }	
}














