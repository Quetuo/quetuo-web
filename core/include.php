<?php
	define ('IN_MODULES', 1);
	
	$modules_initfuncs = array ();
	
	require_once ('core/security.php');
	require_once ('core/db.php');
	require_once ('core/settings.php');
	require_once ('core/user.php');
	require_once ('core/blog.php');
	require_once ('core/page.php');
	require_once ('core/gpg.php');
	require_once ('core/github.php');
	
	foreach ($modules_initfuncs as $initfuncs)
	{
		$initfuncs ();
	}
?>
