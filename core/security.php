<?php
	/* Security module
	 * Written by Quetuo
	*/
	
	$config ['security'] ['scanfor'] = array ('\'',
		'SELECT+',
		'UNION+',
		'alert(',
		'<script>');
		
	
	function security_scanvar ($var)
	{
		global $config;
		foreach ($config ['security'] ['scanfor'] as $scan)
		{
			if (strpos ($var, $scan) !== false)
			{
				die ('No hacking!');
			}
		}
	}
	
	foreach ($_GET as $get)
	{
		security_scanvar ($get);
	}
	foreach ($_POST as $post)
	{
		security_scanvar ($post);
	}
?>
