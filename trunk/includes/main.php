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
		
		//$events = new EventHandler();	
		EventHandler::CheckForEvents();		
		
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
		$arrUrl = explode( '&', $_SERVER['REQUEST_URI'] );
		$param = explode( '=', $arrUrl[1] );
		
		switch( $param[0] ){
		case 'AddEvent':
			$html = file_get_contents("C:\Inetpub\wwwroot\mediawiki\extensions\mwCalendar\html\AddEvent.html");
			
			// update the 'hidden' input field so we retain the calendar name for the db update
			$html = str_replace('[[CalendarName]]', $this->calendarName, $html);
			$html = str_replace('[[EventID]]', null, $html);
			
			break;
			
		case 'EditEvent':
			$html = file_get_contents("C:\Inetpub\wwwroot\mediawiki\extensions\mwCalendar\html\AddEvent.html");
			
			$event = $this->db->getEvent( $param[1] );
			$start = date('n/j/Y', $event['start']);
			$end = date('n/j/Y', $event['end']);
				
			// update the 'hidden' input field so we retain the calendar name for the db update
			$html = str_replace('[[CalendarName]]', $this->calendarName, $html);
			$html = str_replace('[[EventID]]', $event['id'], $html);	
			$html = str_replace('[[Subject]]', $event['subject'], $html);	
			$html = str_replace('[[Location]]', $event['location'], $html);			
			$html = str_replace('[[Invites]]', $event['invites'], $html);	
			$html = str_replace('[[Start]]', $start, $html);	
			$html = str_replace('[[End]]', $end, $html);				
			$html = str_replace('[[Text]]', $event['text'], $html);		
		
			break;
			
		default:
			$html = $this->createCalendar();
		} //end switch
			
		$wgOut->addHtml($html);
	}
	
	// build main calendar month
	private function createCalendar(){
		$calendarHTML = $this->searchHTML($this->htmlData, 	'<!--Calendar Start-->', 	'<!--Calendar End-->');
		//$headerHTML = $this->searchHTML($this->htmlData, 	'<!--Header Start-->', 		'<!--Header End-->');
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
		
		$ret = $this->buildWeekHeader();
		
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
		
		$calendarHTML = str_replace('[[HEADER]]', $this->buildNavControls(), $calendarHTML);
		$calendarHTML = str_replace('[[BODY]]', $weeksHTML, $calendarHTML);
		//$calendarHTML = str_replace('[[FOOTER]]', $footerHTML, $calendarHTML);

		$calendarHTML .= $addEventHTML; //temp button
		
		return $calendarHTML;
	}
	
	private function buildWeekHeader(){
		
		$arr = array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
		
		foreach($arr as $head){
			$header .= '<td>' . $head . '</td>';
		}
	
		return '<tr class="calendar_header">' . $header . '</tr>';
	}
	
	private function buildNavControls(){
		$navHTML = $this->searchHTML($this->htmlData, 		'<!--Nav Start-->', '<!--Nav End-->');
		$navHTML = str_replace('[[MONTH_CONTROL]]', $this->buildMonthSelect(), $navHTML);
		$navHTML = str_replace('[[YEAR_CONTROL]]', $this->buildYearSelect(), $navHTML);
		$navHTML = str_replace('[[CALENDAR_NAME]]', $this->calendarName, $navHTML);
	
		return $navHTML;
	}

	function buildMonthSelect(){
		global $wgLang;
		
		$backBtn = "<input class='btn' name='monthBack' type='submit' value='<<'>&nbsp;";
		$forwardBtn = "&nbsp;<input class='btn' name='monthForward' type='submit' value='>>'>";		
	
	    // build the month select box
	    $monthSelect = "<select name='monthSelect' method='post' onChange='javascript:this.form.submit()'>";
		for ($i = 1; $i <= 12; $i++) {
    		if ($i == $this->month) {
				$monthSelect .= "<option class='lst' value='" . ($i) . "' selected='true'>" . 
				$wgLang->getMonthName($i) . "</option>\n";
    		}
    		else {
				$monthSelect .= "<option class='lst' value='" . ($i) . "'>" . 
				$wgLang->getMonthName($i) . "</option>\n";
    		}
	    }
	    $monthSelect .= "</select>";
	
		return $backBtn . $monthSelect . $forwardBtn;
	}
	
	private function buildYearSelect(){
    	
		$yearoffset = 3;
		
		$backBtn = "<input class='btn' name='yearBack' type='submit' value='<<'>&nbsp;";
		$forwardBtn = "&nbsp;<input class='btn' name='yearForward' type='submit' value='>>'>";

	    // build the year select box, with +/- 5 years in relation to the currently selected year
	    $yearSelect = "<select name='yearSelect' method='post' onChange='javascript:this.form.submit()'>";
		for ($i = ($this->year - $yearoffset); $i <= ($this->year + $yearoffset); $i += 1) {
    		if ($i == $this->year) {
				$yearSelect .= "<option class='lst' value='$i' selected='true'>" . 
				$i . "</option>\n";
    		}
    		else {
				$yearSelect .= "<option class='lst' value='$i'>$i</option>\n";
    		}
	    }
	    $yearSelect .= "</select>";	
	
		return $backBtn . $yearSelect . $forwardBtn;
	}	
	
	private function buildEventList($arrEvents, $month, $day, $year){
		$date = mktime(0,0,0,$month,$day,$year);
		//$date2 = mktime(23,59,59,$month,$day,$year);
		
		foreach($arrEvents as $event){

			if( ($date >= $event['start']) && ($date <= $event['end']) ){
				$links .= $this->buildLink($event);
			}
		}

		return $links;
	}
	
	private function buildLink($event){
		$html = file_get_contents("C:\Inetpub\wwwroot\mediawiki\extensions\mwCalendar\html\AddEvent.html");
		
		$url = $_SERVER['REQUEST_URI'] . '&EditEvent=' . $event['id'];
		$link = '<a href="' . $url . '">' . $event['subject'] . '</a> <br>';
		return $link;
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














