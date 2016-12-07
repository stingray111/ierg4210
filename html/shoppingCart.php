<?php
session_start();
include_once('../lib/db.inc.php');
include_once('../lib/csrf.php');

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

function postCart(){

	//check session and set $userEmail
	$userEmail=getNormalEmail();
	if(!$userEmail){
		$userEmail=getAdminEmail();
	}
	if(!$userEmail){
		header('HTTP/1.1 403 Forbidden');
		if(empty($_SESSION['auth'])){
			error_log('no session');
		}
		if(empty($_COOKIE['auth'])){
			error_log('no cookie');
		}
		echo('You have to login first');
		exit();
	}

	try{
		$postdata = array();
		parse_str(file_get_contents("php://input"),$postdata); 
	}catch(Exception $e){
		throw new Exception('Cannot parse input data');
	}
	if($postdata == NULL){
		error_log('cannot parse');
		throw new Exception('Cannot parse input data');
	}

	try{ // handling the post data
		$cartSize = sizeof($postdata['cart']);
		$pidList = array();

		//validation
		if($cartSize == 0||$postdata['cart']===NULL) throw new Exception('Empty Cart');
		$postdata['business'] = 'pingshan1013-facilitator@hotmail.com';
		$postdata['currency_code'] = 'HKD';

		for($i = 0;$i<$cartSize;$i++){
			if($postdata['cart'][$i]['amount'] <= 0 ){
				throw new Exception('Wrong Amount');
			}	
			array_push($pidList,$postdata['cart'][$i]['pid']);
		}

		//get price from db and validate at the same time
		$pidStr = implode(",",array_map('intval',$pidList));

		$db = ierg4210_DB();
		$q = $db->prepare('select pid,catid,name,price from products where pid in ('.$pidStr.')');
		$q->execute();

		for($i = 0;$i<$cartSize;$i++){
			$ans = $q->fetch();
			if($ans === false) throw new Exception('Cannot find some items');
			$find = false;
			for($j = 0;$j<$cartSize;$j++){
				if($postdata['cart'][$j]['pid'] == $ans['pid']){
					$postdata['cart'][$j]['price']=(float)$ans['price'];
					$find = true;
					break;
				}
			}
			if($find === false) throw new Exception('Cannot find some items');

		}

		$salt = mt_rand();
		//calc the total price
		$totalprice = 0.0;
		for($i = 0;$i<$cartSize;$i++){
			$totalprice+=$postdata['cart'][$i]['price'] * $postdata['cart'][$i]['amount'];
		}

		//create the digest
		$digestb4Hash = $postdata['cart'];
		$digestb4Hash['total']=$totalprice;
		$digestb4Hash['business']=$postdata['business'];
		$digestb4Hash['currency_code']=$postdata['currency_code'];
		$digest = hash_hmac('sha1',json_encode($digestb4Hash),$salt);

		//put info to db
		$db=ierg4210_DB();
		$q = $db->prepare('insert into orders values(null,null,:digest,:salt,:cart,:username)');
		$q->execute(array(
			':digest'=>$digest,
			':salt'=>$salt,
			':cart'=>json_encode($postdata['cart']),
			':username'=>$userEmail
		));
		$lastInsertId = $db->lastInsertId();
		$ans = array(
			'oid'=>$lastInsertId,
			'digest'=>$digest
		);
		echo json_encode($ans);


	}catch(Exception $e){
		throw new Exception($e->getMessage());
	}



	//var_dump ($postdata);
	//echo (sizeof($postdata['cart']));
	//echo ($postdata['cart'][0]);
	//echo ($postdata['cart'][0]['pid']);
}


try{
	//validation
	if(empty($_REQUEST['action']) || !preg_match('/^\w+$/',$_REQUEST['action'])){
		throw new Exception('Undefined Action 1');
	}
	if(is_callable($_REQUEST['action'])){
		$returnVal = call_user_func($_REQUEST['action']);
		if ($returnVal === false) {
			throw new Exception('returned false');
		}
	}else{
		throw new Exception('Undefined Action 2');
	}

}catch(PDOException $e){
	//database error
	header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
	header('Content-type: application/json');
	echo json_encode(array('error'=>'db error'));
	error_log('db error');
}catch(Exception $e){
	//other errors
	header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
	header('Content-type: application/json');
	echo json_encode(array('error'=>$e->getMessage()));
	error_log($e->getMessage());
}

?>
