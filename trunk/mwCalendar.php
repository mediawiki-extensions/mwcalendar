<?php

# Confirm MW environment
if (!defined('MEDIAWIKI')) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

########## DEBUGGER ################
define( 'mwcalendar_debugger', 3); // 0=off, 1=standard, 2=custom, 3=all
########## DEBUGGER ################

######### OPTIONS ###############
define( 'mwcalendar_email_ical_mode', 1 ); // 0=off, 1=attachment, 2=embedded, 3=html email only
define( 'mwcalendar_email_allday_format',1 ); // 1=20101215-20101216 (Outlook?), 2=20101215T000000-20101215T325959
######### OPTIONS ###############

# set the version
define('mwcalendar_version','0.3.2'); //do not modify format
define('mwcalendar_version_label',' (beta)'); //do not modify format
define( 'mwcalendar_base_path', dirname(__FILE__) );

require_once( mwcalendar_base_path . '/includes/main.php' );
require_once( mwcalendar_base_path . '/includes/conversion.php' );
require_once( mwcalendar_base_path . '/includes/helpers.php');

# Credits	
$wgExtensionCredits['parserhook'][] = array(
    'name'=>'mwCalendar',
    'author'=>'Eric Fortin',
    'url'=>'http://www.mediawiki.org/wiki/Extension:MW_Calendar',
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
	global $wgVersion,$wgParser, $mwcDebugger;

	$wgParser->disableCache();
	
	// conversion option; no need to do any normal calendar initializations
	if($ret = run_conversion($params)) {return $ret;}	

	$calendar = new mwCalendar($params);

	$ret = $calendar->display();
	
	if(mwcalendar_debugger){
		$ret .= "<center><b><font color=red>*** DEBUG MODE ACTIVATED ***</font></b></center>";
	}
	
	$ret .= '<small>v.'.mwcalendar_version.mwcalendar_version_label.'</small><br>';
	
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




