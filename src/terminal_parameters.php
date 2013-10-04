<?php

/**
 * Get parameters sent from terminal
 * @license LGPL v3
 * @version 2013-10-04
 * @param $required array required parameters
 * @param $optionnal array optionnal parameters
 * @param $start int number of parameters from start to ignore
 * @param $end int number of parameters from end to ignore
 * @param $help string|array parameter(s) expected to display help or null to disable help
 * @throws Exception when parameters sent from are not correct
 * @return array parameters
 */
function terminal_parameters($required,$optional=array(),$start=0,$end=0,$help=array('h','help')) {
	// Build expected parameters
	$params_exp = array();
	foreach(array_merge($required,$optional) as $key => $param) {
		if(is_array($param)) {
			foreach($param as $param_2) {
				$params_exp[strtolower($param_2)] = $key;
			}
		} else {
			$params_exp[strtolower($param)] = $key;
		}
	}
	 
	// Remove parameters to ignore
	if($end == 0) {
		$params_given = array_slice($_SERVER['argv'],$start+1);
	} else {
		$params_given = array_slice($_SERVER['argv'],$start+1,-$end);
	}
	 
	// Get values associated with keys
	$params_in = array(); $last_key = null;
	for($i=0;isset($params_given[$i]);$i++) {
		// Check if it is a key or a value
		if(preg_match('/-{1,2}(.*)/',$params_given[$i],$matches)) {
			// Get key
			$key = strtolower($matches[1]);
				
			// Help ?
			if($help != null && (!is_array($help) && $key == $key || is_array($help) && in_array($key,$help))) {
				$params = '';
				foreach($required as $key => $param) {
					$params .= '-'.(is_array($param) ? implode('|',$param) : $param).' value ';
				}
				foreach($optional as $key => $param) {
					$params .= '[-'.(is_array($param) ? implode('|',$param) : $param).' value] ';
				}
				die('Usage : '.$_SERVER['argv'][0].' '.$params."\n" );
			}
			
			// Check if it is an expected parameter
			if(!isset($params_exp[$key])) {
				throw new Exception('Parameter\'s name "'.$key.'" is not expected');
			}
			
			// Check if it is a parameter already defined
			if(isset($params_in[$key])) {
				throw new Exception('Parameter\'s name "'.$key.'" match to several values');
			}
			
			// Check if last key is associated to a value
			if($last_key != null) {
				$params_in[$last_key] = true;
			}
			
			// Save key
			$last_key = $key;
		} else {
			// Check if last key is defined
			if($last_key == null) {
				throw new Exception('Parameter\'s value "'.$params_given[$i].'" is not associated to a name');
			}
				
			// Save value
			$params_in[$key] = $params_given[$i];
				
			// Erase last key
			$last_key = null;
		}
	}
	
	// Check if last key is associated to a value
	if($last_key != null) {
		$params_in[$last_key] = true;
	}
	
	// Math expected parameters to given parameters
	$params_out = array_fill(0,count(array_merge($required,$optional)),null);
	foreach($params_in as $key => $value) {
		$params_out[$params_exp[$key]] = $value;
	}
	 
	// Check required parameters
	for($i=0;$i<count($required);$i++) {
		if($params_out[$i] === null) {
			throw new Exception('Parameter "'.(is_array($required[$i]) ? array_shift($required[$i]) : $required[$i]).'" is required');  
		}
	}
	 
	// Return parameters
	return $params_out;
}