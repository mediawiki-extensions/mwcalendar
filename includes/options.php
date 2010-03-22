<?php

require_once( mwcalendar_base_path . '/includes/Database.php');

class Options{

	function showOptions($calendar){
		$html = "<form name=frmOptions method=POST>";
		$html .= "<h2>Options</h2>";
		$html .= $this->buildOptionList($calendar);
		
		$html .= "<br><br><input type=submit name='SaveOptions' value='Save' />";
		$html .= "<input type=hidden name='calendar' value='$calendar' />";
		$html .= "</form>";
		
		return $html;
	}
	
	function buildOptionList($calendar){
		$db = new CalendarDatabase();
		
		$arrOptions = $db->getOptions($calendar);
		
		$summary_js = array_key_exists('summary_js', $arrOptions) ? ($arrOptions['summary_js'] ? 'checked' : '') : 'checked';
	
		$options = '';
		$options .= "<input type=checkbox name='summary_js' $summary_js />&nbsp; Javascript summary popups\n";
	
		return $options;
	}
}

