<?php

# Confirm MW environment
if (!defined('MEDIAWIKI')) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

# set the version
define('mwcalendar_version','v.0.1');

define( 'mwcalendar_base_path', dirname(__FILE__) );

require_once( mwcalendar_base_path . '/includes/main.php' );

# Credits	
$wgExtensionCredits['parserhook'][] = array(
    'name'=>'mwCalendar',
    'author'=>'Eric Fortin',
    'url'=>'',
    'description'=>'MediaWiki Calendar',
    'version'=> mwcalendar_version
);

$wgExtensionFunctions[] = "mwCalendar";

function mwCalendar() {
	global $wgParser;
	$wgParser->setHook( "mwcalendar", "launchCalendar" );
}

function launchCalendar($paramstring, $params = array()) {
	global $wgScriptPath, $wgScript;
	
	//return mwcalendar_base_path;
	//return $wgScriptPath; //    	/mediawiki 
	//return $wgScript; //			/mediawiki/index.php 
	//return __FILE__; // 			C:\Inetpub\wwwroot\mediawiki\extensions\mwcalendar\mwCalendar.php 
	//return dirname(__FILE__); //	C:\Inetpub\wwwroot\mediawiki\extensions\mwcalendar 	
	
	$calendar = new mwCalendar;
	
	if( !isset($params["name"]) ) $params["name"] = "Public";
	$name = $params["name"];

	return $calendar->begin($name);
}

function test(){


}










