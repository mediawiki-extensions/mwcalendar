<?php

## this is included in a class/function so no need to delare any classes... just do the scripts
if( version_compare($db_ver,'0.0.3','<') ){
	
	// clean out invites... re-did them completely
	$dbw->query("UPDATE $calendar_events SET invites=''; ");

$wgOut->addHtml("...update to: (0.0.3) -Success!<br>");
}