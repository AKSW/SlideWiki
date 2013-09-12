<script type="text/javascript">
function compare_decks(){
	$('#legend').show();
	var tmp;
	$.each($('#new_tree .tnode'),function(k,v){
		if(!$(v).hasClass('root-node')){
			//console.log(getPropertiesFromId(v.id)['itemId']);
			if(getPropertiesFromId(v.id)['type']=='deck'){
				if(!$("#old_tree .deck-"+getPropertiesFromId(v.id)['itemId']).length){
					$(v).addClass('new-item');
				}else{
					tmp=$("#old_tree .deck-"+getPropertiesFromId(v.id)['itemId'])[0].id;
					if(getPropertiesFromId(tmp)['pos']!=getPropertiesFromId(v.id)['pos']){
						$(v).addClass('modified-item');
						$("#old_tree #"+tmp).addClass('modified-item');
					}
				}
			}else{
				if(!$("#old_tree .slide-"+getPropertiesFromId(v.id)['itemId']).length){
					$(v).addClass('new-item');
				}else{
					tmp=$("#old_tree .slide-"+getPropertiesFromId(v.id)['itemId'])[0].id;
					if(getPropertiesFromId(tmp)['pos']!=getPropertiesFromId(v.id)['pos']){
						$(v).addClass('modified-item');
						$("#old_tree #"+tmp).addClass('modified-item');
					}
				}
			}
		}
	});
	$.each($('#old_tree .tnode'),function(k,v){
		if(!$(v).hasClass('root-node')){
			//console.log(getPropertiesFromId(v.id)['itemId']);
			if(getPropertiesFromId(v.id)['type']=='deck'){
				if(!$("#new_tree .deck-"+getPropertiesFromId(v.id)['itemId']).length){
					$(v).addClass('removed-item');
				}
			}else{
				if(!$("#new_tree .slide-"+getPropertiesFromId(v.id)['itemId']).length){
					$(v).addClass('removed-item');
				}
			}
		}
	});	
}

function compare_selected_slides(){
	var s1=$("#new_tree").jstree('get_checked')[0].id;	
	var s2=$("#old_tree").jstree('get_checked')[0].id;	

	window.open("?url=compare/reportSlideChanges&slide="+getPropertiesFromId(s1)['itemId']+"&compareTo="+getPropertiesFromId(s2)['itemId'])
}
$(function() {

	$.jstree._themes = "libraries/frontend/jstree/themes/";

	$("#new_tree").bind("loaded.jstree", function(event, data) {
		$("#new_tree").jstree("deselect_all");
		$("#new_tree").jstree("open_all");
		//$("#new_tree").jstree("check_all");
		$('.tdeck').find('ins.jstree-checkbox').hide();
		$('.jstree-clicked').removeClass('jstree-clicked');
	}).bind("change_state.jstree", function(e, data){
		//console.log(data);

        if(data.inst.get_checked().length>1)
           data.inst.uncheck_node(data.rslt[0]);
	}).bind("hover_node.jstree",function(event, data) {

			}).bind("dehover_node.jstree", function(event, data) {

	}).bind("select_node.jstree", function(event, data) {
		window.open(data.rslt.obj[0].childNodes[1].href);
	}).bind("refresh.jstree", function(event, data) {

	}).jstree(
			{
				"core" : {
					"html_titles" : true
				},
				"json_data" :{ "data" :
				<?php
				echo $new_content;
				?>}
				,
				"themes" : {
					"theme" : "default",
					"dots" : true,
					"icons" : true
				},
				checkbox : { "two_state" : true } ,
				"plugins" : [ "themes", "json_data", "ui","checkbox" ]
			});

	$("#old_tree").bind("loaded.jstree", function(event, data) {
		$("#old_tree").jstree("deselect_all");
		$("#old_tree").jstree("open_all");
		//$("#old_tree").jstree("check_all");
		$('.tdeck').find('ins.jstree-checkbox').hide();
		$('.jstree-clicked').removeClass('jstree-clicked');
	}).bind("change_state.jstree", function(e, data){
		//console.log(data);

        if(data.inst.get_checked().length>1)
           data.inst.uncheck_node(data.rslt[0]);
	}).bind("hover_node.jstree",function(event, data) {

	}).bind("dehover_node.jstree", function(event, data) {

	}).bind("select_node.jstree", function(event, data) {
		window.open(data.rslt.obj[0].childNodes[1].href);
	}).bind("refresh.jstree", function(event, data) {

	}).jstree(
			{
				"core" : {
					"html_titles" : true
				},
				"json_data" :{ "data" :
				<?php
				echo $old_content;
				?>}
				,
				"themes" : {
					"theme" : "default",
					"dots" : true,
					"icons" : true
				},
				 checkbox : { "two_state" : true } ,
				"plugins" : [ "themes", "json_data", "ui","checkbox" ]
			});	
});

</script>
<style>
.with-scroll {
	overflow-y: scroll;
	overflow-x: hidden;
	width: 300px;
	height: 400px;
	-webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
}

.new-item {
	-webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
	background: #55ff55;
}
.removed-item {
	-webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
	background: #ff5544;
}
.modified-item {
	-webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
	background: #ffff55;
}
</style>
<div class="content">
<div style="display:none;" id="legend">
<table cellspacing="2" cellpadding="2">
<tr>
<td class="new-item">New item or revision added.</td>
<td class="removed-item">Item deleted.</td>
<td class="modified-item">Position of the item changed!</td>
</tr>
</table>
</div>
<table>
	<tr>
		<td>
		<div><b>Old Deck:</b><br>
		<div id="old_tree" class="with-scroll"></div>
		</div>
		</td>
		<td>
		<div><b>New Deck:</b><br>
		<div id="new_tree" class="with-scroll"></div>
		</div>
		</td>
	</tr>
	<tr>
		<td colspan="2">
		<center><a class="btn" onclick="compare_decks()"> Compare Decks </a> <a
			class="btn info" onclick="compare_selected_slides()"> Compare
		Selected Slides </a></center>
		</td>
	</tr>
</table>
</div>