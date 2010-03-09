<?php

class DateHelper extends AppHelper{
	var $helpers = array('Html');
	
	function form2view($param,$format = "Y/n/j"){
		$defaults = array(
			'year'  => date('Y'),
			'month' => date('n'),
			'day'   => date('j'),
			'hour'  => null,
			'min'   => null,
			'sec'   => null,
		);
		extract($defaults);
		extract($param);
		
		$now = mktime($hour,$min,$sec,$month,$day,$year);
		if(!$now){
			return null;
		}
		return date($format,$now);
	}
}


?>