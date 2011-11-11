<?php
	/* Page module
	 * Written by Quetuo
	*/
	
	if (!defined ('IN_MODULES')) {die ();}
	define ('MODULE_PAGE', 1);
	
	class class_page
	{
		public $title = 'Untitled';
		private $template = 'index';
		private $noout = false;
		public function get_content ()
		{
			return ob_get_contents ();
		}
		public function __construct ()
		{
			ob_start ();
		}
		public function __destruct ()
		{
			if ($this -> noout) {return;}
			global $settings, $user;
			$content = $this -> get_content ();
			ob_clean ();
			require ($settings -> site_root . '/style/' . $this -> template . '.html');
		}
		public function template ($file)
		{
			$this -> template = $file;
		}
		public function use_ssl ()
		{
			if ($_SERVER ['HTTPS'])
			{
				return true;
			}
			$url = 'https://' . $_SERVER ['SERVER_NAME'] . $_SERVER ['REQUEST_URI'];
			header ("Location: $url");
			$this -> noout = true;
			die ();
		}
	}
	
	$page = new class_page ();
?>
