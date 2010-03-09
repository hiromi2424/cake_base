<?php

class JpFormHelper extends FormHelper{
	function beforeRender(){
		$this->options['month'] = array_combine(range(1,12),range(1,12));
	}
	
	function dateTime($fieldName, $dateFormat='YMD', $timeFormat = 24, $selected = null, $attributes = array()) {
		if(empty($attributes)){
			$attributes['separator'] = array();
		}
		$attributes += array('empty' => false);
		
		$separator = '-';
		if(isset($attributes['separator'])){
			$separator = $attributes['separator'];
			$attributes['separator'] = '-';
		}
		
		if(!isset($attributes['monthNames'])){
			$attributes['monthNames'] = false;
		}
		$date = parent::dateTime($fieldName,$dateFormat,       null,$selected,$attributes);
		$time = parent::dateTime($fieldName,       null,$timeFormat,$selected,$attributes);
		
		if(is_array($separator)){
			$defaults = array(
				'year'   => __('年',true),
				'month'  => __('月',true),
				'day'    => __('日',true),
				'hour'   => __('時',true),
				'minute' => __('分',true),
				'second' => __('秒',true),
			);
			
			$separator = array_merge($defaults,$separator);
			$format = array();
			if($dateFormat != 'NONE' && !empty($dateFormat)){
				foreach (preg_split('//', $dateFormat, -1, PREG_SPLIT_NO_EMPTY) as $char) {
					switch ($char) {
						case 'Y':
							$format[] = $separator['year'];
						break;
						case 'M':
							$format[] = $separator['month'];
						break;
						case 'D':
							$format[] = $separator['day'];
						break;
					}
				}
			}
			preg_match_all('|<select.*?>.+?</select>|ms',$date,$matches);
			
			$date = "";
			for($i = 0; $i < count($matches[0]); $i++){
				$date .= $matches[0][$i] . $format[$i];
			}
			
			
			$format = array();
			if($timeFormat != 'NONE' && !empty($timeFormat)){
				switch($timeFormat){
					case 12:
						$format[] = $separator['hour'];
						$format[] = $separator['minute'];
						$format[] = '';
					case 24:
						$format[] = $separator['hour'];
						$format[] = $separator['minute'];
						break;
					default:
						break;
				}
			}
			
			preg_match_all('|<select.*?>.+?</select>|ms',$time,$matches);
			
			$time = "";
			for($i = 0; $i < count($matches[0]); $i++){
				$time .= $matches[0][$i] . $format[$i];
			}
			
		}elseif(is_string($separator)){
			$date = str_replace('</select>-','</select>'.$separator,$date);
		}
		return $date.$time;
	}
}

?>