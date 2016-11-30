(function(){

	function updateUI() {
		myLib.get({action:'cat_fetchall'}, function(json){
			// loop over the server response json
			//   the expected format (as shown in Firebug): 
			

			for (var options = [], listItems = [],
					i = 0, cat; cat = json[i]; i++) {
				options.push('<option value="' , parseInt(cat.catid) , '">' , cat.name.escapeHTML() , '</option>');
				listItems.push('<li id="cat' , parseInt(cat.catid) , '"><span class="name">' , cat.name.escapeHTML() , '</span> <span class="delete">[Delete]</span> <span class="edit">[Edit]</span></li>');
			}
			el('prod_insert_catid').innerHTML = '<option></option>' + options.join('');
			el('categoryList').innerHTML = listItems.join('');
			el('productList').innerHTML = "";
		});
	}
	updateUI();

	
	el('categoryList').onclick = function(e) {
		if (e.target.tagName != 'SPAN')
			return false;
		
		var target = e.target,
			parent = target.parentNode,
			id = target.parentNode.id.replace(/^cat/, ''),
			name = target.parentNode.querySelector('.name').innerHTML;
		
		// handle the delete click
		if ('delete' === target.className) {
			confirm('Sure?') && myLib.post({action: 'cat_delete', catid: id}, function(json){
				alert('"' + name + '" is deleted successfully!');
				updateUI(); el('productEditPanel').show();
				el('productPanel').hide();
				updateUI();
			});
		
		// handle the edit click
		} else if ('edit' === target.className) {
			// toggle the edit/view display
			el('categoryEditPanel').show();
			el('categoryPanel').hide();
			
			// fill in the editing form with existing values
			el('cat_edit_name').value = name;
			el('cat_edit_catid').value = id;
			updateUI();
		
		//handle the click on the category name
		} else {
			el('prod_insert_catid').value = id;
			// populate the product list or navigate to admin.php?catid=<id>
			// old method
			//window.location.href = 'admin.php?catid='+id;
			//new method ajax
			myLib.post({action:'prod_by_cat',catid:id},function(json){
				for(i = 0,listItems = [];i<json.length;i++){
					listItems.push('<li id="product'+json[i].pid+'"> '+json[i].pname +' of "' + json[i].catname + '" <span class="edit">[Edit]</span> <span class="delete">[Delete]</span></li>');
				}
				el('productList').innerHTML = listItems.join('');
			});
		}
	}

	el('productList').onclick = function(e){
		if(e.target.tagName !='SPAN')
			return false;
		var target = e.target,
			parent = target.parentNode,
			id = target.parentNode.id.replace(/^product/,'');
			
		
		if('delete' === target.className){
			confirm('Sure?') && myLib.post({action: 'product_delete', pid:id}, function(json){
				alert("delete successful");
				updateUI();
			});
		}else if ('edit' === target.className){
			//toggle the edit/view display
			el('productEditPanel').show();
			el('productPanel').hide();

			myLib.get({action:'cat_fetchall'},function(json){
				for (var options = [], listItems = [], i = 0, cat; cat = json[i]; i++) {
				options.push('<option value="' , parseInt(cat.catid) , '">' , cat.name.escapeHTML() , '</option>');
			}
				el('prod_edit_catid').innerHTML = '<option></option>' + options.join('');
				myLib.post({action:'product_fetchone', pid:id},function(json){
					theproduct = json;
					el('prod_edit_pid').value=theproduct.pid;
					el('prod_edit_catid').value=theproduct.catid;
					el('prod_edit_name').value=theproduct.name;
					el('prod_edit_price').value=theproduct.price;
					el('prod_edit_description').value=theproduct.description;
					document.getElementById("prod_edit_preview").src="/incl/img/"+theproduct.pid+".jpg";
				});

			});
		}
	}
	el('prod_edit_cancel').onclick=function(){
		el('productEditPanel').hide();
		el('productPanel').show();
	}
	
	el('cat_insert').onsubmit = function() {
		return myLib.submit(this, updateUI);
	}
	el('cat_edit').onsubmit = function() {
		return myLib.submit(this, function() {
			// toggle the edit/view display
			el('categoryEditPanel').hide();
			el('categoryPanel').show();
			updateUI();
		});
	}

	/*
	el('product_edit').onsubmit = function(){
		return myLib.submit(this,function(json){
			alert(JSON.stringify(json));
			el('productEditPanel').hide();
			el('productPanel').show();
			el('productList').innerHTML = '';
			updateUI();
		});
	}
	 */
	
	el('cat_edit_cancel').onclick = function() {
		// toggle the edit/view display
		el('categoryEditPanel').hide();
		el('categoryPanel').show();
	}

})();

