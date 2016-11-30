<?php
include_once('../lib/csrf.php');
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>IERG4210 Login Demo</title>
</head>
<body>
<h1>IERG4210 Login Demo</h1>
<fieldset>
	<legend>Login Form</legend>
	<form id="loginForm" method="POST" action="auth-process.php?action=<?php echo ($action = 'login'); ?>">
		<label for="email">Email:</label>
		<input type="text" name="email" required="true" pattern="^[\w=+\-\/][\w='+\-\/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$" />
		<label for="pw">Password:</label>
		<input type="password" name="pw" required="true" pattern="^[\w@#$&%\^\*\-]+$" />
		<input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>"/>
		<input type="submit" value="Login" />
	</form>
</fieldset>
</body>
</html>
