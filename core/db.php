<?php
	if (!defined ('IN_MODULES')) {die ();}
	
	$config ['db'] ['host'] = 'localhost';
	$config ['db'] ['user'] = '';
	$config ['db'] ['pass'] = '';
	$config ['db'] ['db'] = '';
	
	$db = new mysqli ($config ['db'] ['host'], $config ['db'] ['user'], $config ['db'] ['pass'], $config ['db'] ['db']);
?>
