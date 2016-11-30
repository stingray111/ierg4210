<?php
include_once('../lib/db.inc.php');

function ierg4210_price_by_pids(){
	global $db;
	$db = ierg4210_DB();
	try{
		//error_log(json_decode($_POST['prodList'])->name);
		$list = json_decode($_POST['prodList']);
	}
	catch(Exception $e){
	}
	
	$pidList = array();
	foreach ($list as $item){
		array_push($pidList,(int)($item->{'pid'}));
	}
	//error_log($pidList);
	$q = $db->prepare('select name,pid,price from products 
		where pid in ('.implode(',',array_map('intval',$pidList)).')');
	if($q->execute())
		return $q->fetchAll();
	return false;
}

function ierg4210_cat_fetchall() {
	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("SELECT * FROM categories LIMIT 100;");
	if ($q->execute())
		return $q->fetchAll();
}

function ierg4210_prod_by_cat(){
	$_POST['catid'] = (int)$_POST['catid'];
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("select products.name as pname,categories.name as catname,products.pid,products.catid
		from products inner join categories 
		on products.catid=categories.catid 
		where products.catid=".$_POST['catid']);
	//$q = $db->prepare("select name,pid from products where products.catid=".$_POST['catid']);
	if ($q->execute()){
		return $q->fetchAll();
	}
}

function ierg4210_product_fetchone() {
	// DB manipulation
	$_POST['pid'] = (int) $_POST['pid'];
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("SELECT * FROM products where pid=".$_POST['pid']);
	if ($q->execute()){
		return $q->fetch(PDO::FETCH_OBJ);
	}
}

header('Content-Type: application/json');
// input validation
if (empty($_REQUEST['action']) || !preg_match('/^\w+$/', $_REQUEST['action'])) {
	echo json_encode(array('failed'=>'undefined'));
	exit();
}
// The following calls the appropriate function based to the request parameter $_REQUEST['action'],
//   (e.g. When $_REQUEST['action'] is 'cat_insert', the function ierg4210_cat_insert() is called)
// the return values of the functions are then encoded in JSON format and used as output
try {
	if (($returnVal = call_user_func('ierg4210_' . $_REQUEST['action'])) === false) {
		if ($db && $db->errorCode()) 
			error_log(print_r($db->errorInfo(), true));
		echo json_encode(array('failed'=>'1'));
	}
	echo 'while(1);' . json_encode(array('success' => $returnVal));
} catch(PDOException $e) {
	error_log($e->getMessage());
	echo json_encode(array('failed'=>'error-db'));
} catch(Exception $e) {
	echo 'while(1);' . json_encode(array('failed' => $e->getMessage()));
}

?>
