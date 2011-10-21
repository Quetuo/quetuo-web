<?php
	require_once ('core/include.php');
	
	//$page -> template ('login');
	
	//$page -> use_ssl ();
	$page -> title = "Login";
	
	if (isset ($_POST ['username']) && isset ($_POST ['password']))
	{
		if ($user -> login ($_POST ['username'], $_POST ['password']))
		{
			echo "<strong>Logged in!</strong>";
		}
		else
		{
			echo "<strong>Login failed!</strong>";
		}
	}
	
	if (isset ($_GET ['logout']))
	{
		$user -> logout ();
		?>
			<strong>Logged out</strong>		
		<?php
	}
	
	if ($user -> get_id ())
	{
	}
	else
	{
		?>
			<form method="POST">
				<table>
					<tr>
						<td><label for="username">Username</label></td>
						<td><input name="username" id="username" /></td>
					</tr>
					<tr>
						<td><label for="password">Password</label></td>
						<td><input type="password" name="password" id="password" /></td>
					</tr>
					<tr>
						<td colspan="2"><input type="submit" value="Login" /></td>
					</tr>
				</table>
			</form>
		<?php
	}
?>
