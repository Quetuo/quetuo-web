<?php
	/* Settings module
	 * Written by Quetuo
	*/
	
	if (!defined ('IN_MODULES')) {die ();}
	define ('MODULE_SETTINGS', 1);
	
	class class_settings
	{
		private $values = array ();
		public function __construct ()
		{
			global $db;
			$result = $db -> query ('SELECT `name`,`value` FROM `settings`');
			while ($row = $result -> fetch_row ())
			{
				$this -> values [$row [0]] = $row [1];
			}
		}
		public function __get ($name)
		{
			if (array_key_exists ($name, $this -> values))
			{
				return $this -> values [$name];
			}
			if ($name == 'site_root')
			{
				// We can attempt to figure this out
				$this -> values [$name] = dirname (dirname (__FILE__));
				return $this -> values [$name];
			}
			return NULL;
		}
		public function __set ($name, $value)
		{
			global $db;
			if (array_key_exists ($name, $this -> values))
			{
				$this -> values [$name] = $value;
				$stmt = $db -> stmt_init ();
				if (!$stmt) {trigger_error ('Failed to create prepared statement'); return;}
				$stmt -> prepare ('UPDATE `settings` SET `value`=? WHERE `name`=?');
				$stmt -> bind_param ('ss', $value, $name);
				$stmt -> execute ();
				$stmt -> close ();
			}
			else
			{
				$this -> values [$name] = $value;
				$stmt = $db -> stmt_init ();
				if (!$stmt) {trigger_error ('Failed to create prepared statement'); return;}
				$stmt -> prepare ('INSERT INTO `settings` VALUES (?, ?)');
				$stmt -> bind_param ('ss', $name, $value);
				$stmt -> execute ();
				$stmt -> close ();
			}
		}
		public function __unset ($name)
		{
			global $db;
			$stmt = $db -> stmt_init ();
			if (!$stmt) {trigger_error ('Failed to create prepared statement'); return;}
			$stmt -> prepare ('DELETE FROM `settings` WHERE `name`=?');
			$stmt -> bind_param ('s', $name);
			$stmt -> execute ();
			$stmt -> close ();
		}
		public function __isset ($name)
		{
			return array_key_exists ($name, $this -> values);
		}
	}
	
	$settings = new class_settings ();
?>
