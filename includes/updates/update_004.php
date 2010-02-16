<?php

## this is included in a class/function so no need to delare any classes... just do the scripts
if($db_ver < '0.0.4'){

/*
global $wgDBprefix,$wgOut;
$dbr = wfGetDB( DB_SLAVE );
$dbw = wfGetDB( DB_MASTER );

$wgOut->addHtml("entered:");

$sql['version'] = "ALTER TABLE ".$wgDBprefix."calendar_header ADD (version varchar(255) NULL default '');";

$res = $dbr->query("SHOW COLUMNS FROM ".$wgDBprefix."calendar_header");
	while (list($column, $sqldata) = each($sql)){
		$dbr->ignoreErrors(true);
		$res = $dbr->query( "SELECT $column FROM ".$wgDBprefix."calendar_header LIMIT 0,1" );
		$dbr->ignoreErrors(false);
		if( !$res ) {
			//$wgOut->addHtml($sqldata);
			//$dbw->query($sqldata); 
		}				
	}
*/

$wgOut->addHtml("Updated to: (0.0.4) -Success!<br>");
}