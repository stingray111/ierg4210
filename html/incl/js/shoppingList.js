reloadCart();

function getCart(){
	storage = window.localStorage.getItem('cart');
	try{
		if(storage){
			storage = JSON.parse(storage);
		}else{
			throw "storage not exist exception";
		}
	}catch(e){
		storage = [];
		setCart(storage);
	}
	var modified = false;
	for(i = 0;i<storage.length;i++){
		if(storage[i].amount == 0 ){
			storage.splice(i,1);
			modified = true;
		}
	}
	if (modified) setCart(storage);
	return storage;
}

function setCart(obj,call){
	window.localStorage.setItem("cart",JSON.stringify(obj));
	if(call!=null) reloadCart(function(e){call();});
}

function reloadCart(e){
	var obj = getCart();
	if(obj.length>0){

		myLib.post({action:'price_by_pids',prodList:JSON.stringify(obj)},function(json){
			var ans = json;
			var result = [];
			var sum = 0;
			for(var i = 0;i<ans.length;i++){
				result.push('<li><label>'+ans[i].name+'</label>');
				result.push('<input id="shopListProd_'+ans[i].pid+'" class="shopListProd" type="number" class="inputamountbox"></li>');
				for(var j =0;j<obj.length;j++){
					if(obj[j].pid == ans[i].pid){
						sum+=obj[j].amount*ans[i].price;
					}
				}
			}
			el('shoppinglistitems').innerHTML = result.join('');
			el('shoppinglisticon').innerHTML = "Shopping List $"+sum.toFixed(2);
			for(var i =0;i<obj.length;i++){
				el('shopListProd_'+obj[i].pid).value = obj[i].amount;
			}
			updatelistclick();
		});


		/*
		var request = jQuery.ajax({type:"POST",
			url:"/normal-process.php?rnd=" + new Date().getTime(),
			contentType:"application/x-www-form-urlencoded",
			data:{action:'price_by_pids',prodList:JSON.stringify(obj)}});

		request.done(
			 function(json){
					var ans = json;
					var result = [];
					var sum = 0;
					for(var i = 0;i<ans.length;i++){
						result.push('<li><label>'+ans[i].name+'</label>');
						result.push('<input id="shopListProd_'+ans[i].pid+'" class="shopListProd" type="number" class="inputamountbox"></li>');
						for(var j =0;j<obj.length;j++){
							if(obj[j].pid == ans[i].pid){
								sum+=obj[j].amount*ans[i].price;
							}
						}
					}
					el('shoppinglistitems').innerHTML = result.join('');
					el('shoppinglisticon').innerHTML = "Shopping List $"+sum.toFixed(2);
					for(var i =0;i<obj.length;i++){
						el('shopListProd_'+obj[i].pid).value = obj[i].amount;
					}
					updatelistclick();

			}
		);
		request.fail(function(json){alert(json);});
		*/

	}else{
		el('shoppinglistitems').innerHTML = "Nothing in cart";
		el('shoppinglisticon').innerHTML = "Shopping List $0";
	}
	if(e!=null)e();
}


function addItemToCart(id){
	localCart = getCart();

	if(localCart.length == 0){
		localCart.push(JSON.parse('{"pid":'+id+',"amount":1}'));
	}else{
		find = false;
		for(i = 0;i<localCart.length;i++){
			if(localCart[i].pid == id){
				//localCart[i].amount+=1;
				find = true;
				break;
			}
		}
		if(!find) localCart.push(JSON.parse('{"pid":'+id+',"amount":1}'));
	}
	setCart(localCart,function(e){reloadCart();});
}


var prodAddBtn = document.getElementsByClassName("prodAddBtn");
for(var i=0;i<prodAddBtn.length;i++){
	document.getElementById(prodAddBtn[i].id).onclick = function(e){
		addItemToCart(e.target.id.split("_")[1]);
	}
}

var innerAddToCart = document.getElementsByClassName("innerAddToCart");
for(var i =0;i<innerAddToCart.length;i++){
	document.getElementById(innerAddToCart[i].id).onclick = function(e){
		addItemToCart(e.target.id.split("_")[1]);
	}
}

function updatelistclick(){
	var shoplistitem= document.getElementsByClassName("shopListProd");
	for(var i =0;i<shoplistitem.length;i++){
		document.getElementById(shoplistitem[i].id).onkeyup = function(e){
			if(e.target.value != "" ){
				var cart = getCart();
				var targetid = e.target.id.split("_")[1];
				for(var i=0;i<cart.length;i++){
					if(cart[i].pid == targetid){
						cart[i].amount = isNaN(parseInt(e.target.value)) ? 1:parseInt(e.target.value);
					}
				}
				var focus = document.activeElement.id;
				setCart(cart,function(e){
					reloadCart(function(e){
						setTimeout(function(e){
							document.getElementById(focus).focus();
						},100);
					});
				});
			}
		};
	}
}



document.getElementById('shoppinglistcontent').onmouseleave = function(e){
	reloadCart();
}

function login(){
	alert('hello');
}

function logout(){
	alert('hello');
}




