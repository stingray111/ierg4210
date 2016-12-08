<?php
if($userType != 2){
	header('Location: /',true,302);
	if(empty($_SESSION['auth'])){
		error_log('no session');
	}
	if(empty($_COOKIE['auth'])){
		error_log('no cookie');
	}
	exit();
}
?>
