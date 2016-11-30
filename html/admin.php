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
include_once('../lib/csrf.php');
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>IERG4210 Shop - Admin Panel</title>
	<link href="/incl/css/admin.css" rel="stylesheet" type="text/css"/>
</head>

<body>
<h1>IERG4210 Shop - Admin Panel (Demo)</h1>
<article id="main">

<section id="categoryPanel">
	<fieldset> <legend>New Category</legend>
	<form id="cat_insert" method="POST" action="admin-process.php?action=<?php echo ($action='cat_insert');?>" onsubmit="return false;">
			<label for="cat_insert_name">Name</label>
			<div><input id="cat_insert_name" type="text" name="name" required="true" pattern="^[\w\- ]+$" /></div>

			<input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>"/>
			<input type="submit" value="Submit" />
		</form>
	</fieldset>
	
	<!-- Generate the existing categories here -->
	<ul id="categoryList"></ul>
</section>

<section id="categoryEditPanel" class="hide">
	<fieldset>
		<legend>Editing Category</legend>
		<form id="cat_edit" method="POST" action="admin-process.php?action=<?php echo($action='cat_edit');?>" onsubmit="return false;">
			<label for="cat_edit_name">Name</label>
			<div><input id="cat_edit_name" type="text" name="name" required="true" pattern="^[\w\- ]+$" /></div>
			<input type="hidden" id="cat_edit_catid" name="catid" />
			<input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>"/>
			<input type="submit" value="Submit" /> <input type="button" id="cat_edit_cancel" value="Cancel" />
		</form>
	</fieldset>
</section>

<section id="productPanel">
	<fieldset>
		<legend>New Product</legend>
		<form id="prod_insert" method="POST" action="admin-process.php?action=<?php echo($action='prod_insert');?>" enctype="multipart/form-data">
			<label for="prod_insert_catid">Category *</label>
			<div><select id="prod_insert_catid" name="catid"></select></div>

			<label for="prod_insert_name">Name *</label>
			<div><input id="prod_insert_name" type="text" name="name" required="true" pattern="^[\w\- ]+$" /></div>

			<label for="prod_insert_price">Price *</label>
			<div><input id="prod_insert_price" type="number" step="0.01" name="price" required="true" pattern="^[\d\.]+$" /></div>

			<label for="prod_insert_description">Description</label>
			<div><textarea id="prod_insert_description" name="description" pattern="^[\w\-, ]$"></textarea></div>

			<label for="prod_insert_name">Image *</label>
			<div><input type="file" name="file" required="true" accept="image/jpeg, image/gif, image/x-png" /></div>

			<input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>"/>
			<input type="submit" value="Submit" />
		</form>
	</fieldset>
	

	<!-- Generate the corresponding products here -->
	<ul id="productList"> </ul>

</section>
	<section id="productEditPanel" class="hide">
		<fieldset>
		<legend>Editing Product</legend>
		<form id="product_edit" method="POST" action="admin-process.php?action=<?php echo($action='product_edit');?>" enctype="multipart/form-data">

			<label for="prod_edit_catid">Category *</label>
			<div><select id="prod_edit_catid"  name="catid" ></select></div>

			<label for="prod_edit_name">Name *</label>
			<div><input id="prod_edit_name" type="text" name="name" required="true" pattern="^[\w\- ]+$" /></div>

			<label for="prod_edit_price">Price *</label>
			<div><input id="prod_edit_price" type="number" step="0.01" name="price" required="true" pattern="^[\d\.]+$" /></div>

			<label for="prod_edit_description">Description</label>
			<div><textarea id="prod_edit_description" name="description" pattern="^[\w\-, ]+$"></textarea></div>
			<img id="prod_edit_preview"/>
			<label for="prod_edit_image">Image</label>
			<div><input type="file" name="file"  accept="image/jpeg, image/gif, image/x-png" /></div>

			<input type="hidden" id="prod_edit_pid" name="pid"/>
			<input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>"/>
			<input type="submit" value="Submit" /> <input type="button" id="prod_edit_cancel" value="Cancel" />
		</form>
		</fieldset>
	</section>


<div class="clear"></div>
</article>
<script type="text/javascript" src="/incl/js/adminLib.js"></script>
<script type="text/javascript" src="/incl/js/admin.js"></script>
</body>
</html>
