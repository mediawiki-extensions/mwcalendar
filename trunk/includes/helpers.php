<?php

# this should be static only functions
class helpers{

	//private static $date_format = 'n/j/Y g:i A';
	private static $date_format = 'n/j/Y';
	
	## date functions
	public static function getDateFormat(){ return self::$date_format; }
	public static function setDateFormat($format){ self::$date_format=$format; }
	public static function date($timestamp){ return date(self::$date_format, $timestamp); }
	
	## coolie stuff
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
	
	static function is_my_calendar($calendarName){

		$arr = explode( '&', $_SERVER['REQUEST_URI'] );

		if( !isset($arr[1]) ) return true;
		
		$name = urldecode($arr[1]);
		
		if( stripos($name, $calendarName) > 0)
			return true;
	
		if( isset($_POST['calendar'])){
			if($_POST['calendar'] == $calendarName)
				return true;
		}

		return false;	
	}
}








