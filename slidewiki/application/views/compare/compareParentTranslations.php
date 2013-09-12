<script src="static/js/compare.js"></script>
<script type="text/javascript">



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
		<div><b>The deck was translated from this revision:</b><br>
		<div id="old_tree" class="with-scroll-modal"></div>
		</div>
		</td>
		<td>
		<div><b>There is the last revision of source deck:</b><br>
		<div id="new_tree" class="with-scroll-modal"></div>
		</div>
		</td>
	</tr>
	<tr>
		<td colspan="2">
		<center>
                    <a class="btn" onclick="compare_decks()"> Compare Decks </a> 
                    <a class="btn info" onclick="compare_selected_slides()"> Compare Selected Slides </a>
                    <a class="btn error" onclick='updateTranslation(<?php echo $last_id?>, "<?php echo $language_id;?>","<?php echo $language_name;?>" )'> Update deck </a>
                </center>
		</td>
	</tr>
</table>
</div>
