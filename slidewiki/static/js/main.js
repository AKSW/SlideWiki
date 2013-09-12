$(function() {
	if (typeof deck == 'undefined')
		return;
	$.jstree._themes = "libraries/frontend/jstree/themes/";
	//record the changes in slide content
	$('[contenteditable]').live('focus', function() {
		$('.editor-bar').show();
	    var $this = $(this);
	  //only store the original value and compare the change according to that
	    if (!$this.data('before')) {
	    	var content=$.trim($this.text());
	    	if(!content || content=='« Click to add title »' || content=='« Click to add text »' || content=='« Click to add note »'){
	    		$this.data('before', '<<placeholder>>');
	    	}else{
	    		$this.data('before', $.trim($this.html()));
	    	}
	    }
	    item_change = jQuery.unique(item_change);
	    item_change=manual_cleanArray(item_change);
	    if(item_change.length){
	    	enable_save_button();
	    }
	    return $this;
	}).live('blur keyup paste', function() {
	    var $this = $(this);
	    //console.log($this.data('before'));
	    //console.log($.trim($this.text()));
		//update html source code
		if($('#modal_html_code').css('display')!='none')
			fill_source_code(selected_id);
	    //perform scaling:
	    var whole_id=$(this)[0].id;
	    var parts = whole_id.split("_");
	    var selected_id=parts[2];
	    if(parts[1]=='title' || parts[1]=='body'){
	    	applyScaling(selected_id)	
	    }
    	//consider conditions when text is empty and is compared with place holder
	    if (($this.data('before') !== $.trim($this.html())) && ($this.data('before')!='<<placeholder>>' || $.trim($this.text())!='')) {
	        //$this.data('before', $this.html());
			    item_change.push($this[0].id);
			    enable_save_button();
		        $this.trigger('change');
	    }else if($this.data('before') == $.trim($this.html()) || ($this.data('before')=='<<placeholder>>' && $.trim($this.text())=='')){
		    if($("#save_changes_button").hasClass('save_active')){
		    	//consider multiple changes
		    	item_change = jQuery.unique(item_change);
		    	//manual cleaning
		    	item_change =manual_uniquearr(item_change);
		    	item_change =manual_cleanArray(item_change);
		    	if(item_change.indexOf($this[0].id)!=-1){
			    	if(item_change.length==1){
				    	$("#save_changes_button").removeClass('save_active');
				    	$("#save_changes_button").addClass('save_inactive');
				    	item_change=[];
			    	}else{
			    		item_change.splice(item_change.indexOf($this[0].id), 1);
			    	}
		    	}
		    }	    	
	    }
	    return $this;
	});
	//clicking on other tabs should hide the editor
	$('#discussionlink').click(function(){
		hide_editor_toolbar();
		hide_save_toolbar();
		disable_save_button();
	})
	$('#questionslink').click(function(){
		hide_editor_toolbar();
		hide_save_toolbar();
		disable_save_button();
	})
	$('#usagelink').click(function(){
		hide_editor_toolbar();
		hide_save_toolbar();
		disable_save_button();
	})
	$('#historylink').click(function(){
		hide_editor_toolbar();
		hide_save_toolbar();
		disable_save_button();
	})	
	$("#tree").bind("loaded.jstree", function(event, data) {
		// do sth after loading the tree
		//$("#tree").jstree("open_all");
		//create node selection range
		fillAllSlidesArray();
		// initialize address
		handleInitialAddress();
		//disables context menu in case user is not logged in
		disableContextMenu();
		MathJax.Hub.Queue([ "Typeset", MathJax.Hub, 'tree' ]);	
	}).bind("refresh.jstree", function(event, data) {
		var nodes=$.cookie('my_jstree_open');
		if(!nodes || ($.trim(nodes)=='') || (typeof nodes === 'undefined')) {}else{
			nodes=nodes.split(",");
			$.each(nodes, function(k,v){
				$("#tree").jstree("open_node", $(v));
			})
			$.cookie('my_jstree_open',null);
		}
		MathJax.Hub.Queue([ "Typeset", MathJax.Hub, 'tree' ]);
	}).bind("rename_node.jstree", function(event, data) {
		var title = data.rslt.name;
		var selected_id = data.rslt.obj[0].children[1].id;
		clearMathJaxRendering(selected_id);
		renameItem(selected_id, title);
	}).bind("move_node.jstree", function(event, data) {
		// handle change in the position of slides
		var temp;
		var selected_id = data.rslt.o[0].children[1].id;
		var target_id = data.rslt.r[0].children[1].id;
		var pos_stat = data.rslt.p;
		//todo:also consider target node
		if (confirmNewRevision(selected_id)){ 
			item_change=manual_cleanArray(item_change);
			if(item_change.length){
				var answer = confirm("You have some changes which are not saved yet! Do you want to save them before any new action?");
				if (answer) {
					save_changes();
				} else {
					resetGolbalVars();
					moveItem(selected_id, target_id, pos_stat);
				}
			}else{
				moveItem(selected_id, target_id, pos_stat);
			}			
		}
	}).bind(
			"hover_node.jstree",
			function(event, data) {
				var hovered_id = data.rslt.obj[0].children[1].id;
				var hovered_properties = getPropertiesFromId(hovered_id);
				$(".jstree-hovered").removeClass("jstree-hovered");
				if (hovered_properties['type'] == 'deck') {
					$(".deck-" + hovered_properties['itemId']).addClass(
							"jstree-hovered");
				} else {
					$(".slide-" + hovered_properties['itemId']).addClass(
							"jstree-hovered");
				}
			}).bind("dehover_node.jstree", function(event, data) {
		$(".jstree-hovered").removeClass("jstree-hovered");
	}).bind("select_node.jstree", function(event, data) {
		// `data.rslt.obj` is the jquery extended node that was clicked
		var selected_id = data.rslt.obj[0].children[1].id;
		//get properties
		var parts=getPropertiesFromId(selected_id);
		//load the content related to the active tab
		var current_tab=$('#item_tabs .active').text().trim();
		if(parts['type']=='slide')
			$('#editlink').hide();
		else{
			$('#editlink').show();
			$("#viewlink")[0].children[0].textContent="View";
		}
		switch(current_tab){
		case 'Edit':
		case 'View':
			selectNode(selected_id);
			show_deck_brand();
			break;
		case 'Discussion':
			showDiscussion(parts['type'],parts['itemId']);
			updateModeAddress(selected_id,"discussion");
			break;
		case 'Questions':
			parts['type']=='slide'?showSlideQuestions(parts['itemId']):showDeckQuestions(parts['itemId']);
			updateModeAddress(selected_id,"quest")
			break;
		case 'History':
			parts['type']=='slide'?showSlideRevisions(parts['itemId']):showDeckRevisions(parts['itemId']);
			updateModeAddress(selected_id,"history");
			break;
		case 'Usage':
			parts['type']=='slide'?showSlideUsage(parts['itemId']):showDeckUsage(parts['itemId']);
			updateModeAddress(selected_id,"use");
			break;			
		}	
		updateTabURLs(selected_id,parts['type']);
		createBreadCrumb(selected_id);
	}).bind("refresh.jstree", function(event, data) {
		//$("#tree").jstree("open_all");
		//create list of slides from tree
		fillAllSlidesArray();
		//it gets the node_id which needs to be selected after tree is refreshed
		var after_refresh_node=$('#after_refresh_node').text().trim();
		if(after_refresh_node){
			//deselect previous nodes if exist
			$.jstree._focused().deselect_all();
			//select defined node in #after_refresh_node
			$("#tree").jstree("select_node", "#"+after_refresh_node+'-node'); 
			//selectNode(after_refresh_node);
		}
	}).jstree(
			{
				"core" : {
					"html_titles" : true
				},
				"json_data" : {
					"ajax" : {
						"url" : 
							function(node){
							var nodeId="";
							var url="";
							var tmp="";
							if(node==-1){
								url="ajax/getDeckTree/deck/" + deck;
							}else{
								nodeId=node.attr('id');
								tmp = getPropertiesFromId(nodeId);
								url="ajax/getDeckChildrenTree/deck/" + tmp['itemId'];
							}
							return url;
						},
							
						"data" : function(n) {
							return {
								id : n.attr ? n.attr("id") : 0
							};
						}
					}
				},
				"crrm" : {
					"move" : {
						"check_move" : function(m) {
							var selected=m.o[0].id;
							selected=selected.split("-node")[0];
							golabal_var1=new Array();
							golabal_var1=getNestedDecksList(selected);
							if (m.p != 'inside') {
								return true;
							}
							if (!getCurrenUserID()) {
								alert('You must log in to the system to move items!');
								return false;
							}
							if(isLastItem(selected)){
								alert('A deck must have at least one item!');
								return false;
							}							
						}
					}
				},
                                "dnd" : {
                                        "drop_target" : false,
                                        "drag_target" : ".jstree-draggable",
                                        "drag_check" : function (data) {
                                            if($(data.o).attr("question") != "true") {
                                                return false;
                                            }
                                            return {
                                                after : false,
                                                before : false,
                                                inside : true
                                            };
                                        },
                                        "drag_finish" : function (data) {
                                            var slide = $(data.r).attr('id');
                                            var slide_array = getPropertiesFromId(slide);
                                            var slide_id = slide_array['itemId'];
                                            var question = $(data.o).attr('quest_id');
                                            reAssign(question, slide_id);
                                        }
                                },

				"contextmenu" : {
					items : {
						"ccp" : false,
						"remove" : false,
						"create" : false,
						"rename" : false,
						"renameItem" : {
							"label" : "Rename",
							"separator_before" : true,
							"separator_after" : false,
							"action" : function(obj) {
								var selected_id = obj[0].children[1].id;
								//get the full title instead of shortend one
								tmp=$('#'+selected_id).attr('title');
								tmp=$.trim(tmp.split('|')[0]);
								//if a deck is selected sends its last child to check for the new deck revision
								var to_check=selected_id;
								var p=getPropertiesFromId(selected_id);
								if(p['type']=='deck'){
									to_check=getLastChildFromId(selected_id);
								}								
								if (confirmNewRevision(to_check)) {
									item_change=manual_cleanArray(item_change);
									if(item_change.length){
										var answer = confirm("You have some changes which are not saved yet! Do you want to save them before any new action?");
										if (answer) {
											save_changes();
										} else {
											resetGolbalVars();
											//set full title
											$("#tree").jstree('set_text', "#"+selected_id+'-node' , tmp );
											//rename node
											$("#tree").jstree("rename","#"+selected_id+'-node');
										}
									}else{
										//set full title
										$("#tree").jstree('set_text', "#"+selected_id+'-node' , tmp );
										//rename node
										$("#tree").jstree("rename","#"+selected_id+'-node');
									}									
								}
							}
						},						
						"deleteItem" : {
							"label" : "Delete",
							"separator_before" : true,
							"separator_after" : false,
							"action" : function(obj) {
								var selected_id = obj[0].children[1].id;
								if(isLastItem(selected_id)){
									alert('A deck must have at least one item!');
								}else{
									if (confirmNewRevision(selected_id)) {
										//$("#tree").jstree("delete_node",$("#"+selected_id));
										item_change=manual_cleanArray(item_change);
										if(item_change.length){
											var answer = confirm("You have some changes which are not saved yet! Do you want to save them before any new action?");
											if (answer) {
												save_changes();
											} else {
												resetGolbalVars();
												deleteItem(selected_id);
											}
										}else{
											deleteItem(selected_id);
										}										
									}
								}
							}
						},	
						"duplicateItem" : {
							"label" : "Duplicate item",
							"separator_before" : true,
							"separator_after" : true,
							"action" : function(obj) {
								var selected_id = obj[0].children[1].id;
								//if a deck is selected sends its last child to check for the new deck revision
								var p=getPropertiesFromId(selected_id);
								if(p['type']=='deck'){
									alert('The feature is only supported for slides now!');
									return;
								}else{
									if (confirmNewRevision(selected_id)) {
										// hide ajax loader after the action is
										// finished
										item_change=manual_cleanArray(item_change);
										if(item_change.length){
											var answer = confirm("You have some changes which are not saved yet! Do you want to save them before any new action?");
											if (answer) {
												save_changes();
											} else {
												resetGolbalVars();
												duplicateItem("slide", selected_id);
											}
										}else{
											duplicateItem("slide", selected_id);
										}											
										$("#ajax_progress_indicator").css(
												'display', 'none');
									}	
								}
							}
						},						
						"appendSlide" : {
							"label" : "Append new slide",
							"separator_before" : true,
							"separator_after" : false,
							"action" : function(obj) {
								var selected_id = obj[0].children[1].id;
								//if a deck is selected sends its last child to check for the new deck revision
								var p=getPropertiesFromId(selected_id);
								if(p['type']=='deck'){
									selected_id=getLastChildFromId(selected_id);
								}
								if (confirmNewRevision(selected_id)) {
									item_change=manual_cleanArray(item_change);
									if(item_change.length){
										var answer = confirm("You have some changes which are not saved yet! Do you want to save them before any new action?");
										if (answer) {
											save_changes();
										} else {
											resetGolbalVars();
											appendItem("slide", selected_id, 0);
										}
									}else{
										appendItem("slide", selected_id, 0);
									}
									// hide ajax loader after the action is
									// finished
									$("#ajax_progress_indicator").css(
											'display', 'none');
								}
							}
						},
						"appendExistingSlide" : {
							"label" : "Append existing slide",
							"separator_before" : false,
							"separator_after" : true,
							"action" : function(obj) {
								var selected_id = obj[0].children[1].id;
								//if a deck is selected sends its last child to check for the new deck revision
								var p=getPropertiesFromId(selected_id);
								if(p['type']=='deck'){
									selected_id=getLastChildFromId(selected_id);
								}								
								if (confirmNewRevision(selected_id)) {
									
									// hide ajax loader after the action is
									// finished
									item_change=manual_cleanArray(item_change);
									if(item_change.length){
										var answer = confirm("You have some changes which are not saved yet! Do you want to save them before any new action?");
										if (answer) {
											save_changes();
										} else {
											resetGolbalVars();
											appendItem("slide", selected_id, 1);
										}
									}else{
										appendItem("slide", selected_id, 1);
									}									
									$("#ajax_progress_indicator").css(
											'display', 'none');
								}
							}
						},
						"appendDeck" : {
							"label" : "Append new deck",
							"action" : function(obj) {
								var selected_id = obj[0].children[1].id;
								//if a deck is selected sends its last child to check for the new deck revision
								var p=getPropertiesFromId(selected_id);
								if(p['type']=='deck'){
									selected_id=getLastChildFromId(selected_id);
								}
								if (confirmNewRevision(selected_id)) {
									
									// hide ajax loader after the action is
									// finished
									item_change=manual_cleanArray(item_change);
									if(item_change.length){
										var answer = confirm("You have some changes which are not saved yet! Do you want to save them before any new action?");
										if (answer) {
											save_changes();
										} else {
											resetGolbalVars();
											appendItem("deck", selected_id, 0);
										}
									}else{
										appendItem("deck", selected_id, 0);
									}									
									$("#ajax_progress_indicator").css(
											'display', 'none');
								}
							}
						},
						"appendExistingDeck" : {
							"label" : "Append existing deck",
							"action" : function(obj) {
								var selected_id = obj[0].children[1].id;
								//if a deck is selected sends its last child to check for the new deck revision
								var p=getPropertiesFromId(selected_id);
								if(p['type']=='deck'){
									selected_id=getLastChildFromId(selected_id);
								}								
								if (confirmNewRevision(selected_id)) {
									
									// hide ajax loader after the action is
									// finished
									item_change=manual_cleanArray(item_change);
									if(item_change.length){
										var answer = confirm("You have some changes which are not saved yet! Do you want to save them before any new action?");
										if (answer) {
											save_changes();
										} else {
											resetGolbalVars();
											appendItem("deck", selected_id, 1);
										}
									}else{
										appendItem("deck", selected_id, 1);
									}										
									$("#ajax_progress_indicator").css(
											'display', 'none');
								}
							}
						},
						"findReplace" : {
							"label" : "Find and Replace",
							"separator_before" : true,
							"action" : function(obj) {	
								var selected_id = obj[0].children[1].id;
								prepare_find_replace(selected_id);
								 $("#modal_findreplace").draggable({
									    handle: ".modal-header"
									});
								$('#modal_findreplace').modal('show');
							}
						}						
					}

				},
				"themes" : {
					"theme" : "default",
					"dots" : true,
					"icons" : true
				},	
				"cookies":{
					"auto_save":true
					//"save_opened": true
				},
				"plugins" : [ "themes", "json_data", "crrm", "dnd", "ui",
						"contextmenu","cookies" ]
			});
});
