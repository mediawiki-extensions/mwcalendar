<?php

if( version_compare($db_ver,'0.3.3','<') ){	
	// add options column to 'calendar_header'
	$sql = "ALTER TABLE ".$wgDBprefix."calendar_header ADD (options mediumtext default ''); ";
	$dbw->query($sql);

$wgOut->addHtml("...update to: (0.3.3) -Success!<br>");
}