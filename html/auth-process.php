<?php
session_start();
include_once('../lib/db.inc.php');

function ierg4210_login(){
	if (empty($_POST['email']) || empty($_POST['pw']) 
		|| !preg_match("/^[\w=+\-\/][\w='+\-\/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$/", $_POST['email'])
		|| !preg_match("/^[\w@#$%\^\&\*\-]+$/", $_POST['pw']))
		throw new Exception('Wrong style');
	
	// Implement the login logic here

	$login_success=false;
	$userSql = "select * from users where username=?";
	$db = ierg4210_DB();
	$q = $db->prepare($userSql);
	if($q->execute(array($_POST['email']))){
		if($ans = $q->fetch(PDO::FETCH_OBJ)){
			if(hash_hmac('sha1',$_POST['pw'],$ans->salt) == $ans->saltedpassword){
				$login_success=true;
				if($ans->admin == 1){
					$exp = time()+3600*24*3;
					$token = array('email'=>$ans->username, 
						'exp'=>$exp, 
						'k'=>hash_hmac('sha1',$exp.$ans->saltedpassword,$ans->salt));
					setcookie('auth',json_encode($token),$exp,'','',false,true);
					$_SESSION['auth'] = $token;
					// redirect to admin page
					header('Location: admin.php', true, 302);
					exit();
				}else{
					$exp = time()+3600*24*3;
					$token = array('email'=>$ans->username, 
						'exp'=>$exp, 
						'k'=>hash_hmac('sha1',$exp.$ans->saltedpassword,$ans->salt));
					setcookie('normal',json_encode($token),$exp,'','',false,true);
					$_SESSION['normal'] = $token;
					header('Location: /',true,302);
					exit();
				}

					
			}else{
				error_log("wrong password");
			}
		}else{
			error_log("Ac Not Exist");
		}
	}else{
		error_log("DB problem");
	}
	throw new Exception('Wrong Credentials');
}

function ierg4210_logout(){
	// clear the cookies and session
	session_unset();
	session_destroy();
	// redirect to login page after logout
	header('Location: login.php', true, 302);
	exit();
}


header("Content-type: text/html; charset=utf-8");

try {
	// input validation
	if (empty($_REQUEST['action']) || !preg_match('/^\w+$/', $_REQUEST['action']))
		throw new Exception('Undefined Action');
	
	// check if the form request can present a valid nonce
	include_once('../lib/csrf.php');
	csrf_verifyNonce($_REQUEST['action'], $_POST['nonce']);
	
	// run the corresponding function according to action
	if (($returnVal = call_user_func('ierg4210_' . $_REQUEST['action'])) === false) {
		if ($db && $db->errorCode()) 
			error_log(print_r($db->errorInfo(), true));
		throw new Exception('Failed');
	} else {
		// no functions are supposed to return anything
		// echo $returnVal;
	}

} catch(PDOException $e) {
	error_log($e->getMessage());
	header('Refresh: 10; url=login.php?error=db');
	echo '<strong>Error Occurred:</strong> DB <br/>Redirecting to login page in 10 seconds...';
} catch(Exception $e) {
	header('Refresh: 10; url=login.php?error=' . $e->getMessage());
	echo '<strong>Error Occurred:</strong> ' . $e->getMessage() . '<br/>Redirecting to login page in 10 seconds...';
}
?>
