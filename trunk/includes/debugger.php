<?php

class mwcDebugger{

	private static $debug_data = '';
	
	public static function set($data){
		self::$debug_data .= 'debugger ==> ' . $data . "<br>";
	}

	public static function get(){	
		return self::$debug_data;
	}

}