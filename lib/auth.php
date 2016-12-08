<?php
function isAdmin(){
	if((!empty($_SESSION['auth'])) && (!empty($_COOKIE['auth']))){
		if($t = json_decode(stripslashes($_COOKIE['auth']),true)){
			if(time()>$t['exp']) return false;
			$db=ierg4210_DB();
			$q = $db->prepare('SELECT * FROM users WHERE username=? AND admin=1');
			$q->execute(array($t['email']));
			if($r = $q->fetch()){
				$realk = hash_hmac('sha1',$t['exp'].$r['saltedpassword'],$r['salt']);
				if($realk == $t['k']){
					$_SESSION['auth'] = $t;
					return $t['email'];
				}
			}
		}
	}
	return false;
}


function getEmail(){
	if((!empty($_SESSION['auth'])) && (!empty($_COOKIE['auth']))){
		if($t = json_decode(stripslashes($_COOKIE['auth']),true)){
			if(time()>$t['exp']) return false;
			$db=ierg4210_DB();
			$q = $db->prepare('SELECT * FROM users WHERE username=? AND admin=1');
			$q->execute(array($t['email']));
			if($r = $q->fetch()){
				$realk = hash_hmac('sha1',$t['exp'].$r['saltedpassword'],$r['salt']);
				if($realk == $t['k']){
					$_SESSION['auth'] = $t;
					return $t['email'];
				}
			}
		}
	}
	return false;
}

$userType = 0;//0: no login, 1:user, 2:admin
$userEmail = getEmail();

if(isAdmin()){
	$userType = 2;
}else if($userEmail != false){
	$userType = 1;
}

?>
