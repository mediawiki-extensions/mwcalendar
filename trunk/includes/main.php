<?php

# Confirm MW environment
if (!defined('MEDIAWIKI')) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

require_once( mwcalendar_base_path . '/includes/EventHandler.php');
require_once( mwcalendar_base_path . '/includes/Database.php');

class mwCalendar{
	
	var $month = '';
	var $day = '';
	var $year = '';
	
	var $htmlData = '';
	var $db;
	
	var $calendarName;
	var $addEventHtml = '';
	
	public function mwCalendar(){
		global $wgOut;	
		// set the calendar's initial date
		$now = getdate();
		
		$this->addEventHtml = file_get_contents( mwcalendar_base_path . "/html/AddEvent.html");
		
		$this->month = $now['mon'];
		$this->year = $now['year'];
		$this->day = $now['mday'];			
		
		//$events = new EventHandler();	
		EventHandler::CheckForEvents();		
		
		$this->db = new CalendarDatabase;

		$wgOut->addScriptFile( '/mediawiki/extensions/mwCalendar/html/DatePicker.js');	
		$wgOut->addStyle( '/mediawiki/extensions/mwCalendar/html/DatePicker.css', 'screen');
		
		$style = file_get_contents( mwcalendar_base_path. "/html/default.css");
		$wgOut->addHtml($style . chr(13));	
		
		$this->htmlData = file_get_contents( mwcalendar_base_path . "/html/default.html");
	}
	
	public function begin($name){
		global $wgOut;	
		$html = "";		
		
		$this->calendarName = $name;
		
		// determine what we need to display
		$arrUrl = explode( '&', $_SERVER['REQUEST_URI'] );

		$urlEvent[0] = '';
		if( isset($arrUrl[1]) ){
			$urlEvent = explode( '=', $arrUrl[1] ); #ex: EditEvent=45
		}
		
		switch( $urlEvent[0] ){
		case 'AddEvent':
			$html = $this->addEventHtml;
			
			if( isset($urlEvent[1]) ){
				$startDate = date('n/j/Y', $urlEvent[1]);
				$endDate = date('n/j/Y', $urlEvent[1]);
			}else{
				$startDate = date('n/j/Y', time());
				$endDate = date('n/j/Y', time());
			}
			
			// update the 'hidden' input field so we retain the calendar name for the db update
			$html = str_replace('[[CalendarName]]', $this->calendarName, $html);
			$html = str_replace('[[EventID]]', null, $html);
			$html = str_replace('[[Start]]', $startDate, $html);
			$html = str_replace('[[End]]', $endDate, $html);
			$html = str_replace('[[Disabled]]', 'disabled', $html);
			
			break;
			
		case 'EditEvent':
			$html = $this->addEventHtml;
			
			$event = $this->db->getEvent( $urlEvent[1] );
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
/*		
		case 'EditEvent':
			$html = $this->addEventHtml;
			$event = $this->db->getEvent( $urlEvent[1] );
			
			
			break;
	*/		
		case 'monthForward':
			$arr_date = getdate($urlEvent[1]);
			$this->month = $arr_date['mon']+1;
			$this->day = 1;
			$this->year = $arr_date['year'];
			
			$html = $this->createCalendar();
			
			break;
				
			
		default:
			$cookie_name = $this->calendarName;
			if( isset($_COOKIE[$cookie_name]) ){
				$date = getdate($_COOKIE[$cookie_name]); //timestamp value
				$this->month = $date['mon'];
				$this->year = $date['year'];
			}
			$html = $this->createCalendar();
		} //end switch
		
		 // remove any remaining [[xyz]] type tags
		$html = $this->clearHtmlTags($html);
		
		$wgOut->addHtml($html);
	}
	
	// this function removes any HTML tags that havent been overwritten
	private function clearHtmlTags($html){
		$html = str_replace('[[CalendarName]]', '', $html);
		$html = str_replace('[[EventID]]', '', $html);	
		$html = str_replace('[[Subject]]', '', $html);	
		$html = str_replace('[[Location]]', '', $html);			
		$html = str_replace('[[Invites]]', '', $html);	
		$html = str_replace('[[Start]]', '', $html);	
		$html = str_replace('[[End]]', '', $html);				
		$html = str_replace('[[Text]]', '', $html);	
		$html = str_replace('[[Disabled]]', '', $html);			
		
		return $html;
	}
	
	// build main calendar month
	private function createCalendar(){
		$calendarHTML = $this->searchHTML($this->htmlData, 	'<!--Calendar Start-->', 	'<!--Calendar End-->');
		$weekdayHTML = $this->searchHTML($this->htmlData, 	'<!--Weekday Start-->', 	'<!--Weekday End-->');
		$weekendHTML = $this->searchHTML($this->htmlData, 	'<!--Weekend Start-->', 	'<!--Weekend End-->');
		$emptyHTML = $this->searchHTML($this->htmlData, 	'<!--Empty Start-->', 		'<!--Empty End-->');
		$weekHTML = $this->searchHTML($this->htmlData, 		'<!--Week Start-->', 		'<!--Week End-->');
	
		$dayOfWeek = date('N', mktime(12, 0, 0, $this->month, 1, $this->year));	// 0-6
	    $daysInMonth = date('t', mktime(12, 0, 0, $this->month, 1, $this->year));  // 28-31
		$weeksInMonth = ceil( ($dayOfWeek + $daysInMonth)/7 ); // 4-6
		
		$first = mktime(0,0,0,$this->month-3,1,$this->year);
		$last = mktime(0,0,0,$this->month,$daysInMonth,$this->year);
		
		$arrMonthEvents = $this->db->getEvents($this->calendarName, $first, $last);
		
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
						$addLink = $this->buildAddEventLink($this->month, $day, $this->year);
						
						$temp = str_replace("[[Add]]", $addLink, $weekendHTML);
						$temp = str_replace("[[Day]]", $day, $temp);
						$temp = str_replace("[[EventList]]", $events, $temp);
					}
					else{
						$events = $this->buildEventList($arrMonthEvents, $this->month, $day, $this->year);
						$addLink = $this->buildAddEventLink($this->month, $day, $this->year);
						
						$temp = str_replace("[[Add]]", $addLink, $weekdayHTML);						
						$temp = str_replace("[[Day]]", $day, $temp);
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
		
		return $calendarHTML;
	}
	
	private function buildAddEventLink($month, $day, $year){
		
		$timestamp = mktime(0,0,0,$month,$day,$year);
		
		$url = $this->cleanLink( $_SERVER['REQUEST_URI'] ) . '&AddEvent=' . $timestamp;
		$link = '<a href="' . $url . '">new</a>';		
		return $link;
	}
	
	private function buildWeekHeader(){
		
		$header = '';
		
		$arr = array('Sunday', 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
		
		foreach($arr as $head){
			$header .= '<td>' . $head . '</td>';
		}
	
		return '<tr class="calendar_header">' . $header . '</tr>';
	}
	
	private function buildNavControls(){
		global $wgLang;
		
		$date = $wgLang->getMonthName($this->month) . ', ' . $this->year;
		$title = "$this->calendarName<br><font size='-1'>$date</font>";
	
		$navHTML = $this->searchHTML($this->htmlData, 		'<!--Nav Start-->', '<!--Nav End-->');
		$navHTML = str_replace('[[MONTH_CONTROL]]', $this->buildMonthSelect(), $navHTML);
		$navHTML = str_replace('[[YEAR_CONTROL]]', $this->buildYearSelect(), $navHTML);
		$navHTML = str_replace('[[CALENDAR_NAME]]', $title, $navHTML);
	
		return $navHTML;
	}

	function buildMonthSelect(){
		global $wgLang;
		
		$todayBtn = "&nbsp;<input class='btn' name='today' type='submit' value='today'>";		
		$backBtn = "<input class='btn' name='monthBack' type='submit' value='<<'>&nbsp;";
		$forwardBtn = "&nbsp;<input class='btn' name='monthForward' type='submit' value='>>'>";		
		$timestamp = mktime(12,0,0,$this->month,1,$this->year);
		
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
		
		$hidden = "<input name=timestamp type=hidden value='$timestamp' size=10>"
			. "<input name=name type=hidden value='$this->calendarName' size=10>";
	
		return $backBtn . $monthSelect . $forwardBtn . $todayBtn . $hidden;
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
	
		$links = '';
	
		$date = mktime(0,0,0,$month,$day,$year);
		//$date2 = mktime(23,59,59,$month,$day,$year);
		
		foreach($arrEvents as $event){

			if( ($date >= $event['start']) && ($date <= $event['end']) ){
				$links .= '<li>' . $this->buildLink($event) . '</li>';
			}
		}

		return "<ul class='bullets'>" . $links . '</ul>';
	}
	
	private function buildLink($event){
		
		$url = $this->cleanLink($_SERVER['REQUEST_URI']) . '&EditEvent=' . $event['id'];
		$link = '<a href="' . $url . '">' . $event['subject'] . '</a>';
		
		return $link;
	}
		
    function searchHTML($html, $beginString, $endString) {
	
    	$temp = explode($beginString, $html);
    	if (count($temp) > 1) {
			$temp = explode($endString, $temp[1]);
			return $temp[0];
    	}
    	return "";
    }	
	
	private function cleanLink($url){
		
		$arr = explode('&', $url);
		
		return $arr[0];
	}
}














