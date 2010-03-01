<?php

# Confirm MW environment
if (!defined('MEDIAWIKI')) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

require_once( mwcalendar_base_path . '/includes/EventHandler.php');
require_once( mwcalendar_base_path . '/includes/Database.php');
require_once( mwcalendar_base_path . '/includes/helpers.php');

class mwCalendar{
	
	var $month = '';
	var $day = '';
	var $year = '';
	var $now = array();
	
	var $htmlData = '';
	var $db;
	
	var $calendarName;
	var $title = '';
	var $tabHtml = '';
	
	var $subject_max_length;
	var $event_list = 0;
	
	public function mwCalendar($params){
		global $wgOut,$wgTitle, $wgScript, $wgScriptPath;	
		
		$list = '';	
		
		$this->setDefaults($params); ## RUN FIRST ##
		
		// set the calendar's initial date
		$now = $this->now = getdate();
		
		$this->month = $now['mon'];
		$this->year = $now['year'];
		$this->day = $now['mday'];		
		
		$this->title = $wgScript . '?title=' . $wgTitle->getPrefixedText();
		
		$this->db = new CalendarDatabase;
		$this->db->validateVersion(); //make sure db and files match		
		
		## this basically calls a function that evaluates $_POST[] events (new, delete, cancel, etc)
		## no need to do anything else in the calendar until any db updates have completed
		EventHandler::CheckForEvents(helpers::is_my_calendar($this->calendarName) );	
			
		## build the mw user-list which should only be users with active email
		$arrUsers = $this->db->getDatabaseUsers();
		while(list($user,$realname) = each($arrUsers)){
			$realname = htmlentities($realname, ENT_QUOTES);
			$list .= "<option>$user($realname)</option>";
		}
			
		## pull in all the html template forms we have
		$addEventHtml = file_get_contents( mwcalendar_base_path . "/html/AddEvent.html");	
		$batchHtml = file_get_contents( mwcalendar_base_path . "/html/batchadd.html");
		$this->htmlData = file_get_contents( mwcalendar_base_path . "/html/default.html");
		
		$addEventHtml = str_replace('[[SELECT_OPTIONS]]',$list,$addEventHtml);	
		
		## building my own sytlesheets and javascript links...
		$this->stylesheet = $this->buildStylesheet( array('DatePicker.css','tabber.css','default.css') );
		$this->javascript = $this->buildJavascript( array('DatePicker.js','tabber.js','InviteSelect.js') );
		
		## build the addEvent and batch tabs		
		$tab1 = $this->buildTab( helpers::translate('mwc_event'), $addEventHtml);
		$tab2 = $this->buildTab( helpers::translate('mwc_batch'),$batchHtml);
		$this->tabHtml  = '<div class="tabber">' . $tab1 . $tab2 . '</div>';	
	}
	
	private function buildStylesheet($arrStyles){
		global $wgScriptPath;
		$ret = chr(13) . "<!-- BEGIN CALENDAR CSS -->".chr(13);
		
		foreach($arrStyles as $style){
			$ret .= '<link rel="stylesheet" href="'.$wgScriptPath.'/extensions/mwcalendar/html/'.$style.'" type="text/css" media="screen" />'.chr(13);		
		}
		
		$ret .= "<!-- END CALENDAR CSS -->".chr(13);
		
		return $ret;
	}
	
	private function buildJavascript($arrJavaScripts){
		global $wgScriptPath;
		$ret = chr(13) . "<!-- BEGIN CALENDAR JS -->".chr(13);
		
		foreach($arrJavaScripts as $javascript){
			$ret .= '<script type="text/javascript" src="'.$wgScriptPath.'/extensions/mwcalendar/html/'.$javascript.'"></script>'.chr(13);		
		}
		
		$ret .= "<!-- END CALENDAR JS -->".chr(13);
		
		return $ret;
	}

	private function buildTab($name,$tabBody){
		global $wgParser;

		$tab = '<div class="tabbertab" title='.$name.'>'
			. '<p>'.$tabBody.'</p>'
			. '</div>';

		return $tab;
	}
	
	## SET DEFAULTS ##
	private function setDefaults($params){

		$this->calendarName = isset( $params['name'] ) ? $params['name'] :  helpers::translate('mwc_default_name');
		$this->subject_max_length = isset( $params['sublength'] ) ? $params['sublength'] : 15;	
		$this->event_list = isset( $params['eventlist'] ) ? $params['eventlist'] : 0;		
	}
	
	public function begin(){
		global $wgOut;
	
		$html = $this->stylesheet;
		$html .= $this->javascript;

		// determine what we need to display
		$arrUrl = explode( '&', $_SERVER['REQUEST_URI'] );

		$urlEvent[0] = '';
		if( isset($arrUrl[2]) ){
			$urlEvent = explode( '=', $arrUrl[2] ); #ex: EditEvent=45
		}

		if( helpers::is_my_calendar($this->calendarName) ){
			if($urlEvent[0] == 'AddEvent' ){
				return $html . $this->url_AddEvent($arrUrl[0],$urlEvent[1]);			
			}
			
			if($urlEvent[0] == 'EditEvent' ){
				return $html . $this->url_EditEvent($arrUrl[0],$urlEvent[1]);
			}
		}
		
		## load normal calendar
		$cookie_name = helpers::cookie_name( $this->calendarName ); 

		if( isset($_COOKIE[$cookie_name]) ){
			$date = getdate($_COOKIE[$cookie_name]); //timestamp value
			$this->month = $date['mon'];
			$this->year = $date['year'];
		}
		
		if($this->event_list > 0){
			$html .= $this->createEventList();
		}else{
			$html .= $this->createCalendar();
		}
		
		$html = str_replace('[[SafeURL]]',$arrUrl[0],$html);
		$html = $this->clearHtmlTags($html);
		
		return $html;
	}
	
	private function url_AddEvent($safeUrl, $timestamp){
		$html = $this->tabHtml;

		$startDate = $endDate = helpers::date($timestamp);

		// update the 'hidden' input field so we retain the calendar name for the db update
		$html = str_replace('[[CalendarName]]', $this->calendarName, $html);
		$html = str_replace('[[EventID]]', null, $html);
		$html = str_replace('[[Start]]', $startDate, $html);
		$html = str_replace('[[End]]', $endDate, $html);
		$html = str_replace('[[Disabled]]', 'disabled', $html); 
		
		$html = str_replace('[[SafeURL]]',$safeUrl,$html);
		$html = $this->clearHtmlTags($html);
		
		return $html;	
	}
	
	private function url_EditEvent($safeUrl, $eventID){
		global $wgUser;
		$currentUser = $wgUser->getName();
		
		$html = $this->tabHtml;
		$strInvites = '';
		$lastedited = '';
		$arr_invites = array();
		$tranlated = '';
		
		$event = $this->db->getEvent( $eventID );

		$start = helpers::date( $event['start' ] );
		$end = helpers::date( $event['end'] );		
					
		if(isset($event['editeddate'])){
			$editeddate = helpers::date( $event['editeddate']);					
			$lastedited = helpers::translate('mwc_last_edited_by') . ': ' . $event['editedby'] . " ($editeddate)";
		}

		$createddate = helpers::date($event['createddate']);					
		$createdby = helpers::translate('mwc_created_by') .": " . $event['createdby'] . " ($createddate)";			
		
		$this->makeSafeHtml($event);
		
		$start = helpers::date( $event['start']);
		$end = helpers::date( $event['end']);
		
		// build invite(notify) list
		$arr_invites = unserialize($event['invites']);
		
		if(is_array($arr_invites)){
			foreach($arr_invites as $invite) {
				$user = User::newFromName( trim($invite) );
				if($user){
					$strInvites .= $invite . "(".$user->getRealName().")&#10;";
				}
			}
		}
			
		// update the 'hidden' input field so we retain the calendar name for the db update
		$html = str_replace('[[CalendarName]]', $this->calendarName, $html);
		$html = str_replace('[[EventID]]', $event['id'], $html);	
		$html = str_replace('[[Subject]]', $event['subject'], $html);	
		$html = str_replace('[[Location]]', $event['location'], $html);			
		$html = str_replace('[[Invites]]', $strInvites, $html);	
		$html = str_replace('[[Start]]', $start, $html);	
		$html = str_replace('[[End]]', $end, $html);				
		$html = str_replace('[[Text]]', $event['text'], $html);	
		$html = str_replace('[[LastEdited]]', $lastedited, $html);
		$html = str_replace('[[CreatedBy]]', $createdby, $html);	

		$html = str_replace('[[SafeURL]]',$safeUrl,$html);
		
		//$arrEvent = $this->db->getEvent( $event['id'] );
		
		// disable delete for users that didnt create the event... only creator or admin can delete
		$isValid = User::newFromName( $event['createdby'] )->getID();
		$isAdmin =  in_array('sysop', $wgUser->getGroups());
		if( ($currentUser !=  $event['createdby']) && ($isValid) && (!$isAdmin) ){
			$tranlated = helpers::translate('mwc_delete_title');
			$html = str_replace('[[Disabled]]', "disabled title='$tranlated'", $html); 
		}
		
		return $this->clearHtmlTags($html);	
	}
	
	private function makeSafeHtml(&$arrEvent){
		
		$arrEvent['subject'] = htmlentities($arrEvent['subject'], ENT_QUOTES);
		//$arrEvent['invites'] = htmlentities($arrEvent['invites'], ENT_QUOTES); //not needed in a <textarea>
		$arrEvent['location'] = htmlentities($arrEvent['location'], ENT_QUOTES);

	}
	
	// this function removes or defaults any HTML tags that havent been overwritten
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
		$html = str_replace('[[FOOTER]]', '', $html);	
		$html = str_replace('[[LastEdited]]', '', $html);
		$html = str_replace('[[CreatedBy]]', '', $html);	
		$html = str_replace('[[JSDateFormat]]', helpers::getDateFormat(), $html);		
		$html = str_replace('[[Today_CSS]]', '', $html);	  
		
		## tranlated text labels
		$html = str_replace('[[SubjectText]]', helpers::translate('mwc_event_subject'), $html);
		$html = str_replace('[[LocationText]]', helpers::translate('mwc_event_location'), $html);
		$html = str_replace('[[NotifyText]]', helpers::translate('mwc_event_notify'), $html);
		$html = str_replace('[[StartText]]', helpers::translate('mwc_event_start'), $html);
		$html = str_replace('[[EndText]]', helpers::translate('mwc_event_end'), $html);
		$html = str_replace('[[AlldayText]]', helpers::translate('mwc_event_allday'), $html);
		$html = str_replace('[[AddText]]', helpers::translate('mwc_event_add'), $html);
		$html = str_replace('[[DeleteText]]', helpers::translate('mwc_event_delete'), $html);
		$html = str_replace('[[SaveText]]', helpers::translate('mwc_event_save'), $html);
		$html = str_replace('[[CloseText]]', helpers::translate('mwc_event_close'), $html);
		
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
		
		$first = mktime(0,0,0,$this->month-12,1,$this->year);
		$last = mktime(23,59,59,$this->month,$daysInMonth,$this->year);
		
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
					
					if( helpers::isToday($this->month, $day, $this->year) ){
						$temp = str_replace("[[Today_CSS]]", 'today_css', $temp);
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
	
	private function createEventList(){
	
		//we're only going to pull (+/-) 12 months
		$first = mktime(0,0,0,$this->month-12,1,$this->year);
		$last = mktime(23,59,59,$this->month+12,28,$this->year);

		$eventListRange = $this->event_list;
		
		$arrMonthEvents = $this->db->getEvents($this->calendarName, $first, $last);
		
		$extra_css = '';
		$events = '';
		
		$day = $this->day;
		$month = $this->month;
		$year = $this->year;
		
		for($i=0; $i < $eventListRange; $i++){
							
			$dayOfWeek = date('N', mktime(12, 0, 0, $month, $day, $year));	// 0-6
			if($dayOfWeek > 5) $extra_css = 'calendar_weekend_empty';
			
			$ret = $this->buildEventList($arrMonthEvents, $month, $day, $year);
			
			if($ret){
				if(helpers::isToday($month,$day,$year)) $extra_css = 'today_css';
				
				$add = "<td text-align=right>" . $this->buildAddEventLink($month, $day, $year) . "</td>";
				$date = "<tr><td class='eventlist_header'>" . date( 'l, M j', mktime(0,0,0,$month, $day, $year)) . "</td>$add</tr>";
				$ret = "<tr><td colspan=2 class='eventlist_events $extra_css'>" . $ret . "<br></td></tr>";
				$events .= $date . $ret;
			}
			$extra_css = '';
			helpers::getNextValidDate($month,$day,$year);//updated by pointer reference
		}
		
		return "<table class='day_cell_child' width=100%>" . $events . "</table>";
	}
	
	private function buildAddEventLink($month, $day, $year){
		
		$timestamp = mktime(0,0,0,$month,$day,$year);
		
		//$url = $this->cleanLink( $_SERVER['REQUEST_URI'] ) . '&AddEvent=' . $timestamp;
		$url = $this->cleanLink( $this->title ) . '&Name='.$this->calendarName.'&AddEvent=' . $timestamp;
		
		$link = '<a href="' . $url . '">'.helpers::translate('mwc_new').'</a>';		
		return $link;
	}
	
	private function buildWeekHeader(){
		
		$header = '';
	
		$i= 1;
		while( $i <= 7 ){
			$header .= '<td>' . helpers::translate($i++, 'weekday') . '</td>';
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
		
		$today = helpers::translate('mwc_today');
		$todayBtn = "&nbsp;<input class='btn' name='today' type='submit' value='$today'>";		
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

		if(!isset($arrEvents)) return '';
	
		$min = mktime(0,0,0,$month,$day,$year);  // calendar day (12:00am)
		$max = mktime(23,59,59,$month,$day,$year); // calendar day (11:59pm))
		
		foreach($arrEvents as $event){
			$bFound = false;
			
			if( ($min >= $event['start']) && ($min <= $event['end']) ) $bFound = true;
			if( ($max >= $event['start']) && ($max <= $event['end']) ) $bFound = true;
			if( ($min <= $event['start']) && ($max >= $event['start']) ) $bFound = true;
			
			if($bFound){
				$links .= '<li>' . $this->buildLink($event) . '</li>';
			}
		}
		
		if($links != '')
			return "<ul class='bullets'>" . $links . '</ul>';
			
		return '';
	}
	
	private function buildLink($event){
		
		$limit = $this->subject_max_length;
		$subject = $event['subject'];
		
		$url = $this->cleanLink($this->title) . '&Name='.$this->calendarName.'&EditEvent=' . $event['id'];
		$link = '<a href="' . $url . '">' . $subject . '</a>';
		
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














