<?php
require_once( mwcalendar_base_path . '/includes/options.php');
# this should be static only functions
class helpers{

	private static $date_format = 'n/j/Y'; //used in email, addevent, etc
	private static $time_format = 'g:i A'; //used in email, addevent, etc
	private static $time_format_event = '(g:ia) '; //used for the time prefix in the calendar eventlist
	private static $debug = false;
	
	## date functions
	public static function getDateFormat(){ return self::$date_format; }
	public static function setDateFormat($format){ self::$date_format=$format; }
	public static function date($timestamp){ return date(self::$date_format, $timestamp); }
	public static function time($timestamp){ return date(self::$time_format, $timestamp); }
	public static function event_time($timestamp){ return date(self::$time_format_event, $timestamp); }
	
	public static function enableDebug(){ self::$debug = true; }
	
	## cookie stuff
	public static function cookie_name($calendar_name){
		global $wgTitle;
		$wiki_page = $wgTitle->getPrefixedText();

		$cookie_name = $wiki_page . "_" . $calendar_name;
		
		$ret = preg_replace('/(\.|\s)/',  '_', $cookie_name); //replace periods and spaces		
		
		return $ret;
	}
	
	public static function isToday($month, $day, $year){
		$today = getdate();
		
		if( ($month == $today['mon'])&&($day == $today['mday'])&&($year == $today['year']) ){
			return true;
		}	
		
		return false;
	}
	
	// clean usernames before filing into database
	public static function invites_str_to_arr($str_invites){
		
		$str_invites = trim($str_invites);
		
		//removes "(realname)" including trailing spaces
		$str_invites = preg_replace('[(\s*\()+.+(\))]', '', $str_invites); 
		
		return explode("\r\n", $str_invites); //javascript uses "\n"	
	}
	
	static function getNextValidDate(&$month, &$day, &$year){

		$seconds = 86400; //1 day
		$arr = getdate(mktime(12, 0, 0, $month, $day, $year) + $seconds);
		
		$day = $arr['mday'];
		$month = $arr['mon'];
		$year = $arr['year'];
		
		return $arr;
	}
	
 	static function is_my_calendar($key){
		
		$arr = explode( '&', $_SERVER['REQUEST_URI'] );

		if(isset($arr[1])){
			$url_name = urldecode($arr[1]);
		
			if( stripos($url_name, $key) > 0)
				return true;
		}
	
		if( isset($_POST['calendar'])){
			helpers::debug("is_my_calendar-\$_POST: ". $_POST['CalendarKey']);	
				if($_POST['CalendarKey'] == $key)
					return true;
		}

		return false;	
	} 
	
	static function translate($value, $key=""){
		global $wgLang;
		
		switch($key){
		case 'month':
			return $wgLang->getMonthName($value);
			
		case 'month-gen': //genitive case or possessive case
			return $wgLang->getMonthNameGen($value);
			
		case 'month_short':
			return $wgLang->getMonthAbbreviation($value);
			
		case 'weekday':
			return $wgLang->getWeekdayName($value);
			
		default:
			//return $wgLang->iconv("", "UTF-8", Common::translate($value));
			return utf8_encode(wfMsg($value));
			//return wfMsg($value);
		}
		return "";
	}
	
	public static function debug($data, $mode=1){
		if(!mwcalendar_debugger) return;
		
		if( ($mode != mwcalendar_debugger) 
			&& (mwcalendar_debugger != 3) ) return;
		
		$path = mwcalendar_base_path . "/calendar.log";		
		$log = date('[j-M-Y H:i:s]') . " $data\r\n";
		error_log($log, 3, $path);	
	}	
	
	public static function curPageURL() { 
		 $pageURL = 'http'; 
		 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";} 
				 $pageURL .= "://"; 
		 if ($_SERVER["SERVER_PORT"] != "80") { 
				 $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"]; 
		 } else { 
				 $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]; 
		 } 
		 return $pageURL; 
 } 
 
}








