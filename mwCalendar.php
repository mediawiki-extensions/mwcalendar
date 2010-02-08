<?php

# Confirm MW environment
if (!defined('MEDIAWIKI')) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

require_once( dirname(__FILE__) . '/includes/main.php' );

# set the version
define('kenyu73_mwcalendar','v.0.1');

# Credits	
$wgExtensionCredits['parserhook'][] = array(
    'name'=>'mwCalendar',
    'author'=>'Eric Fortin',
    'url'=>'',
    'description'=>'MediaWiki Calendar',
    'version'=> kenyu73_mwcalendar
);



$wgExtensionFunctions[] = "mwCalendar";

function mwCalendar() {
	global $wgParser;
	$wgParser->setHook( "calendar", "launchCalendar" );
}

function launchCalendar($paramstring, $params = array()) {
	
	$calendar = new mwCalendar;
	
	if( !isset($params["name"]) ) $params["name"] = "Public";
	$name = $params["name"];
	
	global $wgUser;
	//$wgUser->sendMail('calendar test', 'body');
	//mail( 'eric.fortin@ge.com', 'Test sub', '', '' );
	//return $wgUser->getName();
	//return date('D n/j/Y H:i:s', 1266771599) . '<br>' . date('D n/j/Y H:i:s', 1266814800);
	//	return mktime(date_parse('2/6/2010 11:59 PM'));
	return $calendar->begin($name);
}

function test(){
}










