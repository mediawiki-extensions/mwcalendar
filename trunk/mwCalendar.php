<?php

# Confirm MW environment
if (!defined('MEDIAWIKI')) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

# set the version
define('mwcalendar_version','0.0.3'); //do not modify format

define( 'mwcalendar_base_path', dirname(__FILE__) );

require_once( mwcalendar_base_path . '/includes/main.php' );
require_once( mwcalendar_base_path . '/includes/conversion.php' );
require_once( mwcalendar_base_path . '/includes/helpers.php');

# Credits	
$wgExtensionCredits['parserhook'][] = array(
    'name'=>'mwCalendar',
    'author'=>'Eric Fortin',
    'url'=>'',
    'description'=>'MediaWiki Calendar',
    'version'=> mwcalendar_version
);

$wgExtensionFunctions[] = "mwCalendar";
$wgShowSQLErrors=true;

function mwCalendar() {
	global $wgParser;
	$wgParser->setHook( "mwcalendar", "launchCalendar" );
}

function launchCalendar($paramstring, $params = array()) {
//return strtotime('2-22-10');
	// conversion option; no need to do any normal calendar initializations
	if($ret = run_conversion($params)) {return $ret;}	

	$calendar = new mwCalendar($params);

	return $calendar->begin() . '<small>v.'.mwcalendar_version.'</small><br>';;
}

// this will query any wiki-page calendar previous used and port that 
// data into this new database calendar
function run_conversion($params){
	global $run_conversion, $calendar_source, $calendar_target, $calendar_startdate;
	
	# $calendar_source - ex: "Calendar:Main Page/Team Calendar"
	# $calendar_target - ex: "Supprot Team"
	# $calendar_startdate - ex: "1/1/2009" - any standard date would work as php auto detects the date
	
	# $run_conversion: (careful, this WILL create duplicates... run only ONCE!
		# true - run conversion and update database
		# false (default) - only display pages found - trial run
	
	if( isset($calendar_source) && isset($calendar_target) && isset($calendar_startdate)  ){
		$conversion = new conversion($run_conversion);
		return $conversion->convert( $calendar_source, $calendar_target, $calendar_startdate );
	}
	
	return false;
}




