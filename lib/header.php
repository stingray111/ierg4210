<!DOCTYPE html>
<html>
<head>
    <title>ABC Shop</title>
    <link href="/incl/css/main.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <header>
<?php
include_once('db.inc.php');
function getAdminEmail(){
	if((!empty($_SESSION['auth'])) && (!empty($_COOKIE['auth']))){
		if($t = json_decode(stripslashes($_COOKIE['auth']),true)){
			if(time()>$t['exp']) return false;
			$db=ierg4210_DB();
			$q = $db->prepare('SELECT * FROM users WHERE username=?');
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
function getNormalEmail(){
	if((!empty($_SESSION['normal'])) && (!empty($_COOKIE['normal']))){
		if($t = json_decode(stripslashes($_COOKIE['normal']),true)){
			if(time()>$t['exp']) return false;
			$db=ierg4210_DB();
			$q = $db->prepare('SELECT * FROM users WHERE username=?');
			$q->execute(array($t['email']));
			if($r = $q->fetch()){
				$realk = hash_hmac('sha1',$t['exp'].$r['saltedpassword'],$r['salt']);
				if($realk == $t['k']){
					$_SESSION['normal'] = $t;
					return $t['email'];
				}
			}
		}
	}
	return false;
}

if($E = getAdminEmail()){
	include_once('csrf.php');
	echo 'Welcome Admin: '.$E.' ';
	echo '<form id="logout" method="POST" action="auth-process.php?action='.($action='logout').'">
			<input type="hidden" name="nonce" value="'.csrf_getNonce($action).'"/>
			<input type="submit" value="Logout" id="logoutBtn"/>
		</form>';
}else if($E = getNormalEmail()){
	include_once('csrf.php');
	echo 'Welcome Normal User: '.$E.' ';
	echo '<form id="logout" method="POST" action="auth-process.php?action='.($action='logout').'">
			<input type="hidden" name="nonce" value="'.csrf_getNonce($action).'"/>
			<input type="submit" value="Logout" id="logoutBtn"/>
		</form>';
}else{
	echo '<a href="/login.php"><button id="loginBtn" >Login</button></a>';
}
?>
	<div id="Banner">
	    ABC SHOP
	</div>
	<div id="shoppinglistrow">
	    <div id="shoppinglist">
		<div id="shoppinglisticon">
		    Shoppin List $
		 </div>
		 <div id="shoppinglistcontent">
		     <ul id="shoppinglistitems">
		     </ul>
		     <button id="checkoutbutton">Check Out</button>
		 </div>
	    </div>
	</div>
    </header>

    <main>
	<nav id="sidebar" >
	    <ul id="sidebarlist">
<?php 
$db = new PDO("sqlite:../cart.db");
$db->query("PRAGMA foreign_keys = ON;");
$result = $db->query("SELECT * FROM categories");
while($row=$result->fetch(PDO::FETCH_OBJ)){    
	echo '<li><a href="category.php?catid=';
	echo $row->catid;
	echo '">';
	echo $row->name; 
	echo '</a></li>';	
}
?>
	    </ul>
	</nav>
