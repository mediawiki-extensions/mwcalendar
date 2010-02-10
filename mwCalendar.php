<?php

# Confirm MW environment
if (!defined('MEDIAWIKI')) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

# set the version
define('mwcalendar_version','v.0.1');

define( 'mwcalendar_base_path', dirname(__FILE__) );

require_once( mwcalendar_base_path . '/includes/main.php' );
require_once( mwcalendar_base_path . '/includes/conversion.php' );

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
	
	$calendar = new mwCalendar($params);
	
	if($ret = run_conversion($params)) {
		return $ret;
	}

	return $calendar->begin();
}

function run_conversion($params){
	global $run_conversion, $calendar_source, $calendar_target, $calendar_startdate;
	
	$run_conversion = false;
	//$calendar_source = 'CalendarEvents:NSC Interface Calendar/MWP Team Calendar';
	$calendar_target = 'eric';
	$calendar_startdate = '7/1/1973';
	
	if( isset($calendar_source) && isset($calendar_target) && isset($calendar_startdate)  ){
		$conversion = new conversion($run_conversion);
		return $conversion->convert( $calendar_source, $calendar_target, $calendar_startdate );
	}
	
	return false;
}





