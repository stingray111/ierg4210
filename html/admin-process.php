<?php

include_once('../lib/csrf.php');
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
	error_log($pidList);
	$q = $db->prepare('select name,pid,price from products 
		where pid in (:theList)');

	if($q->execute(array(':theList' => implode(',',array_map('intval',$pidList)))))
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

function ierg4210_cat_insert() {
	// input validation or sanitization
	csrf_verifyNonce($_REQUEST['action'],$_POST['nonce']);
	if (!preg_match('/^[\w\-, ]+$/', $_POST['name']))
		throw new Exception("invalid-name");
	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("INSERT INTO categories (name) VALUES (?)");
	return $q->execute(array($_POST['name']));
}

function ierg4210_cat_edit() {
	csrf_verifyNonce($_REQUEST['action'],$_POST['nonce']);
	$_POST['catid'] = (int)$_POST['catid'];
	if (!preg_match('/^[\w\-, ]+$/', $_POST['name']))
		throw new Exception("invalid-name");
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare('update categories set name=:name where catid=:catid');
	return $q->execute(array(':name'=> $_POST['name'],':catid' => $_POST['catid']));
}

function ierg4210_cat_delete() {
	// input validation or sanitization
	$_POST['catid'] = (int) $_POST['catid'];
	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("DELETE FROM categories WHERE catid = ?");
	return $q->execute(array($_POST['catid']));
}

// Since this form will take file upload, we use the tranditional (simpler) rather than AJAX form submission.
// Therefore, after handling the request (DB insert and file copy), this function then redirects back to admin.html
function ierg4210_prod_insert() {
	csrf_verifyNonce($_REQUEST['action'],$_POST['nonce']);
	// input validation or sanitization
	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$_POST['catid'] = (int)$_POST['catid'];
	if (!preg_match('/^[\w\-, ]+$/', $_POST['name']))
		throw new Exception("invalid-name");
	$_POST['price'] = (float)$_POST['price'];
	if (!preg_match('/^[\w\-, ]+$/', $_POST['description']))
		throw new Exception("invalid-description");

	$q = $db->prepare("insert into products values (null,:catid,:name,:price,:desc)");
	$q->execute(array(':catid'=>$_POST['catid'],':name'=>$_POST['name'],':price'=>$_POST['price'],':desc'=>$_POST['description']));
	
	// The lastInsertId() function returns the pid (primary key) resulted by the last INSERT command
	$lastId = $db->lastInsertId();

	// Copy the uploaded file to a folder which can be publicly accessible at incl/img/[pid].jpg
	if ($_FILES["file"]["error"] == 0
		&& $_FILES["file"]["type"] == "image/jpeg"
		&& $_FILES["file"]["size"] < 10000000) {

		// Note: Take care of the permission of destination folder (hints: current user is apache)
		if (move_uploaded_file($_FILES["file"]["tmp_name"], "incl/img/" . $lastId . ".jpeg")) {
			// redirect back to original page; you may comment it during debug
			header('Location: admin.php');
			exit();
		}
		else{
			header('Content-Type: text/html; charset=utf8');
			echo 'Image file permission problem';
		}
	}
	else{
		// To replace the content-type header which was json and output an error message
		header('Content-Type: text/html; charset=utf-8');
		echo 'Invalid file detected. <br/><a href="javascript:history.back();">Back to admin panel.</a>';
	}
	// Only an invalid file will result in the execution below
	$q = $db->prepare("delete from products where pid=?");
	$q->execute(array($lastId));
	exit();
}

function ierg4210_product_edit() {
	csrf_verifyNonce($_REQUEST['action'],$_POST['nonce']);
	//validation
	$_POST['pid'] = (int)$_POST['pid'];
	$_POST['catid'] = (int)$_POST['catid'];
	$_POST['price'] = (float)$_POST['price'];
	if (!preg_match('/^[\w\-, ]+$/', $_POST['name']))
		throw new Exception("invalid-name");
	if (!preg_match('/^[\w\-, ]+$/', $_POST['description']))
		throw new Exception("invalid-description");
	global $db;
	$db = ierg4210_DB();
	$sql="update products set catid=:cid, price=:p, name=:n,description=:d where pid=:pid";
	$q = $db->prepare($sql);

	$q->execute(array(':cid'=>$_POST['catid'],':p'=>$_POST['price'],':n'=>$_POST['name'],':d'=>$_POST['description'],':pid'=>$_POST['pid']));
	//check file
	if ($_FILES["file"]["error"] == 0
		&& $_FILES["file"]["type"] == "image/jpeg"
		&& $_FILES["file"]["size"] < 10000000) {
		if (move_uploaded_file($_FILES["file"]["tmp_name"], "incl/img/" . $_POST['pid']. ".jpg")) {
		}
	}
	header('Location: admin.php');
	exit();
}

function ierg4210_prod_by_cat(){
	$_POST['catid'] = (int)$_POST['catid'];
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("select products.name as pname,categories.name as catname,products.pid,products.catid
		from products inner join categories 
		on products.catid=categories.catid 
		where products.catid=?");
	if ($q->execute(array($_POST['catid']))){
		return $q->fetchAll();
	}
}

function ierg4210_product_fetchone() {
	// DB manipulation
	$_POST['pid'] = (int) $_POST['pid'];
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("SELECT * FROM products where pid=?");
	if ($q->execute(array($_POST['pid']))){
		return $q->fetch(PDO::FETCH_OBJ);
	}
}

function ierg4210_product_delete() {
	// input validation or sanitization
	$_POST['pid'] = (int) $_POST['pid'];
	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("DELETE FROM products WHERE pid = ?");
	return $q->execute(array($_POST['pid']));
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
