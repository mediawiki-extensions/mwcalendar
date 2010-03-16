<?php

class mwcDebugger{

	//private static $debug_data = '';
	
	public static function set($data){
		$path = mwcalendar_base_path . "/calendar.log";		
		$log = date('[j-M-Y H:i:s]') . " $data\r\n";
		error_log($log, 3, $path);	
	}

	public static function get(){	
		//return self::$debug_data;
	}
}