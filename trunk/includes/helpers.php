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
}