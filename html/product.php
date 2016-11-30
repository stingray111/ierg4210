<?php 
session_start();
include_once('../lib/header.php');?>
<link href="/incl/css/product.css" rel="stylesheet" type="text/css"/>
    <article id="content-wrap" >
	<div id="NavRow">
	    <a href="/">Home</a>
	    <text> > </text>
<?php
$itemname='unknown';
$price=0;
$description='unknown';
$pid = empty($_GET['pid'])?0:(int)$_GET['pid'];
$sql = "SELECT * FROM products where pid=".$pid;
$product = $db->query($sql);
$row = $product->fetch(PDO::FETCH_OBJ);
if(empty($row)){
	$pid = 0;
}else{
	$productname=$row->name;
	$price=$row->price;
	$description=$row->description;
	$catid=$row->catid;	
	$catname='unknown';
	$sql = "select * from categories where catid=".$catid;
	$row= $db->query($sql)->fetch(PDO::FETCH_OBJ);
	if (empty($row)){
		$catid = -1;
	}else{
		$catname = $row->name;
	}
	echo '<a href="category.php?catid='.$catid.'">'.$catname.'</a>';
	echo '<text> > </text>';
	echo '<a href="product.php?pid='.$pid.'">'.$productname.'</a>';
}
?>
	</div>
	<div id="productarea">
<?php
if($pid!=0){
	echo '<div class="productinfo" id="productphoto">';
	echo '<img src="/incl/img/'.$pid.'.jpg" alt="">';
	echo '</div>';
	echo '<div class="productinfo" id="producttext">';
	echo '<Text>'.$productname.'</Text>';
	echo '<br>';
	echo '<Text>$'.$price.'</Text>';
	echo '<br>';
	echo '<button class="innerAddToCart" id="innerAddToCart_'.$pid.'">addToCart</button>';
	echo '<br>';
	echo '<p>';
	echo $description;
	echo '</p></div>';
}
?>
	</div>
    </article>
</main>
<?php readfile('../lib/footer.php');?>
