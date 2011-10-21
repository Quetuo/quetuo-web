<?php
	if (!defined ('IN_MODULES')) {die ();}
	
	$config ['db'] ['host'] = 'localhost';
	$config ['db'] ['user'] = 'root';
	$config ['db'] ['pass'] = 'jammi6369';
	$config ['db'] ['db'] = 'quetuo.net';
	
	$db = new mysqli ($config ['db'] ['host'], $config ['db'] ['user'], $config ['db'] ['pass'], $config ['db'] ['db']);
?>
