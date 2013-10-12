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
function terminal_parameters($required,$optional=array(),$start=0,$end=0,$help=array('h','help'))
{
    // Build expected parameters
    $expected = array();
    foreach (array_merge($required, $optional) as $key => $param) {
        if (is_array($param)) {
            foreach ($param as $param2) {
                $expected[strtolower($param2)] = $key;
            }
        } else {
            $expected[strtolower($param)] = $key;
        }
    }
     
    // Remove parameters to ignore
    if ($end == 0) {
        $given = array_slice($_SERVER['argv'], $start+1);
    } else {
        $given = array_slice($_SERVER['argv'], $start+1, -$end);
    }
     
    // Get parameters values
    $input = array(); $lastKey = null;
    for ($i=0;isset($given[$i]);$i++) {
        // Check if it is a key or a value
        if (preg_match('/-{1,2}(.*)/', $given[$i], $matches)) {
            // Get key
            $key = strtolower($matches[1]);
                
            // Help ?
            if ($help != null && (!is_array($help) && $key == $key ||
                is_array($help) && in_array($key, $help))) {
                $params = '';
                foreach ($required as $key => $param) {
                    if (is_array($param)) {
                        $param = implode('|', $param);
                    }
                    $params .= '-'.$param.' value ';
                }
                foreach ($optional as $key => $param) {
                    if (is_array($param)) {
                        $param = implode('|', $param);
                    }
                    $params .= '[-'.$param.' value] ';
                }
                die('Usage : '.$_SERVER['argv'][0].' '.$params."\n");
            }
            
            // Check if it is an expected parameter
            if (!isset($expected[$key])) {
                throw new Exception(
                    'Parameter\'s name "'.$key.'" is not expected'
                );
            }
            
            // Check if it is a parameter already defined
            if (isset($input[$key])) {
                throw new Exception(
                    'Parameter\'s name "'.$key.'" match to several values'
                );
            }
            
            // Check if last key is associated to a value
            if ($lastKey != null) {
                $input[$lastKey] = true;
            }
            
            // Save key
            $lastKey = $key;
        } else {
            // Check if last key is defined
            if ($lastKey == null) {
                throw new Exception(
                    'Parameter\'s value "'.$given[$i].'" '.
                    'is not associated to a name'
                );
            }
                
            // Save value
            $input[$key] = $given[$i];
                
            // Erase last key
            $lastKey = null;
        }
    }
    
    // Check if last key is associated to a value
    if ($lastKey != null) {
        $input[$lastKey] = true;
    }
    
    // Math expected parameters to given parameters
    $output = array_fill(0, count(array_merge($required, $optional)), null);
    foreach ($input as $key => $value) {
        $output[$expected[$key]] = $value;
    }
     
    // Check required parameters
    for ($i=0;$i<count($required);$i++) {
        if ($output[$i] === null) {
            if (is_array($required[$i])) {
                $name = array_shift($required[$i]);
            } else {
                $name = $required[$i];
            }
            throw new Exception('Parameter "'.$name.'" is required');  
        }
    }
     
    // Return parameters
    return $output;
}