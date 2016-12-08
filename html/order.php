<?php
session_start();
include_once('../lib/db.inc.php');

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


if(!isAdmin()){
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
<!DOCTYPE html>
<html>
<head>
	<title>ABC Shop</title>
</head>
<body>
	<a href='/admin.php'>Back to Admin Panel</a>
	<table>
<?php
$db = ierg4210_DB();
$q = $db->prepare('select * from orders');
$q->execute();
$ans = $q->fetchAll();
echo '<tr>
	<th>oid</th>
	<th>tid</th>
	<th>digest</th>
	<th>salt</th>
	<th>cart</th>
	<th>status</th>
	<th>events</th>
	</tr>';


for($i=0; $i<sizeof($ans); $i++){
	echo '<tr>';
	echo '<td>'.$ans[$i]['oid'].'</td>';
	echo '<td>'.$ans[$i]['tid'].'</td>';
	echo '<td>'.$ans[$i]['digest'].'</td>';
	echo '<td>'.$ans[$i]['salt'].'</td>';
	echo '<td>'.$ans[$i]['cart'].'</td>';
	echo '<td>'.$ans[$i]['status'].'</td>';
	echo '<td>'.$ans[$i]['events'].'</td>';
	echo '</tr>';
}

?>
	</table>
</body>
</html>




