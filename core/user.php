<?php
	/* User module
	 * Written by Quetuo
	*/
	
	if (!defined ('IN_MODULES')) {die ();}
	define ('MODULE_USER', 1);
	
	if (defined ('MODULE_SETTINGS'))
	{
		$config ['users'] ['salt_length'] = isset ($settings -> salt_length) ? 10 : $settings -> salt_length;
		$config ['users'] ['hash_method'] = isset ($settings -> hash_method) ? 'sha256' : $settings -> hash_method;
		$config ['users'] ['timeout'] = isset ($settings -> timeout) ? 3600 : $settings -> timeout;
	}
	else 
	{
		$config ['users'] ['salt_length'] = 10;
		$config ['users'] ['hash_method'] = 'sha256';
		$config ['users'] ['timeout'] = 3600;
	}
	
	$db -> query ('DELETE FROM `session` WHERE lastactive<' . (time () - $config ['users'] ['timeout']));
	$db -> query ('UPDATE `session` SET lastactive=' . time () . ' WHERE id=\'' . session_id () . '\'');
	
	function user_generate_salt ()
	{
		global $config;
		$ret = '';
		for ($i = 0; $i < $config ['users'] ['salt_length']; $i ++)
		{
			$ret .= chr (mt_rand (32, 126));
		}
		return $ret;
	}
	
	function user_hash ($password, $password_salt)
	{
		global $config;
		return hash ($config ['users'] ['hash_method'], $password_salt . hash ($config ['users'] ['hash_method'], $password));
	}
	
	$user_username_cache = array ();
	
	function user_get_username ($id)
	{
		if ($id == 0)
		{
			return "Guest";
		}
		if (array_key_exists ($id, $user_username_cache))
		{
			return $user_username_cache [$id];
		}
		global $db;
		$stmt = $db -> stmt_init ();
		if (!$stmt) {trigger_error ('Failed to create prepared statement'); return;}
		$stmt -> prepare ('SELECT `username` FROM `user` WHERE id=?');
		$stmt -> bind_param ('i', $id);
		$stmt -> execute ();
		$stmt -> bind_result ($username);
		$stmt -> fetch ();
		$stmt -> close ();
		$user_username_cache [$id] = $username;
		return $username;
	}
	
	class class_user
	{
		private $id = 0;
		public $username;
		public $email;
		public $meta = array ();
		private $password_hash;
		private $password_salt;
		public function __construct ($id = 0)
		{
			global $db;
			if ($id == 0)
			{
				$this -> session_update ();
				return;
			}
			$this -> load ($id);
			return;
		}
		private function load ($id = 0)
		{
			global $db;
			$stmt = $db -> stmt_init ();
			if (!$stmt) {trigger_error ('Failed to create prepared statement'); return;}
			$stmt -> prepare ('SELECT `id`,`username`,`email`,`password_hash`,`password_salt` FROM `user` WHERE id=?');
			$stmt -> bind_param ('i', $id);
			$stmt -> execute ();
			$stmt -> bind_result ($this -> id, $this -> username, $this -> email, $this -> password_hash, $this -> password_salt);
			$stmt -> fetch ();
			$stmt -> close ();
			$result = $db -> query ('SELECT `meta`.`name`, `user_meta`.`value` FROM `meta` LEFT JOIN `user_meta` ON `meta`.`id`=`user_meta`.`meta_id` WHERE `user_meta`.`user_id`=' . $this -> id);
			while ($row = $result -> fetch_row ())
			{
				$this -> meta [$row [0]] = $row [1];
			}
		}
		public function save ()
		{
			global $db;
			if (!$this -> id) {return false;}
			$stmt = $db -> stmt_init ();
			if (!$stmt) {trigger_error ('Failed to create prepared statement'); return;}
			$stmt -> prepare ('UPDATE `user` SET email=?, password_hash=?, password_salt=? WHERE id=?');
			$stmt -> bind_param ('sssi', $this -> email, $this -> password_hash, $this -> password_salt, $this -> id);
			$stmt -> execute ();
			$stmt -> close ();
		}
		public function change_password ($password)
		{
			global $db;
			if (!$this -> id) {return false;}
			$stmt = $db -> stmt_init ();
			if (!$stmt) {trigger_error ('Failed to create prepared statement'); return;}
			$this -> password_salt = user_generate_salt ();
			$this -> password_hash = user_hash ($password, $this -> password_salt);
			$stmt -> prepare ('UPDATE `user` SET password_hash=?, password_salt=? WHERE id=?');
			$stmt -> bind_param ('ssi', $this -> password_hash, $this -> password_salt, $this -> id);
			$stmt -> execute ();
			$stmt -> close ();
		}
		public function get_id ()
		{
			return $this -> id;
		}
		public function session_update ()
		{
			global $db;
			$s = session_id ();
			$result = $db -> query ('SELECT `user_id` FROM `session` WHERE id=\'' . $s . '\'');
			if ($result -> num_rows == 1)
			{
				$row = $result -> fetch_row ();
				$this -> load ($row [0]);
				return;
			}
			else if ($result -> num_rows > 1)
			{
				trigger_error ('Invalid data in session table');
				return;
			}
			else
			{
				$db -> query ('INSERT INTO `session`  VALUES (\'' . session_id () . '\', ' . $this -> id . ', ' . time () . ')');
				return;
			}
			
		}
		public function login ($username, $password)
		{
			global $db;
			$s = session_id ();
			$stmt = $db -> stmt_init ();
			if (!$stmt) {trigger_error ('Failed to create prepared statement'); return;}
			$stmt -> prepare ('SELECT `id`, `password_hash`, `password_salt` FROM `user` WHERE `username`=?');
			$stmt -> bind_param ('s', $username);
			$stmt -> execute ();
			$stmt -> store_result ();
			if ($stmt -> num_rows == 1)
			{
				$stmt -> bind_result ($id, $password_hash, $password_salt);
				$stmt -> fetch ();
				if (user_hash ($password, $password_salt) == $password_hash)
				{
					$this -> load ($id);
					$stmt = $db -> stmt_init ();
					if (!$stmt) {trigger_error ('Failed to create prepared statement'); return;}
					$stmt -> prepare ('UPDATE `session` SET `user_id`=? WHERE `id`=?');
					$stmt -> bind_param ('is', $this -> id, $s);
					$stmt -> execute ();
					$stmt -> close ();
					return true;
				}
			}
			$stmt -> close ();
			return false;
		}
		public function logout ($all = false)
		{
			global $db;
			if ($all)
			{
				$db -> query ('UPDATE `session` SET `user_id`=0 WHERE `user_id`=' . $this -> id);
			}
			else
			{
				$s = session_id ();
				$stmt = $db -> stmt_init ();
				if (!$stmt) {trigger_error ('Failed to create prepared statement'); return;}
				$stmt -> prepare ('UPDATE `session` SET `user_id`=0 WHERE `id`=?');
				$stmt -> bind_param ('s', $s);
				$stmt -> execute ();
				$stmt -> close ();
			}
		}
		public function get_gravatar ()
		{
			return 'https://secure.gravatar.com/avatar/' . md5 ($this -> email);
		}
		public function __get ($name)
		{
			if (array_key_exists ($name, $this -> meta))
			{
				return $this -> meta [$name];
			}
			return NULL;
		}
		public function __isset ($name)
		{
			return array_key_exists ($name, $this -> meta);
		}
	}
	
	$modules_initfuncs [] = function ()
	{
		
	};
	
	// Okay, initialise
	session_start ();
	$user = new class_user ();
	
?>
