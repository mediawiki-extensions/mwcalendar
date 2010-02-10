<?php

require_once( mwcalendar_base_path . '/includes/Database.php');

class conversion{
	var $go = false;
	
	function conversion($bGO){
		$this->go = $bGO;
	}
    // builds the day events into memory
	// uses prefix seaching (NS:page/name/date)... anything after doesn't matter
    function convert($source, $target, $startdate) {
		
		$date = getdate( strtotime($start_date) );
		
		$month = $date['mon'];
		$day = $date['mday'];
		$year = $date['year'];
		
		$date = "$month-$day-$year";
		
		$search = $source;
		$pages = PrefixSearch::titleSearch( $search, '10000');

		$count=0;
		foreach($pages as $page) {
			$article = new Article(Title::newFromText($page));
			
			$title = $article->getTitle()->getText();
			$body = $article->fetchContent(0,false,false);
			
			$titles .= $this->create_event($title,$body,$target);	
			$count++;
		}
		return $titles;
		//return "$count records converted!";
	}
	
	function create_event($title, $body, $target){
		
		$temp1 = explode('/', $title); //1-1-2009 -Event 1
		$grab_tail = array_pop($temp1);
		$date = explode('-', $grab_tail);	

		//return count($date) . "<br>";
		if(count($date) != 4) return "[[Error Parsing]]: $title <br>";
		
		$month = trim($date[0]);
		$day = trim($date[1]);
		$year = trim($date[2]);
		
		$temp2 = explode("\n", $body);		
		$subject = array_shift($temp2); //take the 1st element off the array (subject line)
		
		if(trim($subject) == '') return "[[Error Parsing]]: $title <br>";
		
		$text = implode("\n", $temp2); //assemple the remaining body

		$db = new CalendarDatabase();
		
		$arr['calendar'] = $target;//$db->getCalendarID($target);
		$arr['subject'] = $subject;
		$arr['location'] = '';
		$arr['start']  = mktime(0,0,0,$month,$day,$year);
		$arr['end']  = mktime(0,0,0,$month,$day,$year);		
		$arr['allday'] = 1;
		$arr['text']  = $text;	
		$arr['createdby'] = 'conversion';
		$arr['invites'] = '';
	
		if($this->go == true) $db->setEvent($arr);

		//return $text . " -x<br>'";
		return $title . '<br>';
	}
}






