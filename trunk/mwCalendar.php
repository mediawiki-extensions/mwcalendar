<?php

# Confirm MW environment
if (!defined('MEDIAWIKI')) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

# set the version
define('mwcalendar_version','0.1.0'); //do not modify format
define('mwcalendar_version_label',' (beta)'); //do not modify format

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
    'version'=> mwcalendar_version . mwcalendar_version_label
);

$wgExtensionFunctions[] = "mwCalendar";
$wgExtensionMessagesFiles['mwCalendar'] = mwcalendar_base_path . "/includes/i18n.php";

$wgShowSQLErrors=true;

function mwCalendar() {
	global $wgParser;
	$wgParser->setHook( "mwcalendar", "launchCalendar" );
	wfLoadExtensionMessages( 'mwCalendar' ); 
}

function launchCalendar($paramstring, $params = array()) {
	global $wgVersion;
	
	// conversion option; no need to do any normal calendar initializations
	if($ret = run_conversion($params)) {return $ret;}	

	$calendar = new mwCalendar($params);

	$ret = $calendar->display() . '<small>v.'.mwcalendar_version.mwcalendar_version_label.'</small><br>';
	
	if( $params['debugger'] ) $ret .=  mwcDebugger::get();
	
	##version check!
	if(version_compare($wgVersion, '1.14.0', '>=')) 
		return $ret;
	else
		return "You must be running MediaWiki version (1.14.0) or higher. Sorry, your version is ($wgVersion).";
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




