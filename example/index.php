<?php

// Require
require('../src/terminal_parameters.php');

try {
	// Get parameters
	$parameters = terminal_parameters(array(array('s','source'),array('d','dest')),array('h','w'));
	
	// Display parameters
	echo 'Source : "',$parameters[0],'"',"\n",
	     'Destination : "',$parameters[1],'"',"\n",
	     'Height : "',$parameters[2],'"',"\n",
	     'Width : "',$parameters[3],'"',"\n";
} catch(Exception $exception) {
	// Display error
	echo $exception->getMessage(),"\n";
}