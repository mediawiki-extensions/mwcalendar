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
		
		$date = getdate( strtotime($startdate) );
		
		$month = $date['mon'];
		$day = $date['mday'];
		$year = $date['year'];
		
		//$date = "$month-$day-$year";
		
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
		
		$temp1 = explode('/', $title); // 1-1-2009 -Event 1
		$grab_tail = array_pop($temp1);
		$date = explode('-', $grab_tail);	

		if(count($date) < 3) {
			return "[[Error Parsing]]: $title <br>";
		}
		
		## add templates
		if(count($date) == 3) {
			 $this->add_from_template($title,$body,$target);
			 return $title . '<br>';
		}
		
		## if we're here, we're adding standard events
		
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
	
	public function add_from_template($title, $body, $target){
		$displayText = "";
		$arrEvent = array();
		
		$temp1 = explode('/', $title);
		$grab_tail = array_pop($temp1); // 2-2010 -Template
		$date = explode('-', $grab_tail);
		
		if(count($date) != 3) return "[[Error Parsing]]: $title <br>";
		
		$db = new CalendarDatabase();
		
		$month = trim($date[0]);
		$year = trim($date[1]);		
		
		$displayText = $body;//$article->fetchContent(0,false,false);
	
		$arrAllEvents=explode(chr(10),$displayText);
		if (count($arrAllEvents) > 0){
			for($i=0; $i<count($arrAllEvents); $i++){
				$arrEvent = explode("#",$arrAllEvents[$i]);
				
				if(!isset($arrEvent[1])) continue;//skip 
				
				if(strlen($arrEvent[1]) > 0){
	
					//$day = $arrEvent[0];
					$arrRepeat = explode("-",$arrEvent[0]);
					
					$startDay = $arrRepeat[0];
					$endDay = $arrRepeat[0];	
					
					if(count($arrRepeat) > 1){
						$endDay = $arrRepeat[1];
					}
					
					$arr['calendar'] = $target;//$db->getCalendarID($target);
					$arr['subject'] = $arrEvent[1];
					$arr['location'] = '';
					$arr['start']  = mktime(0,0,0,$month,$startDay,$year);
					$arr['end']  = mktime(0,0,0,$month,$endDay,$year);		
					$arr['allday'] = 1;
					$arr['text']  = '';	
					$arr['createdby'] = 'conversion';
					$arr['invites'] = '';	
					
					if($this->go == true) $db->setEvent($arr);
				}	
			}
		}	
	}
}






