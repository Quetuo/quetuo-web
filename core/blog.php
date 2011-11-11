<?php
	/* Blog module
	 * Written by Quetuo
	*/
	
	if (!defined ('IN_MODULES')) {die ();}
	define ('MODULE_BLOG', 1);
	
	class class_blog
	{
		public $posts = array ();
		public function __construct ()
		{
			return;
		}
		public function load ($lower = 0, $upper = 10)
		{
			global $db;
			$stmt = $db -> stmt_init ();
			if (!$stmt) {trigger_error ('Failed to create prepared statement'); return;}
			$stmt -> prepare ('SELECT `id` FROM `blog_post` ORDER BY `time` DESC LIMIT ?,?');
			$stmt -> bind_param ('ii', $lower, $upper);
			$stmt -> execute ();
			$post_id = 0;
			$stmt -> bind_result ($post_id);
			while ($stmt -> fetch ())
			{
				$posts [$post_id] = NULL;
			}
			$stmt -> close ();
			foreach ($posts as $id => $post)
			{
				$this -> posts [$id] = new class_blog_post ($id);
			}
		}
	}
	
	class class_blog_post
	{
		private $id = 0;
		private $permalink = '';
		public $user_id = 0;
		public $title = '';
		public $content = '';
		public $comments = array ();
		
		public function __construct ($id = 0)
		{
			global $db;
			if ($id > 0)
			{
				$this -> load ($id);
			}
		}
		public function load ($id)
		{
			global $db;
			$stmt = $db -> stmt_init ();
			if (!$stmt) {trigger_error ('Failed to create prepared statement'); return;}
			$stmt -> prepare ('SELECT `id`, `user_id`, `time`, `permalink`, `title`, `content` FROM `blog_post` WHERE `id`=?');
			$stmt -> bind_param ('i', $id);
			$stmt -> execute ();
			$stmt -> bind_result ($this -> id, $this -> user_id, $this -> time, $this -> permalink, $this -> title, $this -> content);
			$stmt -> fetch ();
			$stmt -> close ();
			
			$stmt = $db -> stmt_init ();
			if (!$stmt) {trigger_error ('Failed to create prepared statement'); return;}
			$stmt -> prepare ('SELECT blog_comment.`id`, blog_comment.`user_id`, user.`username`, blog_comment.`time`, blog_comment.`content` FROM `blog_comment` INNER JOIN user ON `blog_comment`.`user_id`= `user`.`id` WHERE `blog_comment`.`blog_post_id`=?');
			$stmt -> bind_param ('i', $this -> id);
			$stmt -> execute ();
			$stmt -> bind_result ($id, $user_id, $username, $time, $content);
			while ($stmt -> fetch ())
			{
				$this -> comments [] = new class_blog_comment ($id, $this -> id, $user_id, $username, $time, $content);
			}
			$stmt -> close ();
		}
		public function get_id ()
		{
			return $this -> id;
		}
	}
	
	class class_blog_comment
	{
		private $id;
		private $post_id;
		public $user_id;
		public $username;
		public $time;
		public $content;
		
		public function __construct ($id, $post_id, $user_id, $username, $time, $content)
		{
			$this -> id = $id;
			$this -> post_id = $post_id;
			$this -> user_id = $user_id;
			$this -> username = $username;
			$this -> time = $time;
			$this -> content = $content;
		}
	}

	$blog = new class_blog ();
?>
