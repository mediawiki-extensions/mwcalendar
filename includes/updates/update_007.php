<?php

## this is included in a class/function so no need to delare any classes... just do the scripts
if($db_ver < '0.0.7'){
	
	// cleanup allday flag...set all events to allday
	$dbw->query("UPDATE $calendar_events SET allday=1; ");

$wgOut->addHtml("...update to: (0.0.7) -Success!<br>");
}