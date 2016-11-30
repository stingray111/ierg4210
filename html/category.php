<?php 
session_start();
include_once('../lib/header.php'); 
?>
<link rel="stylesheet" href="/incl/css/category.css" type="text/css"/>
	<article id="content-wrap" >
	    <div id="NavRow">
		<a href="/">Home</a>
<?php
$catid = empty($_GET['catid'])? 0 : (int) $_GET['catid'];
if ($catid == 0){
	//do nothing
}
else{
	$sql = "SELECT * FROM categories where catid=".$catid;
	$cat= $db->query($sql);
	$row = $cat->fetch(PDO::FETCH_OBJ);
	if (empty($row)){
		$catid = -1;
	}else{
		echo '<text> > </text>';
		echo '<a href="category.php?catid='.$catid.'">'.$row->name.'</a>';
	}
}
?>
	
	    </div>

	    <ul id="producttable">
<?php
if ($catid == 0){
}
else{
	if ($catid == -1){
		echo 'Category does not exist';
	}
	else{
		$sql = "select * from products where catid=".$catid;
		$product = $db->query($sql);
		while($row=$product->fetch(PDO::FETCH_OBJ)){    
			echo '<li class="productcard">';
				echo '<a href="product.php?pid='.$row->pid.'">';
					echo '<div class="thumbnailbox">';
						echo '<img src="/incl/img/'.$row->pid.'.jpg" class="productthumbnail">';
					echo '</div>';
				echo '<a href="product.php?pid='.$row->pid.'.jpg" class="productname">';
					echo $row->name;
				echo '</a>';
				echo '<br><text>$'.$row->price.'</text><br>';
				echo '<button class="prodAddBtn" id="prodAddBtn_'.$row->pid.'" >';
					echo 'addToChart';
				echo '</button>';
			echo '</li>';
		}
	}
}
?>
	    </ul>
<?php readfile('../lib/footer.php');?>
