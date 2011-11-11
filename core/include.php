<?php
	error_reporting (0);

	define ('IN_MODULES', 1);
	
	$modules_initfuncs = array ();
	
	require_once ('core/security.php');
	require_once ('core/db.php');
	require_once ('core/settings.php');
	require_once ('core/user.php');
	require_once ('core/blog.php');
	require_once ('core/page.php');
	
	foreach ($modules_initfuncs as $initfuncs)
	{
		$initfuncs ();
	}
?>
