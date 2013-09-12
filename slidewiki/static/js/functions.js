

/*------------------Loading slides/decks functions----------------------*/
// selects and loads the node content in view mode
function selectNode(selected_id) {
	//to ensure that no other node is selected
	$.jstree._focused().deselect_all();
	//handle errors if node does not exist or the position is incorrect
	if(!$('#tree').find("#"+selected_id).length){
		//just select the root deck for now
		//todo: direct to correct node not root deck
		selected_id='tree-0-deck-'+deck+'-1';
		$("#ajax_progress_indicator").css('display', 'none');
	}
	_isEditing = false;
	//createBreadCrumb(selected_id);
	var selected_properties = getPropertiesFromId(selected_id);
	//open the parent node
	if(selected_properties['deckId']!=0)
		$("#tree").jstree("open_node", $('#'+getParentFromId(selected_id)+"-node"));	
	if(selected_properties['type']=='deck' || selected_properties['type']=='slide'){
		//fixed a bug: to prevent duplicate slides -- we have class slide for history items as well which conflicts wit view mode
		$("#itemhistory").html('');	
		$("#itemhistory").removeClass();
	}
	$(".jstree-hovered").removeClass("jstree-hovered");
	$(".jstree-clicked").removeClass("jstree-clicked");
	$("#" + selected_id).addClass("jstree-clicked");
	//console.log($('#tree').jstree('get_selected'));
	updateModeAddress(selected_id, 'view',1);
	if (selected_properties['type'] == 'deck') {
		updateTabURLs(selected_id,"deck");
		$(".deck-" + selected_properties['itemId']).addClass("jstree-clicked");
		$("#editlink").show();
                $('#slideview').css('min-height','auto');
		loadDeck(selected_properties['itemId']);
	} else {
		$("#editlink").hide();
                $('#slideview').css('min-height','auto');
		$(".slide-" + selected_properties['itemId']).addClass("jstree-clicked");
		updateTabURLs(selected_id, "slide");
		/*
		if (!is_deck_loaded)
			loadSlide(selected_properties['deckId'],selected_properties['itemId'], selected_properties['pos']);
		*/
		//console.log(loaded_range);
		checkUnsavedChanges(selected_id,'viewslide')
	}	
	window.scrollTo(0, 0);//prevent from jumping to anchor
}
function checkUnsavedChanges(id,action){
	if (loaded_range.indexOf(id+'-node')==-1){
		loaded_range=[];
		item_change=manual_cleanArray(item_change);
		if(item_change.length){
			var answer = confirm("You have some changes which are not saved yet! Do you want to save them?");
			if (answer) {
				save_changes();
			} else {
				resetGolbalVars();
				switch(action){
					case 'viewslide':
						progressiveLoadSlide(id);
						break;
					case 'viewdeck':
						progressiveLoadSlide(id);
						break;						
				}
				
			}
		}else{
			switch(action){
				case 'viewslide':
					progressiveLoadSlide(id);
					break;
				case 'viewdeck':
					progressiveLoadSlide(id);
					break;						
		}
		}
	}else{
		//highlight terms if find-replace modal is visible
		if($('#modal_findreplace').css('display')!='none'){
			$('#'+id+'-view .slide-content').removeHighlight();
			$('#'+id+'-view .slide-content').highlight($('#findreplace_term').val());
		}
	}
}
// only highlights a node
function highlightNode(selected_id) {
	var selected_index,overal_index;
	var selected_properties = getPropertiesFromId(selected_id);
	if(selected_properties['type']!='slide') return 0;
	createBreadCrumb(selected_id);	
	$(".jstree-hovered").removeClass("jstree-hovered");
	$(".jstree-clicked").removeClass("jstree-clicked");
	$("#" + selected_id).addClass("jstree-clicked");
	updateModeAddress(selected_id, 'view',0);// gives an error (infinite loop)?!
	// with array keys
	if (selected_properties['type'] == 'deck') {
		//[TODO]does it really works? it seems that it is not used at all
		if ($("#tree").jstree("get_selected")[0])
			updateTabURLs($("#tree").jstree("get_selected")[0].children[1].id,
					"deck");
		$(".deck-" + selected_properties['itemId']).addClass("jstree-clicked");
		$('#additional-elements').show();
		$('#additional-elements').html($('.deck-contributors').html());
                $('#slideview').css('min-height','none');                
                //deck stream
                $('#deck_stream_button_div').show();
                $('#deck_stream_button').attr('deck_id', selected_properties['itemId']); 
	} else {
            $('#deck_stream_button_div').hide();
            $('#slideview').css('min-height','780px');
		$(".slide-" + selected_properties['itemId']).addClass("jstree-clicked");
		updateTabURLs(selected_id, "slide");
		//show contributors of slide
		if(!show_full_flag)
			$('#additional-elements').show();
		$('#additional-elements').html($('#'+selected_id+'-view .slide-contributors').html());
		//update html source code
		if($('#modal_html_code').css('display')!='none'){
			cleanExtraTags('slide_body_'+selected_id);
			fill_source_code(selected_id);
		}
		//update share link
		if($('#modal_share_link').css('display')!='none')
			showShareLink('slide',selected_properties['itemId']);
		//highlight terms
		if($('#modal_findreplace').css('display')!='none'){
			$('#'+selected_id+'-view .slide-content').removeHighlight();
			$('#'+selected_id+'-view .slide-content').highlight($('#findreplace_term').val());
		}
		if($('#tree_tool_search').length){
			$('#'+selected_id+'-view .slide-content').removeHighlight();
			$('#'+selected_id+'-view .slide-content').highlight($('#tree_tool_search').val());
		}			
        //update language
            $("#current_language").attr('lang', $('#'+selected_id+'-view .slide-lang').attr('lang'));
            $("#current_language_name").empty().html("Language: <b>" + $('#'+selected_id+'-view .slide-lang').attr('lang_name') + "</b>"); 
            
            
        
		//update slide follow status
		$('#deck_follow_status').html($('#'+selected_id+'-view .slide-follow').html());
		//handle progressive load here when we are reaching the outside of predefined range
		selected_index=loaded_range.indexOf(selected_id+'-node');
		overal_index=all_slides.indexOf(selected_id+'-node')
		//do not load when load_range is 0
		if(((selected_index==loaded_range.length-1) && loaded_range.length)|| (selected_index==0)){
			//prevent loop for first and last slides of root deck
			if((overal_index!=0)&& (overal_index!=all_slides.length-1)){
				loaded_range=[];
				item_change=manual_cleanArray(item_change);
				if(item_change.length){
					var answer = confirm("You have some changes which are not saved yet! Do you want to save them?");
					if (answer) {
						save_changes();
					} else {
						resetGolbalVars();
						progressiveLoadSlide(selected_id);
					}
				}else{
					progressiveLoadSlide(selected_id);
				}
			}
		}
		applyScaling(selected_id);
		window.scrollTo(0, 0);//prevent from jumping to anchor
	}
}
function progressiveLoadSlide(selected_id) {
	var range_positions=new Array();
	$("#deck-view").removeClass('deckOverviewItem');
	//hide the editor
	hide_editor_toolbar();
	hide_save_toolbar();
	//show fullscreen button
	show_fullscreen_button();
	var user = getCurrenUserID(); // get the current user_id
	// prevent duplicate rendering
	if ($("#slideview").html() != '') {
		$("#slideview").html('');
	}
	if (loaded_range.indexOf(selected_id+'-node')==-1) {
		//create loaded_range for the selected_id
		//todo think about duplicate slides and problems arise
		loaded_range=[];
		range_positions=fillRangeOfSlides(selected_id);
		//console.log(range_positions);
		//console.log(loaded_range);
		$.ajax({
					url : 'ajax/slidesByRange/deck/' + deck+'/from/'+range_positions[0]+'/to/'+range_positions[1],
					success : function(msg) {
						var data = eval("(" + msg + ")");
						// add current user id
						data.current_user_id = user;
						$("#slide_preview").tmpl(data).appendTo("#slideview");
						//$("#slideview").append('<div class="slide-footer">'+data.footer_text+'<div class="deck-status"><span class="deck-status-current"></span>/<span class="deck-status-total"></span></div></div><p class="deck-status"><span class="deck-status-current"></span>/<span class="deck-status-total"></span></p><a  class="deck-prev-link" title="Previous">&#8592;</a><a href="#" class="deck-next-link" title="Next">&#8594;</a>');
						$("#slideview").append('<div class="deck-status"><span id="current_slide_number" class="deck-status-current"></span>/<span id="total_slides_number">'+all_slides.length+'</span></div>');
						//hide the header image in edit mode
						$(".slide-header").css('background-image','none');
						// save data about revision status of slides in a hidden
						// div
						$("#slides_revision_status").html('');
						$('#activity_stream').empty();
						$('#activity_stream').hide();
						$("#slide_revision_status").tmpl(data).appendTo(
								"#slides_revision_status");
						// prevent duplicate rendering
						if ($("#deck_follow_status").html() != '') {
							$("#deck_follow_status").html('');
						}
						$("#follow_status_deck").tmpl(data).appendTo(
								"#deck_follow_status");
						$("#deck_title").text(data.title);
						selectTab('view');
						// prevent duplicate ajax calls
						is_deck_loaded = true;
						$("#save_changes_button").addClass('save_inactive');
						
						$.deck('.slide');
						$.deck('hideMenu');
						$.deck('iframes');
						$('#slideview').css('min-height','780px');
						$('.slide-original-source').linkify();
						//highlight terms
						if($('#modal_findreplace').css('display')!='none'){
							$('#'+selected_id+'-view .slide-content').removeHighlight();
							$('#'+selected_id+'-view .slide-content').highlight($('#findreplace_term').val());
						}
						if($('#tree_tool_search').length){
							$('#'+selected_id+'-view .slide-content').removeHighlight();
							$('#'+selected_id+'-view .slide-content').highlight($('#tree_tool_search').val());
						}						
						//$.deck('enableScale');
						MathJax.Hub.Queue([ "Typeset", MathJax.Hub, 'slideview' ]);	
						if(user){
							//change view label to edit
							$("#viewlink")[0].children[0].textContent="Edit";
							$(".slide-title").attr('contentEditable',false);
							$(".slide-title").click(function(){
								$('.slide').removeHighlight();
								show_save_toolbar();
								if($.trim($(this).text())=='« Click to add title »'){
									$(this).html('&nbsp;');
								}
								//warning if new revision for deck will be created
								if(!get_active_slide_id() || get_active_slide_id()!=get_node_id_from_parts(this.id)){
									new_revision_notification(get_node_id_from_parts(this.id),'edit_slide_title');
								}else{
									edit_slide_title_ok(get_node_id_from_parts(this.id));
								}
							}).bind('blur keyup paste', function(e) {
							      $(this).find('b, i, font, h1, h2, h3, h4, h5, p, span, div ,pre,ul,li,ol').each(function () {
							          $(this).contents().first().unwrap();
							        });
							      if (e.which != 32) { //space is allowed
							    	  $(this).find('br, hr').remove();
							      }

							}).bind('keydown', function(e) {//prevent enter button
								if (e.which == 13) {
									e.preventDefault();
								}

							});
							
							$(".slide-note").click(function(){
								$('.slide').removeHighlight();
								show_save_toolbar();
								if($.trim($(this).text())=='« Click to add note »'){
									$(this).html('&nbsp;');
								}
								//waning if new revision for deck will be created
								if(!get_active_slide_id() || get_active_slide_id()!=get_node_id_from_parts(this.id)){
									new_revision_notification(get_node_id_from_parts(this.id),'edit_slide_note');
								}else{
									edit_slide_note_ok(get_node_id_from_parts(this.id));
								}
							}).bind('blur keyup paste', function() {
							      $(this).find('b, i, font, h1, h2, h3, h4, h5, p, span, div ,pre,ul,li,ol').each(function () {
							          $(this).contents().first().unwrap();
							        });
							});		
							//adding original source
							$(".slide-original-source").bind('blur keyup paste', function(e) {
							      $(this).find('b, i, font, h1, h2, h3, h4, h5, p, span, div ,pre,ul,li,ol').each(function () {
							          $(this).contents().first().unwrap();
							        });
							      if (e.which != 32) { //space is allowed
							    	  $(this).find('br, hr').remove();
							      }

							}).bind('keydown', function(e) {//prevent enter button
								if (e.which == 13) {
									e.preventDefault();
								}

							});							
						}
						if(show_full_flag)
							apply_fullscreen_slide();
						applyScaling(selected_id);
					}
				});
	}
}
function getSlideStatusForProgressive(selected_id){
	return all_slides.indexOf(selected_id+'-node')+1;
}
function fillRangeOfSlides(selected_id){
	var start,end;
	var i=0;
	var output=new Array();
	//range window size -> n*2 +1
	var tmp,top_add,bottom_add,total,n=5;
	var selected_index=all_slides.indexOf(selected_id+'-node');
	total=(all_slides.length)-1;
	//console.log(selected_index);
	//console.log(total);
	tmp=selected_index-n;
	if(tmp<0){
		start=0;
		bottom_add=n-selected_index; //to balance the range
	}else{
		start=tmp;
		bottom_add=0;
	}
	if((selected_index+n)>total){
		end=total;
		top_add=n-(total-selected_index);
		for (i=1;i<=top_add;i++)
		{
			if((start-1)>=0){
				start=start-1;
			}
		}
	}else{
		top_add=0;
		end=selected_index+n;
		for (i=1;i<=bottom_add;i++)
		{
			if((end+1)<total){
				end=end+1;
			}
		}
	}
	//create range
	//console.log(bottom_add+' - '+top_add)
	//loaded_range=[];
	//todo: notify users for saving content
	resetGolbalVars();
	for (i=start;i<=end;i++)
	{
		loaded_range.push(all_slides[i]);
	}
	return Array(start, end);
}
function fillAllSlidesArray(){
	all_slides=[];
	all_slides=createSlidesList('tree-0-deck-'+deck+'-1-node');
}
function createSlidesList(node_id){
	var output=new Array();
	$.each($('.jstree-leaf'),function(index,value){
		output.push(value.id);
	});
	//console.log(output);
	return output;
}
var slidecontent;
// var slides=new Object();
function loadSlide(deckId, id, pos) {
	$("#deck-view").removeClass('deckOverviewItem');
	//hide the editor
	hide_editor_toolbar();
	hide_save_toolbar();
	//show fullscreen button
	show_fullscreen_button();
	//hide edit tab
	$("#editlink").css('display','none');
	var user = getCurrenUserID(); // get the current user_id
	var selected_id = "tree-" + deckId + "-slide-" + id + "-" + pos;
	// prevent duplicate rendering
	if ($("#slideview").html() != '') {
		$("#slideview").html('');
	}
	if (!is_deck_loaded) {
		$
				.ajax({
					url : 'ajax/showDeckContent/' + deck,
					success : function(msg) {
                                                var data = eval("(" + msg + ")");
						// add current user id
						data.current_user_id = user;
						$("#slide_preview").tmpl(data).appendTo("#slideview");
						$("#slideview")
								.append(
										'<div class="slide-footer">'+data.footer_text+'<div class="deck-status"><span class="deck-status-current"></span>/<span class="deck-status-total"></span></div></div><p class="deck-status"><span class="deck-status-current"></span>/<span class="deck-status-total"></span></p><a class="deck-prev-link pointer-cursor" title="Previous">&#8592;</a><a class="deck-next-link pointer-cursor" title="Next">&#8594;</a><form action="." method="get" class="goto-form"><label for="goto-slide">Go to slide:</label><input type="number" name="slidenum" id="goto-slide"><input type="submit" value="Go"></form>');
						//hide the header image in edit mode
						$(".slide-header").css('background-image','none');
						// save data about revision status of slides in a hidden
						// div
						$("#slides_revision_status").html('');
						$("#slide_revision_status").tmpl(data).appendTo(
								"#slides_revision_status");
						// prevent duplicate rendering
						if ($("#deck_follow_status").html() != '') {
							$("#deck_follow_status").html('');
						}
						$("#follow_status_deck").tmpl(data).appendTo(
								"#deck_follow_status");
                                                $("#deck_title").text(data.title);                                                
						//selectTab('view');
						// prevent duplicate ajax calls
						is_deck_loaded = true;
						$("#save_changes_button").addClass('save_inactive');
						//to prevent duplicate slides -- we have class slide for history items
						$("#itemhistory").html('');						
						$.deck('.slide');
						$.deck('hideMenu');
						//$.deck('enableScale');
						//MathJax.Hub.Queue([ "Typeset", MathJax.Hub, 'slideview' ]);	
						if(user){
							//change view label to edit
							$("#viewlink")[0].children[0].textContent="Edit";
							$(".slide-title").attr('contentEditable',false);
							$(".slide-title").click(function(){
								show_save_toolbar();
								if($.trim($(this).text())=='« Click to add title »'){
									$(this).html('&nbsp;');
								}
								//warning if new revision for deck will be created
								if(!get_active_slide_id() || get_active_slide_id()!=get_node_id_from_parts(this.id)){
									if(!confirmNewRevision(get_node_id_from_parts(this.id))){
										$(".slide-title").attr('contentEditable',false);
										hide_editor_toolbar();
										hide_save_toolbar();
										return;
									}else{
										$(".slide-title").attr('contentEditable',true);
									}
								}
								if(store_active_editor(this.id)){
										editor.activate($(this), {multiline: false,controlsTarget: $('#editor-toolbar'), placeholder: 'Click to add title'});
										editor.disable();
								}
							});
							
							$(".slide-note").click(function(){
								show_save_toolbar();
								if($.trim($(this).text())=='« Click to add note »'){
									$(this).html('&nbsp;');
								}
								//waning if new revision for deck will be created
								if(!get_active_slide_id() || get_active_slide_id()!=get_node_id_from_parts(this.id)){
									if(!confirmNewRevision(get_node_id_from_parts(this.id))){
										$(".slide-note").attr('contentEditable',false);
										hide_editor_toolbar();
										hide_save_toolbar();
										return;
									}else{
										$(".slide-note").attr('contentEditable',true);
									}
								}
								if(store_active_editor(this.id)){
									editor.activate($(this), {controlsTarget: $('#editor-toolbar'), placeholder: 'Click to add note'});
									editor.disable();	
								}
							});	
							applyScaling(selected_id);
													
						}
					}
				});
	}
}
//loads a deck from an index
function loadDeckFrom(id,from) {
	var user = getCurrenUserID(); // get the current user_id
	$.ajax({
		url : 'ajax/showDeckPreviewProgressive/' + id+'/from/'+from,
		success : function(msg) {
			var data = eval("(" + msg + ")");
			data.current_user_id = user;
			$("#show_more_slides").remove();
			$("#deck_preview_partial").tmpl(data).appendTo("#slideview");
                        if(parseInt(from+data.slides.length)<data.size){
				//show the "show more..." button
				$("#slideview").append('<div id="show_more_slides"><center><a class="btn" onclick="loadDeckFrom('+id+','+parseInt(from+data.slides.length)+')">Show more...</a></center></div>');
			} 
                        //deck stream
                        $('#deck_stream_button_div').show();
                        $('#deck_stream_button').attr('deck_id',id); 
		}
	});	
}
function getDeckInfo(deck_revision_id){
    $.ajax({
        url : './?url=ajax/getDeckLite&id=' + deck_revision_id,
        success : function(msg){
            var data = eval('(' + msg + ')');
            $('#deck_profile').empty();
            $('#deck_profile_script').tmpl(data).appendTo($('#deck_profile'));
        }
    })
}
function changeFilter(button){
    if (button.attr('filter') == 1){
        button.attr('filter', 0); 
    }else{
        button.attr('filter', 1); 
    }
    $('#activity_stream').empty();           
}
function getMonthName(month_number){
    var monthNames = [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ];
    return monthNames[month_number];
}
function applyFilterDeckStream(button,deck_id){
    changeFilter(button);
    showDeckStream(deck_id,'','');
}
function applyFilterSlideStream(button,slide_id){
    changeFilter(button);
    showSlideStream(slide_id,'','');
}

function showShortDeckStream(deck_revision_id){
    $.ajax({
        url : './?url=ajax/getShortStream&id=' + deck_revision_id,
        async : true,
	beforeSend: function() {
		$("#ajax_progress_indicator").css('display', 'none');
				$("#activity_stream").html('<img src="./static/img/ajax_loader.gif"  align="center">');
		 },
        success: function (msg){
                $('#deck_stream_button_div').hide();
                var data = eval('(' + msg + ')');
                if (data.activities.length){
                    data.current_id = deck_revision_id;
                    $('#activity_stream').empty();
                    $('#short_deck_stream').tmpl(data).appendTo('#activity_stream');
                    for (var i in data.activities){
                        if (data.activities[i].type){
                            var mod = (i-0+3)%2;                                
                            if (mod != 0){
                                data.activities[i].side = 'l';
                            }else{
                                data.activities[i].side = 'r';
                            }
                            data.activities[i].add_class = '';
                            data.activities[i].timestamp = prettyDate(data.activities[i].timestamp);
                            $('#activity_' + data.activities[i].type).tmpl(data.activities[i]).appendTo($('#short_activities'));
                        }
                    }
                    $('#activity_stream').show();
                }else{
                $("#activity_stream").html('<div id="short_deck_stream_btn"><a style="cursor:pointer" class="btn mini" id="stream_previous_month" href="./?url=main/deck_stream&deck=' + deck_revision_id + '">Show news...</a></div>');
            }                    
        }
    })
}
function showDeckStream(deck_id, month, portion){
    var filter = [];
    $('#filter-array').find('.filter').each(function(index){
        var value = $(this).attr('filter');
        filter[index] = value;
    })
    if (!portion){
           var cur_date = new Date(); 
            var cur_day = cur_date.getUTCDate();        
            portion = 4 - Math.floor(cur_day / 7);
    }
    if (month.length){
        $.ajax({
            url: './?url=ajax/getFullDeckStream&deck_revision_id=' + deck_id + '&filter=' + filter + '&month=' + month + '&portion=' + portion,
            success: function(msg){                   
                var data = eval('(' + msg + ')');
                var month_split = month.split('-');
                var prev_month = '';
                var prev_year = '';                      
                var cur_year = month_split['0'];
                var cur_month = month_split['1'];                        
                var prev_month_string = '';
                if (data.activities.length){
                    data.month = month;
                    data.current = getMonthName(parseInt(cur_month - 1)) + ', ' + cur_year;
                    if ($('#activity_stream').find('#' + month).length == 0){
                        $('#activity_stream').find('#stream_previous_month').remove();
                        $("#activity_stream_script").tmpl(data).appendTo($("#activity_stream"));
                    }
                    for (var i in data.activities){
                        if (data.activities[i].type){
                            var mod = (i-0+3)%2;                                
                            if (mod != 0){
                                data.activities[i].side = 'l';
                            }else{
                                data.activities[i].side = 'r';
                            }
                            data.activities[i].add_class = '';
                            data.activities[i].timestamp = prettyDate(data.activities[i].timestamp);
                            $('#activity_' + data.activities[i].type).tmpl(data.activities[i]).appendTo($('#' + month));
                        }
                    }
                    if (portion == 4){
                        portion = 1;
                        if (cur_month != '01'){
                        prev_month = cur_month - 1; 
                        prev_year = cur_year;
                        }else{
                            prev_month = 12;
                            prev_year = cur_year - 1;                                
                        }
                        if (prev_month < 10 ) {
                            prev_month = '0' + prev_month;
                        }                        
                        prev_month_string = prev_year + '-' + prev_month + '-01';
                    }else{
                        portion++;
                        prev_month_string = month;
                    }                    
                }else{
                    if (portion==4){
                        portion = 1;
                        if (cur_month != '01'){
                        prev_month = cur_month - 1; 
                        prev_year = cur_year;
                        }else{
                            prev_month = 12;
                            prev_year = cur_year - 1;                                
                        }
                        if (prev_month < 10 ) {
                            prev_month = '0' + prev_month;
                        }
                        prev_month_string = prev_year + '-' + prev_month + '-01'; 
                    }else{
                        portion++;
                        prev_month_string = month;
                    }                    
                    showDeckStream(deck_id, prev_month_string,portion);
                }                        
                $('.created_deck').each(function(){
                    if ($(this).attr('deck_id') == $('#main_deck_id').attr('deck_id')){
                        $('#stream_previous_month').remove();
                    }
                })
                if ($('#stream_previous_month').length){
                    $('.translated_deck_from').each(function(){
                        if ($(this).attr('deck_id') == $('#main_deck_id').attr('deck_id')){
                            $('#stream_previous_month').remove();
                        }
                    })
                }
                if ($('#stream_previous_month').length){
                    $('#stream_previous_month').attr('onclick','showDeckStream("' + deck_id + '","' + prev_month_string + '","' + portion + '");');
                }                
            }
        })
    }else{
        $.ajax({
            url: './?url=ajax/getFullDeckStream&deck_revision_id=' + deck_id + '&filter=' + filter + '&portion=' + portion,
            success: function(msg){ 
                var data = eval('(' + msg + ')');
                var cur_date = new Date();                   
                var new_month = '';                            
                var cur_month = cur_date.getMonth() + 1;
                if (cur_month < 10) {
                    cur_month = '0' + cur_month;
                }
                $('#activity_stream').empty();
                var new_year = '';
                var cur_year = cur_date.getUTCFullYear(); 
                var data_string = cur_year.toString() + '-' + cur_month.toString() + '-01';                        
                if (data.activities.length){
                    data.month = data_string;
                    data.current = getMonthName(parseInt(cur_month - 1)) + ', ' + cur_year;
                    if ($('#activity_stream').find('#' + data_string).length == 0){
                        
                        $("#activity_stream_script").tmpl(data).appendTo($("#activity_stream"));
                    }                    
                    for (var i in data.activities){
                        if (data.activities[i].type){                            
                            var mod = (i-0+3)%2;                                
                            if (mod != 0){
                                data.activities[i].side = 'l';
                            }else{
                                data.activities[i].side = 'r';
                            }
                            data.activities[i].add_class = '';
                            $('#activity_' + data.activities[i].type).tmpl(data.activities[i]).appendTo($('#' + data_string));
                            data.activities[i].timestamp = prettyDate(data.activities[i].timestamp);
                        }                                              
                    }
                    if (portion == 4){
                        portion = 1;
                        if (cur_month != '1'){
                        new_month = cur_month - 1;
                        cur_date.setMonth(new_month);                                
                        }else{
                            new_month = 12;                            
                            new_year = cur_year - 1;
                            cur_date.setYear(new_year);
                            cur_date.setMonth(new_month - 1);                                
                        }
                        var month = cur_date.getUTCMonth();
                        if (month < 10) {
                            month = '0' + month;
                        }
                        var year = cur_date.getUTCFullYear();
                        var prev_month_string = year + '-' + month + '-01';
                    }else{
                        portion++;
                        prev_month_string = data_string;
                    }                    
                }else{
                    if (portion == 4){
                        portion = 1;
                        cur_month = cur_date.getMonth() + 1;
                        if (cur_month < 10) {
                            cur_month = '0' + cur_month;
                        }
                        cur_year = cur_date.getUTCFullYear(); 
                        data_string = cur_year.toString() + '-' + cur_month.toString() + '-01';
                        if (cur_month != '1'){
                            new_month = cur_month - 1;
                            cur_date.setMonth(new_month);                                
                        }else{
                            new_month = 12;                            
                            new_year = cur_year - 1;
                            cur_date.setYear(new_year);
                            cur_date.setMonth(new_month - 1);                                
                        }
                        month = cur_date.getUTCMonth();
                        if (month < 10) {
                            month = '0' + month;
                        }
                        year = cur_date.getUTCFullYear();
                        prev_month_string = year + '-' + month + '-01'; 
                    }else{
                        prev_month_string = data_string;
                        portion++;
                    }
                    showDeckStream(deck_id,prev_month_string,portion);
                }                        
                $('.created_deck').each(function(){
                    if ($(this).attr('deck_id') == $('#main_deck_id').attr('deck_id')){
                        $('#stream_previous_month').remove();
                    }
                })
                if ($('#stream_previous_month').length){
                    $('.translated_deck_from').each(function(){
                        if ($(this).attr('deck_id') == $('#main_deck_id').attr('deck_id')){
                            $('#stream_previous_month').remove();
                        }
                    })
                }
                if ($('#stream_previous_month').length){
                    $('#stream_previous_month').attr('onclick','showDeckStream("' + deck_id + '","' + prev_month_string + '","' + portion + '");');
                }                     
            }
        })
    }

}
function showSlideStream(slide_id,month){
    var filter = [];
    $('#filter-array').find('.filter').each(function(index){
        var value = $(this).attr('filter');
        filter[index] = value;
    })
    if (month.length){
        $.ajax({
            url: './?url=ajax/getSlideStream&slide_revision_id=' + slide_id + '&filter=' + filter + '&month=' + month,
            success: function(msg){                   
                var data = eval('(' + msg + ')');
                var month_split = month.split('-');
                var prev_month = '';
                var prev_year = '';                      
                var cur_year = month_split['0'];
                var cur_month = month_split['1'];                        
                var prev_month_string = '';
                if (data.activities.length){
                    activity_stream_cache.push(data.activities);
                    data.month = month;
                    data.current = getMonthName(parseInt(cur_month - 1)) + ', ' + cur_year;
                    $('#activity_stream').find($('#stream_previous_month')).remove();
                    $("#activity_stream_script").tmpl(data).appendTo($("#activity_stream"));
                    for (var i in data.activities){
                        var mod = (i-0+3)%2;                                
                        if (mod != 0){
                            data.activities[i].side = 'l';
                        }else{
                            data.activities[i].side = 'r';
                        }
                        $('#activity_' + data.activities[i].type).tmpl(data.activities[i]).appendTo($('#' + month));
                    }
                    if (cur_month != '01'){
                        prev_month = cur_month - 1; 
                        prev_year = cur_year;
                    }else{
                        prev_month = 12;
                        prev_year = cur_year - 1;                                
                    }
                    if (prev_month < 10 ) {
                        prev_month = '0' + prev_month;
                    }                        
                    prev_month_string = prev_year + '-' + prev_month + '-01';
                }else{
                    if (cur_month != '01'){
                    prev_month = cur_month - 1; 
                    prev_year = cur_year;
                    }else{
                        prev_month = 12;
                        prev_year = cur_year - 1;                                
                    }
                    if (prev_month < 10 ) {
                        prev_month = '0' + prev_month;
                    }
                    prev_month_string = prev_year + '-' + prev_month + '-01'; 
                    showSlideStream(slide_id, prev_month_string);
                }                        
                if ($('#activity_stream').find('.created_slide').length == 0){
                    $('#stream_previous_month').attr('onclick','showSlideStream("' + slide_id + '","' + prev_month_string + '");');
                }else{
                    $('#stream_previous_month').remove();
                }
            }
        })
    }else{
        $.ajax({
            url: './?url=ajax/getSlideStream&slide_revision_id=' + slide_id + '&filter=' + filter,
            success: function(msg){                        
                var data = eval('(' + msg + ')');                        
                var cur_date = new Date();                   
                var new_month = '';                            
                var cur_month = cur_date.getMonth() + 1;
                if (cur_month < 10) {
                    cur_month = '0' + cur_month;
                }
                var new_year = '';
                var cur_year = cur_date.getUTCFullYear(); 
                var data_string = cur_year.toString() + '-' + cur_month.toString() + '-01';                        
                if (data.activities.length){
                    
                    activity_stream_cache.push(data.activities);
                    data.month = data_string;
                    data.current = getMonthName(parseInt(cur_month - 1)) + ', ' + cur_year;
                    $('#activity_stream').empty();
                    $("#activity_stream_script").tmpl(data).appendTo($("#activity_stream"));
                    for (var i in data.activities){                        
                        var mod = (i-0+3)%2;                                
                        if (mod != 0){
                            data.activities[i].side = 'l';
                        }else{
                            data.activities[i].side = 'r';
                        }
                        $('#activity_' + data.activities[i].type).tmpl(data.activities[i]).appendTo($('#' + data_string));
                    }                    
                    if (cur_month != '1'){
                        new_month = cur_month - 1;
                        cur_date.setMonth(new_month);                                
                    }else{
                        new_month = 12;                            
                        new_year = cur_year - 1;
                        cur_date.setYear(new_year);
                        cur_date.setMonth(new_month - 1);                                
                    }
                    var month = cur_date.getUTCMonth();
                    if (month < 10) {
                        month = '0' + month;
                    }
                    var year = cur_date.getUTCFullYear();
                    var prev_month_string = year + '-' + month + '-01';
                }else{
                    cur_month = cur_date.getMonth() + 1;
                    if (cur_month < 10) {
                        cur_month = '0' + cur_month;
                    }
                    cur_year = cur_date.getUTCFullYear(); 
                    data_string = cur_year.toString() + '-' + cur_month.toString() + '-01';
                    if (cur_month != '1'){
                        new_month = cur_month - 1;
                        cur_date.setMonth(new_month);                                
                    }else{
                        new_month = 12;                            
                        new_year = cur_year - 1;
                        cur_date.setYear(new_year);
                        cur_date.setMonth(new_month - 1);                                
                    }
                    month = cur_date.getUTCMonth();
                    if (month < 10) {
                        month = '0' + month;
                    }
                    year = cur_date.getUTCFullYear();
                    prev_month_string = year + '-' + month + '-01';
                    showSlideStream(slide_id,prev_month_string);
                }                        
                if ($('#activity_stream').find('.created_slide').length == 0){
                    
                    $('#stream_previous_month').attr('onclick','showSlideStream("' + slide_id + '","' + prev_month_string + '");');
                }else{
                    $('#stream_previous_month').remove();
                }                     
            }
        })
    }

}
function truncate_middle (fullStr, strLen, separator) {
    if (fullStr.length <= strLen) return fullStr;

    separator = separator || '...';

    var sepLen = separator.length,
        charsToShow = strLen - sepLen,
        frontChars = Math.ceil(charsToShow/2),
        backChars = Math.floor(charsToShow/2);

    return fullStr.substr(0, frontChars) + 
           separator + 
           fullStr.substr(fullStr.length - backChars);
}
function truncateurls(links) {
    for (var i = 0, cnt = links.length, tmpLink; i < cnt; i++) {
        tmpLink = links[i].innerHTML;
        links[i].innerHTML=truncate_middle(tmpLink,50,'...');   
    }	
}

function loadDeck(id) { 
	var user = getCurrenUserID(); // get the current user_id
	item_change=manual_cleanArray(item_change);
	if(item_change.length){
		var answer = confirm("You have some changes which are not saved yet! Do you want to save them?");
		if (answer) {
			save_changes();
		} else {
			resetGolbalVars();
			loadDeck(id)
		}
	}else{
		
		$("#deck-view").addClass('deckOverviewItem');
		//hide the editor
		hide_editor_toolbar();
		hide_save_toolbar();
		hide_fullscreen_button();
		$('#activity_stream').empty()
		//hide source code modal
		$('#modal_html_code').modal('hide');	
		//update share link
		if($('#modal_share_link').css('display')!='none')
			showShareLink('deck',id);			
		//show edit tab
		$("#editlink").css('display','');
		$("#viewlink")[0].children[0].textContent="View";
		// prevent duplicate rendering
		if ($("#slideview").html() != '') {
			$("#slideview").html('');
		}
                $('#slideview').css('min-height','none');
		$.ajax({
					url : 'ajax/showDeckPreviewProgressive/' + id+'/from/0',
					success : function(msg) {
						var data = eval("(" + msg + ")");
						data.current_user_id = user;
						data.sources=manual_uniquearr(data.sources);
						$("#deck_preview").tmpl(data).appendTo("#slideview");
						//show contributors info
						$('#additional-elements').show();
						$('#additional-elements').html($('.deck-contributors').html());
						$('#deck_follow_status').html($('.deck-follow').html());
                                                if (data.language.id){
                                                    $('#current_language').show();
                                                    $('#current_language_null').hide();
                                                    $('#current_language').attr('lang',data.language.id);
                                                    $('#current_language_name').empty().html("Language: <b>" + data.language.name + "</b>");
                                                }else{
                                                    $('#current_language').hide();
                                                    $('#current_language_null').show();
                                                }
                                                $('#get_all_translations_link').attr('onclick', 'getAllTranslations(' + id +')');                                               
						$('.slide-osource-item').linkify('', truncateurls);
						$('.deck-abstract').linkify();
                                                
						if(data.slides.length<data.size){
							//show the "show more..." button
							$("#slideview").append('<div id="show_more_slides"><center><a class="btn" onclick="loadDeckFrom('+id+','+data.slides.length+')">Show more...</a></center></div>');
						}
						// notify user if a new deck revision is available
						if (data.id != data.last_revision_id) {
							$('#historylink')
									.html(
											"History<img title='New revision is available!' src='static/img/exclamation.gif'>");
						}else{
                                                    if (data.translated_from_changed == true){
                                                        $('#historylink').append('<img src="/static/img/exclamation_b.gif" alt="The parent translation was changed!">');
                                                    }
                                                }
                                                
						$('#playSlide').attr('href','playSync/style/'+style+'/transition/'+transition+'/deck/'+id+'#');
						$("#downloadDeck").css("display", '');
						$("#downloadDeck").attr("href",
								'./?url=export/toHTML&deck_id=' + id);
						$("#printDeck").css("display", '');
						$("#printDeck").attr("href",
								'print/deck/' + id);
						selectTab('view');
						resetGolbalVars();
						//to prevent duplicate slides -- we have class slide for history items
						$("#itemhistory").html('');							
						$.deck('.slide');
						MathJax.Hub.Queue([ "Typeset", MathJax.Hub, 'slideview' ]);
						$.deck('showMenu');
						$.deck('iframes');
						overlay();
                                                //deck stream
                                                $('#deck_stream_button_div').show();
                                                $('#deck_stream_button').attr('deck_id',id);                                                
                                        }
				});
	}
        
}
//a call back function which selects a node or reloads the page based on the results from msg JSON format
function selectOrReload(msg,target_node){
	disable_save_button();
	resetGolbalVars();
	if (msg.root_changed) {
		if(target_node)
			if(msg.force_refresh){
				window.location = 'deck/' + msg.root_changed+ '_' + msg.slug_title + '#' + target_node + '-view';
				window.location.reload()
			}else{
				window.location = 'deck/' + msg.root_changed+ '_' + msg.slug_title + '#' + target_node + '-view';
			}
		else
			if(msg.force_refresh){
				window.location = 'deck/' + msg.root_changed+ '_' + msg.slug_title + '#' + msg.items[0].rev_id + '-view';
				window.location.reload()
			}else{
				window.location = 'deck/' + msg.root_changed+ '_' + msg.slug_title + '#' + msg.items[0].rev_id + '-view';
			}
	} else {
		if(target_node)
			$('#after_refresh_node').text(target_node);
		else
			$('#after_refresh_node').text(msg.items[0].rev_id);
		//preserve the nodes opened
		$.cookie('my_jstree_open', $.cookie("jstree_open"));
		var tree = $.jstree._reference("#tree");
		
		if(!msg.refresh_nodes)
			if(msg.items.length){
				tree.refresh('#'+getParentFromId(msg.items[0].rev_id));
			}else
				$("#tree").jstree('refresh');		
		else
			$.each(msg.refresh_nodes, function(i,n){
				//$.each($('.deck-'+n), function(ii,nn){
				    //only refresh the affected nodes
					tree.refresh('.deck-'+n);
				//})
			})
		//
	}	
}
/*------------------Edit slides/decks functions----------------------*/
function editDeck(id) {
	$('#additional-elements').hide();
	if ($("#response_msg").html() != '') {
		$("#response_msg").html('');
	}
	var user = getCurrenUserID(); // get the current user_id
	// prevent duplicate rendering
	if ($("#itemedit").html() != '') {
		$("#itemedit").html('');
	}
	if (!user) {
		var user_alert = eval("("
				+ '{"alert_type":"warning","msg":"Please login or register to edit decks!"}'
				+ ")");
		$("#user_alert").tmpl(user_alert).appendTo("#itemedit");
		return;
	}

	$.ajax({
		url : 'ajax/editDeck/' + id,
		success : function(msg) {
			var data = eval("(" + msg + ")");
			// check if it is owner of the deck
			if (user == data.owner.id) {
				data.owner = "yes";
			}
			if (user == data.initiator.id) {
				data.initiator = "yes";
			}			
			$.each(data.editors, function(i,v){
				if(user==v.id){
					data.editors="yes";
					return;
				}
			});
			$("#deck_edit").tmpl(data).appendTo("#itemedit");
                        $('#tags').tagsInput({width:'327px'});
                        
			resetGolbalVars();
		}
	});
}
function saveDeck(id) {
	var selected_id;
	if (!$("#tree").jstree("get_selected")[0]) {
		if($('.jstree-clicked')[0])
			selected_id = $('.jstree-clicked')[0].id;
	} else {
		selected_id = $("#tree").jstree("get_selected")[0].children[1].id;
	}
	if(!selected_id){
		var hash=window.location.hash;
		var parameters=hash.split('#')[1];
		var parts=getPropertiesFromHash(parameters);	
		selected_id=parts['nodeId'];
	}
	$.ajax({
				url : 'ajax/saveDeck/' + id +'/root/'+deck+'/node/'+selected_id+'/data/' + encodeURIComponent($('#editdeck').serialize()),
				success : function(msg) {
					msg = eval("(" + msg + ")");
					selectOrReload(msg,0);					
				}
			});
}
function newDeckRevision(id) {
	var selected_id;
	if (!$("#tree").jstree("get_selected")[0]) {
		selected_id = $('.jstree-clicked')[0].id;
	} else {
		selected_id = $("#tree").jstree("get_selected")[0].children[1].id;
	}
	$.ajax({
		url : './?url=ajax/newDeckRevision&id=' + id +'&root_deck='+deck+'&node_id='+selected_id+'&' + $('#editdeck').serialize(),
		success : function(msg) {
			msg = eval("(" + msg + ")");
			selectOrReload(msg,0);
		}
	});
}
//uses client-side tree traversing to increase the performance
//acts separately from Revision Model : duplicate
function confirmNewRevision(selected_id) {
	var res;
	var properties = getPropertiesFromId(selected_id);
	var deckId = (properties['deckId'] != 0) ? properties['deckId'] : deck;
        
                            $.ajax({
				async : false,
				url : './?url=ajax/checkCreatingNewDeckRev&deck=' + deckId,
				success : function(msg) {
					msg = eval(msg);
					if (!msg) {
						res = true; //no need to create new revision
					} else {
						var answer = confirm("This change will create a new revision for the selected deck(s). Are you sure you want to do it?");
						if (answer) {
							res = true;//no need to create new revision
						} else {
							res = false;// need to create new revision
						}
					}
				}
			});
	return res;
}
function edit_slide_content_ok(selected_id){
	$(".slide-body").attr('contentEditable',true);
	if(store_active_editor('slide_body_'+selected_id)){
		cleanExtraTags('slide_body_'+selected_id);
		clearMathJaxRendering('slide_title_'+selected_id);
		Aloha.jQuery($('#slide_body_'+selected_id)).aloha();
	    enableSVGEdit('slide_body_'+selected_id);
	    $('#slide_body_'+selected_id).focus();
	    $('#slide_body_'+selected_id).keydown('ctrl+s', execKeyCom('save'));
	}
}
function edit_slide_title_ok(selected_id){
	if(store_active_editor('slide_title_'+selected_id)){
		$(".slide-title").attr('contentEditable',true);
		clearMathJaxRendering('slide_title_'+selected_id);
		clearMathJaxRendering('slide_note_'+selected_id);
		cleanExtraTags('slide_body_'+selected_id);
		$('#slide_title_'+selected_id).focus();
		$('#slide_title_'+selected_id).keydown('ctrl+s', execKeyCom('save'));
	}	
}
function edit_slide_note_ok(selected_id){
	if(store_active_editor('slide_note_'+selected_id)){
		$(".slide-note").attr('contentEditable',true);
		cleanExtraTags('slide_body_'+selected_id);
		clearMathJaxRendering('slide_title_'+selected_id);
		clearMathJaxRendering('slide_note_'+selected_id);
		$('#slide_note_'+selected_id).focus();
		$('#slide_note_'+selected_id).keydown('ctrl+s', execKeyCom('save'));	
	}	
}
function new_revision_notification(selected_id,mode){
	var properties = getPropertiesFromId(selected_id);
	var deckId = (properties['deckId'] != 0) ? properties['deckId'] : deck;
	$.ajax({
				async : false,
				url : './?url=ajax/checkCreatingNewDeckRev&deck=' + deckId,
				success : function(msg) {
					msg = eval(msg);
					if (msg) {
						//show notification modal
						hide_editor_toolbar();
						hide_save_toolbar();
						show_notification_modal(selected_id,mode);
					}else{
						switch (mode){// when no new revision is required
							case 'edit_slide_content':
								edit_slide_content_ok(selected_id);
								break;
							case 'edit_slide_title':
								edit_slide_title_ok(selected_id);
								break;
							case 'edit_slide_note':
								edit_slide_note_ok(selected_id);
								break;								
						}
					}
				}
			});	
}
function show_notification_modal(selected_id,mode){
	 $("#modal_dialog1").draggable({
		    handle: ".modal-header"
		});
	var properties = getPropertiesFromId(selected_id);
	switch (mode){
	case 'edit_slide_content':
		 $( "#modal_dialog1" ).modal('show');
		 $( "#dialog_minor_edit").click(function(){
			 $.cookie("minor_slide_body_"+selected_id, 1);
			 //contains the list of minor cookies
			 minor_changes.push("minor_slide_body_"+selected_id);
			 edit_slide_content_ok(selected_id);
			 $( "#modal_dialog1" ).modal('hide');
		 });
		 $( "#dialog_apply_editorship").unbind('click').click(function(e){
			 e.stopPropagation();
			 var pm=prompt("Enter some comments for the owner: ","");
				$.ajax({
					url : './?url=ajax/applyForEditorship&deck_id='+properties['deckId']+'&pm='+pm,
					success : function(msg) {
						$( "#dialog_editorship_minor_edit").unbind('click').click(function(){
							 $.cookie("minor_slide_body_"+selected_id, 1);
							 //contains the list of minor cookies
							 minor_changes.push("minor_slide_body_"+selected_id);
							 edit_slide_content_ok(selected_id);	
							 $( "#modal_dialog_editorship" ).modal('hide');
						});
						$( "#modal_dialog_editorship" ).modal('show');
					}
				});			 
			 $( "#modal_dialog1" ).modal('hide');
		 });
		 $( "#dialog_new_revision").click(function(){
			 edit_slide_content_ok(selected_id);				 
			 $( "#modal_dialog1" ).modal('hide');
		 });
		 $( "#dialog_cancel").click(function(){
			 	$(".slide-body").attr('contentEditable',false);
				hide_editor_toolbar();
				hide_save_toolbar();				 
			 $( "#modal_dialog1" ).modal('hide');
		 });		 
		 break;
	case 'edit_slide_title':
		 $( "#modal_dialog1" ).modal('show');
		 $( "#dialog_minor_edit").click(function(){
			 $.cookie("minor_slide_title_"+selected_id, 1);
			 minor_changes.push("minor_slide_title_"+selected_id);
			 edit_slide_title_ok(selected_id);	
			 $( "#modal_dialog1" ).modal('hide');
		 });
		 $( "#dialog_apply_editorship").unbind('click').click(function(e){
			 e.stopPropagation();
			 //var user_id= getCurrenUserID(); // get the current user_id
			 var pm=prompt("Enter some comments for the owner: ","");
				$.ajax({
					url : './?url=ajax/applyForEditorship&deck_id='+properties['deckId']+'&pm='+pm,
					success : function(msg) {
						$( "#dialog_editorship_minor_edit").unbind('click').click(function(){
							 $.cookie("minor_slide_title_"+selected_id, 1);
							 //contains the list of minor cookies
							 minor_changes.push("minor_slide_title_"+selected_id);
							 edit_slide_title_ok(selected_id);	
							 $( "#modal_dialog_editorship" ).modal('hide');
						});
						$( "#modal_dialog_editorship" ).modal('show');
					}
				});			 
			 $( "#modal_dialog1" ).modal('hide');
		 });
		 $( "#dialog_new_revision").click(function(){
			 edit_slide_title_ok(selected_id);				 
			 $( "#modal_dialog1" ).modal('hide');
		 });
		 $( "#dialog_cancel").click(function(){
				$(".slide-title").attr('contentEditable',false);
				hide_editor_toolbar();
				hide_save_toolbar();				 
			 $( "#modal_dialog1" ).modal('hide');
		 });		
		break;
	case 'edit_slide_note':
		 $( "#modal_dialog1" ).modal('show');
		 $( "#dialog_minor_edit").click(function(){
			 $.cookie("minor_slide_note_"+selected_id, 1);
			 minor_changes.push("minor_slide_note_"+selected_id);
			 edit_slide_note_ok(selected_id);
			 $( "#modal_dialog1" ).modal('hide');
		 });
		 $( "#dialog_apply_editorship").unbind('click').click(function(e){
			 e.stopPropagation();
			 //var user_id= getCurrenUserID(); // get the current user_id
			 var pm=prompt("Enter some comments for the owner: ","");
				$.ajax({
					url : './?url=ajax/applyForEditorship&deck_id='+properties['deckId']+'&pm='+pm,
					success : function(msg) {
						$( "#dialog_editorship_minor_edit").unbind('click').click(function(){
							 $.cookie("minor_slide_note_"+selected_id, 1);
							 //contains the list of minor cookies
							 minor_changes.push("minor_slide_note_"+selected_id);
							 edit_slide_note_ok(selected_id);	
							 $( "#modal_dialog_editorship" ).modal('hide');
						});
						$( "#modal_dialog_editorship" ).modal('show');
					}
				});			 
			 $( "#modal_dialog1" ).modal('hide');
		 });
		 $( "#dialog_new_revision").click(function(){
			 edit_slide_note_ok(selected_id);				 
			 $( "#modal_dialog1" ).modal('hide');
		 });
		 $( "#dialog_cancel").click(function(){
				$(".slide-note").attr('contentEditable',false);
				hide_editor_toolbar();
				hide_save_toolbar();				 
			 $( "#modal_dialog1" ).modal('hide');
		 });		
		break;		
	case 'new_slide':
	case 'new_deck':
	case 'existing_slide':
	case 'existing_deck':
	case 'use_slide':
	case 'use_deck':
	case 'move_slide':
	case 'move_deck':
	case 'rename_slide':
	case 'rename_deck':		
	break;
	}
}
function enableSVGEdit(id){
	var i=0;
	$.each($('#'+id).find('svg'), function(v){
		var position = $(this).position();
		$(this).attr('id','svg'+i);
		//var top=parseInt($(this).height())/2 + position.top;
		// left=parseInt($(this).width())/2 + position.left;
		//<img src="static/img/edit.png" style="cursor:pointer;position:absolute;top: '+top+'px;left: '+left+'px;"/>
		$(this).mouseover(function(e) {								
			e.stopPropagation();
			$('#svgHandler').remove();
			$(this).before('<span id="svgHandler" style="cursor:pointer;display:block;top: '+position.top+'px;left: '+position.left+'px;">Click to edit</span>');
			//$(this).css('cursor','pointer');
		});
		$(this).mouseout(function() {								
			$('#svgHandler').remove();
			//$(this).css('cursor','');
		});
		var input=$(this).clone().wrap('<div></div>').parent().html();
		$(this).click(function(e) {	
			e.stopPropagation();
			$("#svg_code").val(input);
			$("#svg_codeid").val($(this).attr('id'));
			$('#svg_form_form').submit();
			//var popup_window=window.open("libraries/frontend/svg-edit/svg-editor.php?id="+$(this).attr('id')+"&input="+encodeURIComponent(input));
		});
		i++;
	});
}
function enableWYSISWYG(obj){
	$('.slide').removeHighlight();
	//check if user has logged in by checking the title editable property
	var editable=$($('.slide-title')[0]).attr('contentEditable');
	if(editable){
		show_save_toolbar();
		if($.trim($(obj).text())=='« Click to add text »'){
			$(obj).html('&nbsp;');
		}	
		//waning if new revision for deck will be created
		//prevent showing the notifivations again in title and note
		if(!get_active_slide_id() || get_active_slide_id()!=get_node_id_from_parts(obj.id)){
			new_revision_notification(get_node_id_from_parts(obj.id),'edit_slide_content');
		}else{
			edit_slide_content_ok(get_node_id_from_parts(obj.id));
		}
	}
}
//cleans deck.js MathJax and Codemirror tags in source code
function cleanExtraTags(id){
	clearMathJaxRendering(id);
	//disable highlighted code
	$('#'+id+' .CodeMirror').remove();
	$('#'+id+' .deck-codemirror-result').remove();
	$.each($('#'+id+' .code'),function(index,value){
		$(value).css('display','');
		$(value).removeClass('passive-code');
		$(value).addClass('passive-code');
	});	
	//re-enable iframes
	$.each($('#'+id+' iframe'),function(index,value){
		$(value).attr('src',$(value)[0]._src);
	});		
	$('#'+id+' .slide').removeClass('slide');
	$('#'+id+' .deck-current').removeClass('deck-current');
	$('#'+id+' .deck-previous').removeClass('deck-previous');
	$('#'+id+' .deck-next').removeClass('deck-next');
	$('#'+id+' .deck-after').removeClass('deck-after');
	$('#'+id+' .deck-before').removeClass('deck-before');
}
function clearMathJaxRendering(id){
	$.each($('#'+id+' .MathJax_Display'),function(index,value){
		//console.log(value.nextSibling.text);
		//console.log(value.previousSibling.outerHTML)
		value.previousSibling.outerHTML='';
		//console.log(value.nextSibling.outerHTML);
		$(value).before('\\\['+value.nextSibling.text+'\\\]');
		value.nextSibling.outerHTML='';
		$(value).remove();
	});
	$.each($('#'+id+' .MathJax_Preview'),function(index,value){
		$(value).before('\\\('+$(value).next().next().text()+'\\\)');
		$(value).next().next().remove();
		$(value).next().remove();
		$(value).remove();
	});	
}
function execKeyCom(cmd) {
    return function(e) {
      e.preventDefault();
      execCom(cmd);
    };
  }
function execCom(cmd){
	switch (cmd){
		case 'save':save_changes();resetGolbalVars();
		break;
	}
}
//stores the id of current active editor in a div and return true
//if the editor is already activated returns false
function store_active_editor(id){
	if($.trim($('#active_editor_id').text())=='' || $.trim($('#active_editor_id').text())!=id){
		$('#active_editor_id').text(id);
		return true;
	}else{
		return false;
	}	
}
//gets the whole node id: applicable for one slide not title,body or note
function get_active_slide_id(){
	var whole_id=$('#active_editor_id').text().trim();
	if(!whole_id)
		return 0;
	else{
		var selected_id=get_node_id_from_parts(whole_id);
		return selected_id;
	}
}
function showSlideOriginalSource(obj,id){
	$('#'+id+' .slide-description').show();
	$('#'+id+' .slide-original-source').html('Source Name');
	$('#'+id+' .slide-description .btn').show();
	$(obj).hide();
}
function cancelOriginalSource(id){
	$('#'+id+' .slide-description .btn').hide();
	if($('#'+id+' .slide-original-source').html().trim()=='')
		$('#'+id+' .slide-original-source').text('Source Name');
}
function showDescBtns(id){
	$('#'+id+' .slide-description .btn').show();
	$('#'+id+' .slide-original-source').addClass('slide-desc-edit-mode');
	//if($('#'+id+' .slide-original-source').html().trim()=='Source Name')
		//$('#'+id+' .slide-original-source').html(' ');
}
function saveSlideOriginalSource(id,slide_id){
	if(($('#'+id+' .slide-original-source').text().trim()=='Source Name')|| ($('#'+id+' .slide-original-source').text().trim()=='')){
		alert('please enter a source name!');
		$('#'+id+' .slide-original-source').focus();
	}else{
		$.ajax({
			url : './?url=ajax/updateSlideDescription&slide_id=' + slide_id+'&desc='+encodeURIComponent($('#'+id+' .slide-original-source').text().trim()),
			success : function(msg) {
				//to hide buttons
				cancelOriginalSource(id);
			}
		});
	}
}
// following two functions do some array tasks manually to remove duplicates and clean arrays
function manual_uniquearr(array){
    return $.grep(array,function(el,index){
        return index == $.inArray(el,array);
    });
}
function manual_cleanArray(actual){
	  var newArray = new Array();
	  for(var i = 0; i<actual.length; i++){
	      if (actual[i]){
	        newArray.push(actual[i]);
	    }
	  }
	  return newArray;
}
//------------------------
function save_changes(){
	//only work when save button is active
	 if($("#save_changes_button").hasClass('save_inactive')|| !item_change.length){
	  return;
	 }
	//todo:confirm by user if new deck revision is required: in case multiple slide is edited needs more actions
	//destroy resizable and draggable divs
	$( ".slide-body img" ).resizable('destroy').parent('.ui-wrapper').draggable('destroy');
	//remove duplicate changes
	//console.log(item_change);
	item_change = jQuery.unique(item_change);
	//jQuery.unique does not work properly so we do it manually
	//console.log(item_change);
	item_change =manual_uniquearr(item_change);
	item_change =manual_cleanArray(item_change);
	//console.log(item_change);
	//remove partial change logs
	//for use in future
	var item_change2=new Array();
	var is_minor_rev=0;
	$.each(item_change, function(index, value) {
		if($.cookie("minor_"+value)){
			is_minor_rev=1;
			$.cookie("minor_"+value, null);
		}
		var tmp=value;
		//remove possible redundant ids like proper_tmp_el!
		if(tmp!='proper_tmp_el'){
			tmp=value.split('_');
			tmp=tmp[2];
			item_change2.push(tmp);
		}
	});	
	var pure_changes=new Array();
	pure_changes=jQuery.unique(item_change2);
	//console.log(pure_changes);
	pure_changes =manual_uniquearr(pure_changes);
	pure_changes =manual_cleanArray(pure_changes);
	item_change=pure_changes;
	//console.log(pure_changes);	
	//prepare input for Ajax request
	var input_data=new Array();
	var input_data= new Object;
	input_data.items = new Array;
	$.each(pure_changes, function(index, value) {
		//first cleanup redundant tags
		cleanExtraTags('slide_body_'+value);
		clearMathJaxRendering('slide_title_'+value);
		clearMathJaxRendering('slide_note_'+value);
		var title=jQuery.trim($('#slide_title_'+value).text());
		if(title=='« Click to add title »' || title=='' || title=='Untitled'){
			title='';
		}
		var body=jQuery.trim($('#slide_body_'+value).html());
		if((body=='« Click to add text »')|| (body=='<p>« Click to add text »</p>')){
			body='';
		}
		var note=jQuery.trim($('#slide_note_'+value).text());
		if((note=='« Click to add note »')|| (note=='<p>« Click to add note »</p>')){
			note='';
		}
		input_data.items[index] = new Object;
		input_data.items[index].id = value;
		input_data.items[index].title = title;
		input_data.items[index].body = body;
		input_data.items[index].note =note;

	});
	//console.log(JSON.stringify(input_data));
	var json_input_data=encodeURIComponent(JSON.stringify(input_data));
	if(is_minor_rev){
		$.ajax({
			type : "POST",
			url : "./?url=ajax/saveAsMinorChanges",
			data : 'query='+json_input_data,
			contentType: "application/x-www-form-urlencoded",
			dataType: "json",
			success : function(msg) {
				disable_save_button();
				resetGolbalVars();	
				 $("#modal_dialog_minoredit").draggable({
					    handle: ".modal-header"
					});
				$('#modal_dialog_minoredit').modal('show');
			}
		});
	}else{
		//console.log(json_input_data);
		$.ajax({
			type : "POST",
			url : "ajax/saveSlideChanges",
			data : 'root_deck='+deck+'&query='+json_input_data,
			contentType: "application/x-www-form-urlencoded",
			dataType: "json",
			success : function(msg) {
				msg=eval(msg);
				//for single change select the corresponding node
				if(pure_changes.length==1){
					var selected_id=item_change[0];
					var p=getPropertiesFromId(selected_id);
					var target_id='tree-'+msg.items[0].target_deck_id+'-slide-'+msg.items[0].rev_id+'-'+p['pos'];
					selectOrReload(msg,target_id);
				}else{
					//for multiple changes select the container deck
					selectOrReload(msg,'tree-0-deck-'+deck+'-1');			
				}
			}
			//,error: function(xhr, txt, err){ alert("Error in saving content!"); },
		});
	}
}
//revert slide content to original
function revert_slide_changes(){
	var p = getPropertiesFromId($('.deck-current').attr('id'));
	$.ajax({
		url : './?url=ajax/getSlideContent&id=' +p['itemId'] ,
		success : function(msg) {
			var data = eval("(" + msg + ")");
			$('.deck-current').find('.slide-body').html(data.body);
		    item_change = jQuery.unique(item_change);
		    item_change=manual_cleanArray(item_change);			
			//console.log(item_change);
			var item_change2=new Array();
			$.each(item_change, function(index, value) {
				var tmp=value;
				//remove possible redundant ids like proper_tmp_el!
				if(tmp!=$('.deck-current').find('.slide-body').attr('id')){
					item_change2.push(tmp);
				}
			});	
			item_change=item_change2;
			//console.log(item_change);
			fill_source_code(0);
			if(!item_change.length)
				disable_save_button();
		}
	});
}
function removeBRs(input){
	var r=input.replace(/<br>/gi,''); 
	r=r.replace(/<br[0-9a-zA-Z]+>/gi,'');
	//r = r.replace(/\r\n\r\n/g, '');
	return r;
}
//Unescape HTML entities
function htmlDecode(input){
	  var e = document.createElement('div');
	  e.innerHTML = input;
	  return e.childNodes.length === 0 ? "" : e.childNodes[0].nodeValue;
}
//updates slide body based on the input html code 
function apply_source_code(){
	//var content=$('#html_source_code').val();
	var content=editor1.getValue();
	//strip the script tags
	content=content.replace(/<script.*?>.*?<\/.*?script>/gi, "");
	content=$.trim(content);
	//do not allow inline scripts to get executed
	//content=content.replace('(?<=<.*)javascript.*:[^"]*', "");
	var old_content=$('.deck-current').find('.slide-body').html().trim();
    if(old_content!=content ){
    	$('.deck-current').find('.slide-body').html(content);
    	item_change.push($('.deck-current').find('.slide-body').attr('id'));
    	enable_save_button();
    }	
}
function update_source_code(){
	apply_source_code();
    $('#modal_html_code').modal('hide');
}
function fill_source_code(selector){
	if(typeof editor1 !== 'undefined'){
		$('.CodeMirror').remove();
	}
	if(selector)
		$('#html_source_code').val($('#slide_body_'+selector+'').html().trim());
	else
		$('#html_source_code').val($('.deck-current').find('.slide-body').html().trim());
	 $("#modal_html_code").draggable({
		    handle: ".modal-header"
		});
	 $('#modal_html_code').modal('show');
     apply_codemirror_source_code();
}
function apply_codemirror_source_code(){
	  var te_html = document.getElementById("html_source_code");
		 
	  window.editor1 = CodeMirror.fromTextArea(te_html, {
		    mode: "text/html",
		    lineNumbers: false,
		    lineWrapping: true,
			readOnly: false,
		    onKeyEvent : function (editor, event) {
		    	apply_source_code();
		    },
	  		onFocus:     function (editor, event) {
	  			//just to consider changes like bold, italic which ar enot recognized
		  		//editor.setValue($('.deck-current').find('.slide-body').html().trim());
	  			//editor.refresh();
		    }
		  })  
	  editor1.refresh(); 
	  var totalLines = editor1.lineCount();
	  var totalChars = editor1.getValue().length;
	  var firstLineNumber = editor1.getOption('firstLineNumber');
	  editor1.autoFormatRange({line:0, ch:0}, {line:totalLines, ch:totalChars});
	  //editor1.autoFormatRange({line:0, ch:0}, {line:0, ch:1000000});
}
function insert_math_code(){
	var content=$('#math_source_code').val();
	//strip the script tags
	content=content.replace(/<script.*?>.*?<\/.*?script>/gi, "");
	content=$.trim(content);
	$('#modal_math_code').modal('hide');
    $('#'+$('#active_editor_id').text()).append(content);
    //applyScaling($('#active_editor_id').text());
}
function insert_code_snippet(){
	var content=$('#source_code_snippet').val();
	var coding_mode=$('#coding_mode').val();
	//strip the script tags
	content=$.trim(content);
	//escape HTML entities
	content=$('<div/>').text(content).html();
	$('#modal_code_snippet').modal('hide');
    $('#'+$('#active_editor_id').text()).append("<div class='code passive-code' mode='"+coding_mode+"'>"+content+"</div>");
}
function show_save_toolbar(){
    if(!$("#save_changes_button").hasClass('save_active') ){
    	disable_save_button();
    }
	$('#saveToolbar').show();
}
function hide_save_toolbar(){
	disable_save_button();
	$('#saveToolbar').hide();
}
function hide_fullscreen_button(){
	$('#fullscreen_button').css('display','none');
}
function show_fullscreen_button(){
	$('#fullscreen_button').css('display','');
}
function hide_editor_toolbar(){
	$('.editor-bar').hide();
	$('.aloha-floatingmenu').css('visibility', 'hidden');
	$('.aloha-shadow').css('display', 'none');
	$('.aloha-sidebar-right').css('display', 'none');
}
function show_deck_brand(){
	$('.deck-brand-bar').show();
}
function hide_deck_brand(){
	$('.deck-brand-bar').hide();
}
function show_editor_toolbar(){
	$('.editor-bar').show();
}
function enable_save_button(){
    if($("#save_changes_button").hasClass('save_inactive') ){
    	$("#save_changes_button").removeClass('save_inactive');
    }
	$("#save_changes_button").addClass('save_active');
}
function disable_save_button(){
    if($("#save_changes_button").hasClass('save_active') ){
    	$("#save_changes_button").removeClass('save_active');
    }
	$("#save_changes_button").addClass('save_inactive');
}
function init_img_manager() {
	$("#img_manager_dummy").fancybox({ 
		'href': "./?url=img/imgmanager",
		'title': "Your image gallery",
		'type': 'iframe',
		'width': 720, 
		'height': 650
	}).trigger('click');
}
function previewMathCode(){
	$('#math_source_preview').html($('#math_source_code').val());
	MathJax.Hub.Queue(["Typeset",MathJax.Hub,'math_source_preview']);
}
/*------------------Slide/Deck Discussion functions----------------------*/
function showDiscussion(type, id) {
	if (!reloadTabContent('Discussion',type+'-'+id,'discuss'))
		return;	
	/*
	if ($("#itemdiscussion").html() != '') {
		$("#itemdiscussion").html('');
	}
	*/
	$.ajax({
		url : './?url=ajax/showDiscussion&item=' + type + '&id=' + id,
		success : function(msg) {
			var data = eval("(" + msg + ")");
			data.current_rev=id;
			$("#item_discussion").tmpl(data).appendTo("#itemdiscussion");
			$('#itemdiscussion').linkify();
			//resetGolbalVars();
			//hide contributors from right side
			$('#additional-elements').hide();
		}
	});
}
function show_subdecks_comments(deckid){
	$('#subdeck_comments').html('');
	$.ajax({
		url : './?url=ajax/getSubDeckComments&id=' + deckid,
		success : function(msg) {
			var data = eval("(" + msg + ")");
			data.current_rev=deckid;
			$("#subdeckComments").tmpl(data).appendTo("#subdeck_comments");
			$('#subdeck_comments').linkify();
			if(!data.comments.length)
				$('#subdeck_comments').html('<br/>There is no comment on sub deck/slides.');
		}	
	});
}
function nl2br (str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}
function addComment(after, type, id) {
	var user = getCurrenUserID(); // get the current user_id
	if (!user) {
		var user_alert = eval("("
				+ '{"alert_type":"warning","msg":"Please login or register to add comments!"}'
				+ ")");
		$("#itemdiscussion").html('');
		$("#user_alert").tmpl(user_alert).appendTo("#itemdiscussion");
		return;
	}
	after
			.replaceWith('<div class="form-stacked" id="discussion-new-comment">'
					+ '<div class="clearfix">'
					+ '<label for="title">Title</label>'
					+ '<div class="input"><input class="span6" type="text" name="title" id="title" value="'+(type=='comment'?'Re:'+$('#com_title_'+id).text().trim():'')+'" /></div>'
					+ '</div>'
					+ '<div class="clearfix">'
					+ '<label for="text">Text</label>'
					+ '<div class="input"><textarea name="text" id="text" class="span8"></textarea></div>'
					+ '</div>'
					+ '<div class="actions">'
					+ '<button class="btn primary" onclick="saveComment($(this).parents(\'#discussion-new-comment\'),\''
					+ type
					+ '\','
					+ id
					+ ')">Save</button>'
					+ '</div>'
					+ '</div>');
}
function saveComment(node, type, id) {
	$.ajax({
		url : './?url=ajax/saveComment&type=' + type + '&id=' + id + '&title='
				+ encodeURIComponent(node.find('[name="title"]').val())
				+ '&text='
				+ encodeURIComponent(node.find('[name="text"]').val()),
		success : function(msg) {
			var data = eval("(" + msg + ")");
			node.html($("#comment_template").tmpl(data));
			node.linkify();
		}
	});
}
/*------------------Slide/Deck History functions----------------------*/
function showSlideRevisions(slide) {
	if (!reloadTabContent('History',slide,'hiss'))
		return;	
	/*
	if ($("#itemhistory").html() != '') {
		$("#itemhistory").html('');
	}
	*/
	var user = getCurrenUserID(); // get the current user_id
	$.ajax({
		url : './?url=ajax/showSlideRevisions&id=' + slide,
		success : function(msg) {
			var data = eval("(" + msg + ")");
			data.current_user = user;
                        
                        $("#slide_history").tmpl(data).appendTo("#itemhistory");
			//resetGolbalVars();
			// $.deck('.slide');
			MathJax.Hub.Queue([ "Typeset", MathJax.Hub, 'itemhistory' ]);
			// $.deck('showMenu');
			overlaySelected('itemhistory');
			//overlay();
			$('#additional-elements').hide();
		}
	});
}
function showDeckRevisions(deck) {
	if (!reloadTabContent('History',deck,'hisd'))
		return;	
	/*
	if ($("#itemhistory").html() != '') {
		$("#itemhistory").html('');
	}
	*/
	var user = getCurrenUserID(); // get the current user_id        
	$.ajax({
		url : './?url=ajax/showDeckRevisions&id=' + deck,
		success : function(msg) {
			var data = eval("(" + msg + ")");
			data.current_user = user;
			$("#deck_history").tmpl(data).appendTo("#itemhistory");
			//resetGolbalVars();
			$('#additional-elements').hide();
		}
	});
}
function toggleChangelog(deckid,index){
	if($('#changelog_'+deckid+'_'+index).is(":visible") ){
		$('#changelog_'+deckid+'_'+index).hide();
	}else{
		$.ajax({
			url : './?url=compare/reportDeckChanges&deck=' + deckid,
			success : function(msg) {
				$('#changelog_'+deckid+'_'+index).html(msg);
				$('#changelog_'+deckid+'_'+index).show();
			}
		});
	}
}
/*------------------Slide/Deck Usage functions----------------------*/
function showSlideUsage(slide) {
	if (!reloadTabContent('Usage',slide,'uss'))
		return;	
	/*
	if ($("#itemusage").html() != '') {
		$("#itemusage").html('');
	}
	*/
	$.ajax({
		url : './?url=ajax/showSlideUsage&id=' + slide,
		success : function(msg) {
			var data = eval("(" + msg + ")");
			$("#item_usage").tmpl(data).appendTo("#itemusage");
			//resetGolbalVars();
			$('#additional-elements').hide();
		}
	});
}
function showDeckUsage(id) {
	if (!reloadTabContent('Usage',id,'usd'))
		return;	
	/*
	if ($("#itemusage").html() != '') {
		$("#itemusage").html('');
	}
	*/
	$.ajax({
		url : './?url=ajax/showDeckUsage&id=' + id,
		success : function(msg) {
			var data = eval("(" + msg + ")");
			$("#item_usage").tmpl(data).appendTo("#itemusage");
			//resetGolbalVars();
			$('#additional-elements').hide();
		}
	});
}
function replaceItem(new_item_id,type){
	var selected_id;
	if (!$("#tree").jstree("get_selected")[0]) {
		selected_id = $('.jstree-clicked')[0].id;
	} else {
		selected_id = $("#tree").jstree("get_selected")[0].children[1].id;
	}
	var p = getPropertiesFromId(selected_id);
	var show_confirm=1;
	if(p['itemId']!=deck){
		show_confirm=confirmNewRevision(selected_id);
	}
	if (show_confirm) {
                
		$.ajax({
                        url : './?url=ajax/replaceItem&root_deck='
                                        + deck + '&node_id=' + selected_id
                                        + '&new_item_id=' + new_item_id + '&type=' + type,
                        success : function(msg) {
                                if (msg!=-1){
                                    msg = eval("(" + msg + ")");
                                    selectOrReload(msg,0);                                                    
                                }else{
                                    alert("This change can't be done because it leads to endless loop");
                                }
                        }
                });
	}	
}
function replaceSlideWith(slide_new) {
	var selected_id;
	if (!$("#tree").jstree("get_selected")[0]) {
		selected_id = $('.jstree-clicked')[0].id;
	} else {
		selected_id = $("#tree").jstree("get_selected")[0].children[1].id;
	}
	if (confirmNewRevision(selected_id)) {
		var p = getPropertiesFromId(selected_id);
		var node_arr = new Array();
		node_arr = getNestedDecksList(selected_id);
		$
				.ajax({
					url : './?url=ajax/replaceSlideAtPosition&containerdeck='
							+ p['deckId'] + '&newslide=' + slide_new
							+ '&position=' + p['pos'] + '&id=' + p['itemId']
							+ "&deck_arr=" + node_arr,
					success : function(msg) {
						msg = eval("(" + msg + ")");
						if (msg.changeRoot != 0) {
							window.location = 'deck/'
									+ msg.changeRoot + '_' + msg.slug_title + '#' + 'tree-'
									+ msg.deckId + '-slide-' + slide_new + '-'
									+ p['pos'] + '-view';
						} else {
							$('#after_refresh_node').text('tree-' + msg.deckId + '-slide-'+ slide_new + '-' + p['pos']);
							$("#tree").jstree("refresh");
							//selectNode('tree-' + msg.deckId + '-slide-'+ slide_new + '-' + p['pos']);
						}
						resetGolbalVars();
					}
				});
	}
}
function replaceDeckWith(deck_new) {
	var selected_id;
	if (!$("#tree").jstree("get_selected")[0]) {
		selected_id = $('.jstree-clicked')[0].id;
	} else {
		selected_id = $("#tree").jstree("get_selected")[0].children[1].id;
	}
	var p = getPropertiesFromId(selected_id);	
	if (p['deckId'] == 0) {
		window.location = 'deck/' + deck_new;
	} else {
		if (confirmNewRevision(selected_id)) {
			var node_arr = new Array();
			node_arr = getNestedDecksList(selected_id);

			$.ajax({
				url : './?url=ajax/replaceDeckAtPosition&containerdeck='
						+ p['deckId'] + '&newdeck=' + deck_new + '&position='
						+ p['pos'] + '&id=' + p['itemId'] + "&deck_arr="
						+ node_arr,
				success : function(msg) {
					msg = eval("(" + msg + ")");
					if (msg.changeRoot != 0) {
						window.location = 'deck/'
								+ msg.changeRoot + '#' + 'tree-' + msg.deckId
								+ '-deck-' + deck_new + '-' + p['pos']
								+ '-view';
					} else {
						$('#after_refresh_node').text('tree-' + msg.deckId + '-deck-' + deck_new+ '-' + p['pos']);
						$("#tree").jstree("refresh");
						//selectNode('tree-' + msg.deckId + '-deck-' + deck_new+ '-' + p['pos']);
					}
					resetGolbalVars();
				}
			});
		}
	}
}
/*------------------Context menu functions----------------------*/
// removes the context menu if user is not logged in
function disableContextMenu() {
	var user = getCurrenUserID(); // get the current user_id
	if (!user) {
		$("#vakata-contextmenu").remove();
		return;
	}
}
function deleteItem(selected_id) {
	var properties = getPropertiesFromId(selected_id);
	$.ajax({
		url : "./?url=ajax/deleteItemFromPosition&node_id="
				+ selected_id + "&root_deck=" + deck+ "&parent_deck="+getParentFromId(selected_id),
		success : function(msg) {
			msg = eval("(" + msg + ")");
			selectOrReload(msg,0);
		}
	});
}
function appendItem(type, selected_id, existing) {
	if (existing == 0)
		appendNewItem(type, selected_id);
	else
		appendExistingItem(type, selected_id);
}
function duplicateItem(type, selected_id) {
    var selected_properties = getPropertiesFromId(selected_id);
    // create a new slide/deck and return the id
    $.ajax({
        async : false,
        url : "./?url=ajax/insertDuplicateItemToDeck&root_deck=" + deck + "&node_id="
            + selected_id+ "&type="+type+ "&item_id="+selected_properties['itemId'],
        success : function(msg) { 
            msg = eval("(" + msg + ")");
            selectOrReload(msg,0);

        }
    });
}
function appendNewItem(type, selected_id) {
	var selected_properties = getPropertiesFromId(selected_id);
	// create a new slide/deck and return the id
	$.ajax({
		async : false,
		url : "./?url=ajax/insertNewItemToDeck&root_deck=" + deck + "&node_id="
				+ selected_id+ "&type="+type,
		success : function(msg) {
			msg = eval("(" + msg + ")");
			selectOrReload(msg,0);
		}
	});
}
function appendExistingItemByRevisionId(id, type, title, selected_id) {
    var selected_properties = getPropertiesFromId(selected_id);
    // create a new slide/deck and return the id
    $.ajax({
        async : false,
        url : "./?url=ajax/insertItemToDeck&root_deck=" + deck + "&node_id="
            + selected_id+ "&type="+type+ "&item_id="+id,
        success : function(msg) { 
            $('#search_modal').modal('hide');
            msg = eval("(" + msg + ")");
            selectOrReload(msg,0);

        }
    });

}
function checkCycle(selected_id, target_id) {
	var parent_id = target_id;
	var selected_properties = getPropertiesFromId(selected_id);
	var parent_properties = getPropertiesFromId(parent_id);
	var deckId = parent_properties['deckId'];
	var itemId = selected_properties['itemId'];
	var cycle;

	while (deckId > 0) {
		if (itemId == deckId) {
			cycle = 0;
			break;
		} else {
			parent_id = getParentFromId(parent_id);
			parent_properties = getPropertiesFromId(parent_id);
			deckId = parent_properties['deckId'];
			cycle = 1;
		}
	}
	return cycle;
}
function removeOverlay(id) {
	$('#' + id).remove();
}
function appendExistingItem(type, selected_id) {
	var deckId = 0;
	if (type == "deck") {
		var parent_properties = getPropertiesFromId(selected_id);
		var parent_id = selected_id;
		deckId = parent_properties['deckId'];
		while (deckId > 0) {
			parent_id = getParentFromId(parent_id);
			parent_properties = getPropertiesFromId(parent_id);
			deckId = parent_properties['deckId'];
		}
		deckId = parent_properties['itemId'];
		

	}
        $('#search_modal').empty().load("./?url=search/view_overlay&typeOfSearch=" + type + "&selected_id="	+ selected_id);
        $('#search_modal').modal('show');
	
}
function renameItem(selected_id, title) {
	var properties = getPropertiesFromId(selected_id);
	var child_node_id='';
	if(properties['type']=='deck'){
		child_node_id=getLastChildFromId(selected_id);
	}
	$.ajax({
			url : "./?url=ajax/setItemTitle&root_deck=" + deck
					+ "&title=" + encodeURIComponent(title) + '&node_id=' + selected_id+ '&child_node_id=' + child_node_id+ '&type=' + properties['type'],
			success : function(msg) {
				msg = eval("(" + msg + ")");
				selectOrReload(msg,0);
			}
	});
	// $(".jstree-clicked").removeClass("jstree-clicked");
	// $("#"+selected_id).addClass("jstree-clicked");
}
function moveItemOperations(selected_id, target_id, pos_stat) {
	var target_properties = getPropertiesFromId(target_id);
	target_properties['pos'] = parseInt(target_properties['pos']);
	var selected_properties = getPropertiesFromId(selected_id);
	var new_position;
	// move in same deck
	if (target_properties['deckId'] == selected_properties['deckId']) {
		if (target_properties['pos'] > selected_properties['pos']) {
			switch (pos_stat) {
			case "before":
					new_position = target_properties['pos']-1;
				break;
			case "after":
				new_position = target_properties['pos'];
				break;
			}
		}else{
			switch (pos_stat) {
			case "before":
					new_position = target_properties['pos'];
				break;
			case "after":
				new_position = target_properties['pos'] + 1;
				break;
			}	
		}
	}else{
		//move to other deck
		switch (pos_stat) {
		case "before":
				new_position = target_properties['pos'];
			break;
		case "after":
			new_position = target_properties['pos'] + 1;
			break;
		}		
	}
	$
			.ajax({
				url : "./?url=ajax/moveItem&root_deck="
						+ deck + "&source_node_id="
						+ selected_id + "&target_node_id="
						+ target_id + "&position="
						+ new_position,
				success : function(msg) {
					resetGolbalVars();
					msg=eval("(" + msg + ")");
					selectOrReload(msg,0);
				}
			});
}
function moveItem(selected_id, target_id, pos_stat) {
	// get properties for each node
	var selected_properties = getPropertiesFromId(selected_id);
	if (selected_properties['type'] == 'slide') {
		moveItemOperations(selected_id, target_id, pos_stat);
	} else if (checkCycle(selected_id, target_id)) {
		moveItemOperations(selected_id, target_id, pos_stat);

	} else {
		alert('This operation will lead to endless loop');
		$('#after_refresh_node').text('');
		$("#tree").jstree("refresh");
	}
}
function updateSlideIDTitle(deck, slide_old, slide_new, pos) {
	var id, parts;
	$.ajax({
		url : './?url=ajax/getSlideTitle&id=' + slide_new,
		success : function(title) {
			$.each($(".slide-" + slide_old), function(index, value) {
				value.childNodes[1].nodeValue = title;
				$(value).addClass("slide-" + slide_new);
				$(value).addClass("jstree-hovered");
				id = value.id;
				parts = id.split('-');
				value.href = '#tree-' + parts[1] + '-slide-' + slide_new + '-'
						+ parts[4] + '-view';
				value.id = 'tree-' + parts[1] + '-slide-' + slide_new + '-'
						+ parts[4];
			});
		}
	});
}
function updateCurrentSlideIDTitle(node_id, new_slide_id) {
	var parts;
	$.ajax({
		url : './?url=ajax/getSlideTitle&id=' + new_slide_id,
		success : function(title) {
			console.log('here!');
			$("#" + node_id)[0].childNodes[1].nodeValue = title;
			$("#" + node_id)[0].addClass("slide-" + new_slide_id);
			$("#" + node_id)[0].addClass("jstree-hovered");
			parts = node_id.split('-');
			console.log(parts);
			console.log('#tree-' + parts[1] + '-slide-' + new_slide_id + '-'
					+ parts[4]);
			$("#" + node_id)[0].href = '#tree-' + parts[1] + '-slide-'
					+ new_slide_id + '-' + parts[4] + '-view';
			$("#" + node_id)[0].id = 'tree-' + parts[1] + '-slide-'
					+ new_slide_id + '-' + parts[4];
		}
	});
}


//------------------------------------for general use----------------------------
function in_array( needle, haystack) {
    for(var key in haystack){
        if (haystack[key] == needle){
            return true;
        }
    }
     return false;
}

function unWrap(container, resultArray){
    //alert(container.tagName);    
    var im_text = $(container).immediateText();
    var new_text = im_text.replace(/[ \n]+/,'');
    
    if(new_text.length > 0){
        
        resultArray.push($(container).immediateText());
    }
    if ($(container).children().size()){
        $(container).children().each(function(){
            
            unWrap(this,resultArray);
        })
    } 
    return resultArray;    
}

function addslashes(str) {
str=str.replace(/\\/g,'\\\\');
str=str.replace(/\'/g,'\\\'');
str=str.replace(/\"/g,'\\"');
str=str.replace(/\0/g,'\\0');
return str;
}

function stripslashes(str) {
str=str.replace(/\\'/g,'\'');
str=str.replace(/\\"/g,'"');
str=str.replace(/\\0/g,'\0');
str=str.replace(/\\\\/g,'\\');
return str;
}

/*---------------------- Followind slides/decks functions -----------------*/
function follow(obj, item_type, id) {
	var unfollow = 0;
	if (obj.text().trim() == "Follow " + item_type)
		unfollow = 0;
	else
		unfollow = 1;
	$.ajax({
		url : './?url=ajax/isFollowing&item_type=' + item_type + '&id=' + id
				+ '&unfollow=' + unfollow,
		success : function() {
			if (unfollow == 1) {
				obj.text("Follow " + item_type);
				obj.removeClass('danger');
				obj.addClass('success');
			} else {
				obj.text("Unfollow " + item_type);
				obj.removeClass('success');
				obj.addClass('danger');
			}
			if(item_type=='slide')
				$('.slide-follow #'+$(obj)[0].id).parent().html($('#deck_follow_status').html());
			else
				if ((item_type=='deck'))
					$('.deck-follow #'+$(obj)[0].id).parent().html($('#deck_follow_status').html());
		}
	});
}
/*---------------------- Breadcrumb functions -----------------*/
function createBreadCrumb(selected_id) {
	var node_arr = new Array();
	var output='';
	var sibling='';
	var status_no='';
	var separator='<span class="separator-icon"> <b>►</b></span>';
	var tmp,tmp2,temp_id,temp_title,temp_parent, temp_fullid,usage_sibling, i = 0;
	var current = $("#" + selected_id + "-node");
	while (current[0].id != "tree") {
		current = $("#" + current[0].id).parent().parent();
		temp_id = getPropertiesFromId(current[0].id)['itemId'];
		temp_parent = getPropertiesFromId(current[0].id)['deckId'];
		temp_title=$(current[0].children[1]).text();
		temp_fullid=getPropertiesFromHash(current[0].id)['nodeId'];
		if (temp_id) {
			node_arr[i] = temp_title;
			sibling='';
			if(temp_parent!=0)
				sibling=prepareSiblingDecks(temp_fullid);
			output=sibling+'<li><a class="pointer-cursor" onclick="selectNode(\''+temp_fullid+'\');">'+temp_title+'</a></li>'+output;
		} 
		i++;
	}
	if(getPropertiesFromId(selected_id)['type']=='deck'){
		if(output){
			sibling=prepareSiblingDecks(selected_id);
			output=output+sibling+$($("#" + selected_id + "-node")[0].children[1]).text();
		}else{ //for the root node
			output=$($("#" + selected_id + "-node")[0].children[1]).text();	
		}
		status_no='';
	}else{
		output=output+'<li>'+separator+'</li>';
		//get slide numbers
		//fix for bug when ajax loader is added in search
		tmp=$('#'+selected_id+'-node').parent().parent().find('.deck-'+getPropertiesFromId(selected_id)['deckId']).attr('title');
		tmp=tmp.split('|')[1];
		//console.log(tmp);
		tmp=tmp.split(' ')[1];
		//tmp=tmp.substring(1);
		//if the parent is root node show the absolute position
		if($('#'+selected_id+'-node').parent().parent().parent().parent()[0].id=='tree'){
			tmp2=$('#'+selected_id).attr('title');
			tmp2=tmp2.split('|')[2];
			tmp2=tmp2.split(':')[1];
			status_no=tmp2+'/<b>'+tmp+'</b>';
		}else
			status_no=getPropertiesFromId(selected_id)['pos']+'/<b>'+tmp+'</b>';
	}
	//get the root deck id from global deck var
	usage_sibling='<a id="tree-0-deck-'+deck+'-1-siblings" class="usagePath-icon" onclick="prepareUsageSiblingDecks(\'tree-0-deck-'+deck+'-1\')" onmouseover="$(this).addClass(\'usagePath-icon-active\')" onmouseout="$(this).removeClass(\'usagePath-icon-active\')" title="Show/hide full usage path"> &Dagger;</a> <span id="root_deck_sibling"></span>'
	$('#breadcrumb').html( '<ul class="breadcrumb"><li>'+usage_sibling+'</li>'+output+'<li id="slide_status_number"> '+status_no+'</li></ul>');
}
//creates the dropdown for root deck usage
function prepareUsageSiblingDecks(deck_node_id) {
	var deck_id=getPropertiesFromId(deck_node_id)['itemId'];
	var tmp=$("#root_deck_sibling").text().trim();
	if(!tmp){
	$("#root_deck_sibling").html('');
	$.ajax({
		//url : './?url=ajax/showDeckUsage&id=' + deck_id,
		url : './?url=ajax/createUsagePath&deck=' + deck_id,
		success : function(msg) {
			//var data = eval("(" + msg + ")");
			$("#root_deck_sibling").html('Full Usage Path:<br/>'+msg+'<hr/> Current Path:<br/>');
			//$("#root_sibling_usage").tmpl(data).appendTo("#root_deck_sibling");
			//if($("#root_deck_sibling").text().trim()){
				//$("#root_deck_sibling").prepend('<li><center><b>Used in:</b></center></li>');
			//}else{
				//$("#root_deck_sibling").prepend('<li><center><b>No usage found!</b></center></li>');
			//}
			//showSiblibgs('#'+deck_node_id+'-siblings');
			$("#root_deck_sibling").show();
		}
	});
	}else{
		$("#root_deck_sibling").html('');
		//showSiblibgs('#'+deck_node_id+'-siblings');
	}
}
//creates the dropdown for child decks
function prepareSiblingDecks(deck_node_id) {
	var output='';
	var separator='<span class="separator-icon"> <b>►</b></span>';
	if(deck_node_id){
		var tmp=$('#'+deck_node_id+'-node')[0].previousSibling;
		while(tmp){
			if(!$(tmp).hasClass("jstree-leaf"))
				output='<li><a style="cursor:pointer" onclick="selectNode(\''+getPropertiesFromHash(tmp.id)['nodeId']+'\');">'+$($(tmp)[0].children[1]).text()+'</a></li>'+output;
			tmp=$(tmp)[0].previousSibling;
		}		
		var tmp=$('#'+deck_node_id+'-node')[0].nextSibling;
		while(tmp){
			if(!$(tmp).hasClass("jstree-leaf"))
				output=output+'<li><a style="cursor:pointer" onclick="selectNode(\''+getPropertiesFromHash(tmp.id)['nodeId']+'\');">'+$($(tmp)[0].children[1]).text()+'</a></li>';
			tmp=$(tmp)[0].nextSibling;
		}
		if(output)
			output='<span id="'+deck_node_id+'-siblings" class="dropdown-sibling" onclick="showSiblibgs(this)" onmouseover="$(this).addClass(\'separator-icon-active\')" onmouseout="$(this).removeClass(\'separator-icon-active\')">'+separator+'<ul class="dropdown-menu">'+output+'</ul></span>';	
		else
			output='<span id="'+deck_node_id+'-siblings" class="dropdown-sibling" onclick="showSiblibgs(this)" onmouseover="$(this).addClass(\'separator-icon-active\')" onmouseout="$(this).removeClass(\'separator-icon-active\')">'+separator+'</span>';
		return output;
	}
}
function showSiblibgs(ref) {
	if($(ref).hasClass('open')){
		$(ref).removeClass('open');
		$(ref).find('.separator-icon').html(' <b>►</b>');
	}else{
		$('.dropdown-sibling').removeClass('open');
		$('.separator-icon').html(' <b>►</b>');
		$(ref).addClass('open');
		$(ref).find('.separator-icon').html(' <b>▼</b>');
		var position = $(ref).position();
		$('.dropdown-sibling .dropdown-menu').css('left',position.left+5).css('top',position.top+23);
	}
}
/*---------------------- deck editors functions -----------------*/
//show users in category of editor
function show_editors(deck_id,read_only){
	if($("#editors").text().trim()!='')
		$("#editors").text('');
	$.ajax({
		url : './?url=ajax/getEditors&deck_id=' + deck_id,
		success : function(msg) {
			var data = eval("(" + msg + ")");
			data.deck_id=deck_id;
			if(read_only){
				$("#editor_list_ro").tmpl(data).appendTo("#editors");
			}else{
				$("#editor_list").tmpl(data).appendTo("#editors");
			}
		}
	});
}
//show template for adding editors
function add_editor(deck_id,is_owner){
	if($("#editors").text().trim()!='')
		$("#editors").text('');
    var data ={"deck_id": deck_id};
    if(is_owner){
    	$("#add_editor").tmpl(data).appendTo("#editors");
    }else{
    	$("#add_editor_no").tmpl(data).appendTo("#editors");
    }
}
//add users as editor
function add_editor_action(deck_id,is_owner){
	var userOrMail=$('#editor_useremail').val().trim();
	if(!userOrMail){
		$("#editors").text('');
		return;
	}	
	var ajax_url;
	if($('#apply_to_all_subdecks:checked').length){
		//assign editor to all subdecks belonging to owner or editor
		if(is_owner)
			ajax_url='./?url=ajax/addEditorToAllSubdecks&is_editor=0&deck_id=' + deck_id +'&userOrMail='+userOrMail;
		else
			ajax_url='./?url=ajax/addEditorToAllSubdecks&is_editor=1&deck_id=' + deck_id +'&userOrMail='+userOrMail;	
	}else{
		//assign editor only to current deck
		ajax_url='./?url=ajax/addEditor&deck_id=' + deck_id +'&userOrMail='+userOrMail;
	}
	$.ajax({
		url : ajax_url,
		success : function(msg) {
			var data = eval("(" + msg + ")");
			if(!data){
				alert('User is not registered in the system!');
				return 0;
			}
			data.deck_id=deck_id;
			$("#editors").text('');
			$("#editor_no").text(data.users.length);
			if(!data.users.length){
				$("#add_desc").text('Add one');
			}else{
				$("#add_desc").text('Add more');
			}
		    if(is_owner){
		    	$("#editor_list").tmpl(data).appendTo("#editors");
		    }else{
		    	$("#editor_list_ro").tmpl(data).appendTo("#editors");
		    }
		}
	});
}
function remove_user_from_deck(user_id,deck_id){
	var ajax_url;
	if($('#apply_to_all_subdecks_'+user_id+':checked').length){
		//delete editor from all subdecks belonging to owner
		ajax_url='./?url=ajax/removeEditorFromAllSubdecks&deck_id=' + deck_id +'&user_id='+user_id;
	}else{
		//delete editor only from the current deck
		ajax_url='./?url=ajax/removeEditor&deck_id=' + deck_id +'&user_id='+user_id;
	}	
	$.ajax({
		url : ajax_url,
		success : function(msg) {
			var data = eval("(" + msg + ")");
			data.deck_id=deck_id;
			$("#editors").text('');
			$("#editor_no").text(data.users.length);
			if(!data.users.length){
				$("#add_desc").text('Add one');
			}else{
				$("#add_desc").text('Add more');
			}
			$("#editor_list").tmpl(data).appendTo("#editors");
		}
	});	
}
/*-----------tree tools functions------------------------------*/
function toggleScrollToTree() {
	if($('#tree').hasClass('with-scroll'))
		$('#tree').removeClass('with-scroll')
	else		
		$('#tree').addClass('with-scroll');
}
function showSearchInput(){
	if(!$('#tree_tool_search').length){
		$('#tree_tools').append('<input id="tree_tool_search" type="text" class="input span5 search-query" placeholder="Search">');
		$("#tree_tool_search").keyup(function(event){
			/*
			if(event.keyCode == 13){ //when enter is pressed
				handleSearchInSlides($("#tree_tool_search").val().trim());
			}
			*/
			var k=$("#tree_tool_search").val().trim();
			setTimeout(function() {
					if(k==$("#tree_tool_search").val().trim())
						handleSearchInSlides($("#tree_tool_search").val().trim());
			}, 1000);
		});	
	}else{
		$('.slide').removeHighlight();
		$('#tree_tool_search').remove();
		$('.tree-node-highlighted').removeClass('tree-node-highlighted');
	}	
}
function handleSearchInSlides(term){
	var tmp;
	if(term.length==0)
		$('.tree-node-highlighted').removeClass('tree-node-highlighted');
	if(term.length<3)
		return 0;
	//enable search in selected nodes
	var search_nodes=new Array(); 
	search_nodes[0]=deck;
	$.ajax({
		url : './?url=ajax/searchInDeckContent&decks='+search_nodes+'&term='+term,
		beforeSend: function() {
		$("#ajax_progress_indicator").css('display', 'none');
			$.each(search_nodes,function(i,v){
				if(!$(".deck-"+v).parent().find('.search-progress').length)
					$(".deck-"+v).before('<img class="search-progress" src="./static/img/ajax_loader_small.gif"  align="center"> ');
			})
		  },		
		success : function(msg) {
			$('.search-progress').remove();
			$('.tree-node-highlighted').removeClass('tree-node-highlighted');
			if(!msg)
				return;
			var data = eval("(" + msg + ")");
			$.each(data,function(i,v){
				$('.slide-'+v.id).addClass('tree-node-highlighted');
				$('.slide-'+v.id).parent().removeClass('jstree-closed');
				$('.slide-'+v.id).parent().addClass('jstree-open');
				tmp=$('.slide-'+v.id).parent().parent();
				while(tmp.length){
					$(tmp).removeClass('jstree-closed');
					$(tmp).addClass('jstree-open');
					tmp=$(tmp).parent();
				}
			})
		}
	});	
}
function prepare_find_replace(selected_id){
	var node_title=$('#'+selected_id).text().trim();
	$('#findreplace_node_title').text(node_title);
	$('#findreplace_node').val(selected_id);
	$('#row_find_term .find-next-btn').attr('onclick','findreplace_next(0,0);');
	$('.tree-node-highlighted').removeClass('tree-node-highlighted');
	$('#'+selected_id+'-view').removeHighlight();
	findreplace_results=[];
	$('#row_find_term .find-prev-btn').css('display','none');
	$('#find_result_no').html('');
}
function findreplace_next(term,i){
	if(!term){
		var term=$("#findreplace_term").val();
	}
	if(term.length<3){
		alert('Your search term must have more than 2 characters!');
		return 0;
	}
	var term2=$("#findreplace_term").val();
	if((term!=term2)){
		findreplace_next(term2,0)
		return 0;
	}
	if(i==0){
		findreplace_results=[];
		var search_nodes=new Array(); 
		var selected_id=$("#findreplace_node").val();
		var p=getPropertiesFromId(selected_id);
		search_nodes[0]=p['itemId'];
		var search_url;
		if(p['type']=="slide")
			search_url='./?url=ajax/searchInSlideContent&slides='+search_nodes+'&term='+term;
		else
			search_url='./?url=ajax/searchInDeckContent&decks='+search_nodes+'&term='+term;	
		$.ajax({
				url : search_url,
				beforeSend: function() {
					if(p['type']!="slide"){
						$("#ajax_progress_indicator").css('display', 'none');
						$.each(search_nodes,function(i,v){
							if(!$(".deck-"+v).parent().find('.search-progress').length)
								$(".deck-"+v).before('<img class="search-progress" src="./static/img/ajax_loader_small.gif"  align="center"> ');
						})
					}
				 },		
				success : function(msg) {
					$('#find_result_no').html('');
					$('.search-progress').remove();
					$('.tree-node-highlighted').removeClass('tree-node-highlighted');
					$('.slide').removeHighlight();
					if(!msg){
						alert('Can not find "'+term+'"!');
						return 0;
					}
					if(p['type']=="slide"){
						if(!eval(msg)){
							alert('Can not find "'+term+'"!');
							return 0;
						}
						findreplace_results.push(eval(msg));
						if($('.slide-'+msg).length==1){
							selectNode($('.slide-'+msg).attr('id'));
							$('.slide-'+msg).addClass('tree-node-highlighted');
						}						
						return 0;
					}
					var data = eval("(" + msg + ")");
					$.each(data,function(i,v){
						findreplace_results.push(v.id);
					})
					findreplace_results=manual_cleanArray(findreplace_results);
					if(findreplace_results.length==0){
						alert('Can not find "'+term+'"!');
						return 0;
					}
					$('#find_result_no').html('<b>'+findreplace_results.length+'</b> slide(s) found.');
					//results=results.reverse(); 
					$('.slide-'+findreplace_results[0]).parent().removeClass('jstree-closed');
					$('.slide-'+findreplace_results[0]).parent().addClass('jstree-open');
					var tmp=$('.slide-'+findreplace_results[0]).parent().parent();
					while(tmp.length){
						$(tmp).removeClass('jstree-closed');
						$(tmp).addClass('jstree-open');
						tmp=$(tmp).parent();
					}	
					if($('.slide-'+findreplace_results[0]).length==1){
						selectNode($('.slide-'+findreplace_results[0]).attr('id'));
						$('.slide-'+findreplace_results[0]).addClass('tree-node-highlighted');
					}					
					//console.log(findreplace_results);
					$('#row_find_term .find-next-btn').attr('onclick','findreplace_next("'+term+'",1);');
					$('#row_find_term .find-prev-btn').attr('onclick','findreplace_next("'+term+'",-1);');
					$('#row_find_term .find-prev-btn').css('display','none');
				}
			});		
	}else{
		if((i>findreplace_results.length-1) || (i<0)){
			alert('Can not find more results!');
			return 0;
		}else{
			$('.tree-node-highlighted').removeClass('tree-node-highlighted');
			$('.slide').removeHighlight();
			$('.slide-'+findreplace_results[i]).parent().removeClass('jstree-closed');
			$('.slide-'+findreplace_results[i]).parent().addClass('jstree-open');
			var tmp=$('.slide-'+findreplace_results[i]).parent().parent();
			while(tmp.length){
				$(tmp).removeClass('jstree-closed');
				$(tmp).addClass('jstree-open');
				tmp=$(tmp).parent();
			}	
			$('#row_find_term .find-prev-btn').attr('onclick','findreplace_next("'+term+'",'+(i-1)+');');
			$('#row_find_term .find-prev-btn').css('display','');
			$('#row_find_term .find-next-btn').attr('onclick','findreplace_next("'+term+'",'+(i+1)+');');
			if($('.slide-'+findreplace_results[i]).length==1){
				selectNode($('.slide-'+findreplace_results[i]).attr('id'));
				$('.slide-'+findreplace_results[i]).addClass('tree-node-highlighted');
			}
		}
	}
}
function find_replace(){
	if (!findreplace_results.length){
		alert('Please first search for the matches!');
		return 0;
	}
	var term=$("#findreplace_term").val();
	var rep=$("#findreplace_replace").val();
	$('#slideview .deck-current .slide-content .search-highlight').html(rep);
	$('#slideview .deck-current .slide-content').removeHighlight();
	item_change.push($('#slideview .deck-current .slide-body').attr('id'));
	show_save_toolbar();
	enable_save_button();
}
function find_replace_all(){
	//console.log(findreplace_results);
	if (!findreplace_results.length){
		alert('Please first search for the matches!');
		return 0;
	}
	var slides=new Array(); 
	var decks=new Array();
	var id,p,res;
	var term=$("#findreplace_term").val();
	var rep=$("#findreplace_replace").val();
	res=true;
	$.each(findreplace_results,function(i,v){
		if($('.slide-'+v).length==1){
			id=$('.slide-'+v).attr('id');
			p=getPropertiesFromId(id);
			decks.push(p['deckId']);
			slides.push(id);
		}
	})
	decks=$.unique( decks );
	//console.log(decks);
	if (!decks.length)
		return 0;
	$.ajax({
		url : './?url=ajax/checkCreatingNewDeckRevMultiple&decks='+decks,	
		success : function(msg) {
			msg=eval(msg);
			//might create new revision
			if(msg){
				var answer = confirm("This change might create a new revision for some decks. Are you sure you want to do it?");
				if (answer) {
					res = true;//no need to create new revision
				} else {
					res = false;// need to create new revision
				}
			}else{
				var answer = confirm("Are you sure you want to replace all the instances of term '"+term+"'?");
				if (answer) {
					res = true;//no need to create new revision
				} else {
					res = false;// need to create new revision
				}				
			}
			//console.log(res);
			if(res){
				//do the action
				$.ajax({
					url : './?url=ajax/saveSlidesByReplace&root_deck='+deck+'&term='+term+'&rep='+rep+'&slides='+slides,	
					success : function(data) {
						$('#modal_findreplace').modal('hide');
						findreplace_results=[];
						$('#row_find_term .find-prev-btn').css('display','none');
						$('#find_result_no').html('');
						var data = eval("(" + data + ")");
						//for single change select the corresponding node
						if(slides.length==1){
							var selected_id=slides[0];
							var p=getPropertiesFromId(selected_id);
							var target_id='tree-'+data.items[0].target_deck_id+'-slide-'+data.items[0].rev_id+'-'+p['pos'];
							selectOrReload(data,target_id);
						}else{
							//for multiple changes select the container deck
							selectOrReload(data,'tree-0-deck-'+deck+'-1');			
						}
					}
				});	
			}
		}
	});			
}
/*-----------General functions------------------------------*/
function changeDeckStyle(){
	$("#page_css").attr('href','ajax/css/' + $('#selected_style').val());
	style=$('#selected_style').val();
	var tmp,selected_id;
	if (!$("#tree").jstree("get_selected")[0]) {
		tmp=$('.jstree-clicked')[0]?$('.jstree-clicked')[0].title:'';
		selected_id=$('.jstree-clicked')[0].id;
	} else {
		tmp=$("#tree").jstree("get_selected")[0].children[1].title;
		selected_id=$("#tree").jstree("get_selected")[0].children[1].id;
	}
	if (!tmp){
		var hash=window.location.hash;
		var parameters=hash.split('#')[1];
		var parts=getPropertiesFromHash(parameters);	
		tmp=$('#'+parts['nodeId']).attr('title');
		selected_id=parts['nodeId'];
	}
	var p=getPropertiesFromId(selected_id);
	if(p['type']=='slide'){
		tmp=tmp.split('|')[2];
		absolute_pos=tmp.split(':')[1];	
		$('#playSlide').attr('href','playSync/style/'+style+'/transition/'+transition+'/deck/'+deck+'#'+selected_id+'-'+(parseInt(absolute_pos)-1)+'-view');
	}else{
		$('#playSlide').attr('href','playSync/style/'+style+'/transition/'+transition+'/deck/'+deck+'#');
	}		
	$.ajax({
		url : './?url=ajax/setUserPreference&deck=' + deck +'&type=style&id='+$('#selected_style').val(),
		success : function(msg) {
			//console.log(msg);
		}
	});		
}
function changeDeckTransition(){
	$("#page_transition").attr('href','ajax/transition/css/' + $('#selected_transition').val());
	transition=$('#selected_transition').val();
	var tmp,selected_id;
	if (!$("#tree").jstree("get_selected")[0]) {
		tmp=$('.jstree-clicked')[0]?$('.jstree-clicked')[0].title:'';
		selected_id=$('.jstree-clicked')[0].id;
	} else {
		tmp=$("#tree").jstree("get_selected")[0].children[1].title;
		selected_id=$("#tree").jstree("get_selected")[0].children[1].id;
	}
	if (!tmp){
		var hash=window.location.hash;
		var parameters=hash.split('#')[1];
		var parts=getPropertiesFromHash(parameters);	
		tmp=$('#'+parts['nodeId']).attr('title');
		selected_id=parts['nodeId'];
	}
	var p=getPropertiesFromId(selected_id);
	if(p['type']=='slide'){
		tmp=tmp.split('|')[2];
		absolute_pos=tmp.split(':')[1];	
		$('#playSlide').attr('href','playSync/style/'+style+'/transition/'+transition+'/deck/'+deck+'#'+selected_id+'-'+(parseInt(absolute_pos)-1)+'-view');
	}else{
		$('#playSlide').attr('href','playSync/style/'+style+'/transition/'+transition+'/deck/'+deck+'#');
	}
	$.ajax({
		url : './?url=ajax/setUserPreference&deck=' + deck +'&type=transition&id='+$('#selected_transition').val(),
		success : function(msg) {
			//console.log(msg);
		}
	});		
}
//checks whether we need to reload a tab content or not
function reloadTabContent(tab_name,id,prefix){
	//using css classes to check the reload of div
	switch(tab_name){
	case 'Questions':
		if ($("#itemquestions").hasClass(prefix+'-'+id)) {
			return 0;
		}else{
			$("#itemquestions").html('');
			$("#itemquestions").removeClass();
			$("#itemquestions").addClass(prefix+'-'+id);
			return 1;
		}
		break;
	case 'History':
		if ($("#itemhistory").hasClass(prefix+'-'+id)) {
			return 0;
		}else{
			$("#itemhistory").html('');
			$("#itemhistory").removeClass();
			$("#itemhistory").addClass(prefix+'-'+id);
			return 1;
		}
		break;	
	case 'Usage':
		if ($("#itemusage").hasClass(prefix+'-'+id)) {
			return 0;
		}else{
			$("#itemusage").html('');
			$("#itemusage").removeClass();
			$("#itemusage").addClass(prefix+'-'+id);
			return 1;
		}
		break;		
	case 'Discussion':
		if ($("#itemdiscussion").hasClass(prefix+'-'+id)) {
			return 0;
		}else{
			$("#itemdiscussion").html('');
			$("#itemdiscussion").removeClass();
			$("#itemdiscussion").addClass(prefix+'-'+id);
			return 1;
		}
		break;			
	}
}
//to extract different properties of a node from its id
function getPropertiesFromId(id) {
	var output = new Array();
	var parts = id.split("-");
	output['deckId'] = parts[1];
	output['type'] = parts[2];
	output['itemId'] = parts[3];
	output['pos'] = parts[4];
	return output;
}

// to get parent node (deck)/next/previous of a node
function getParentFromId(id) {
	var parent_id = $("#" + id)[0].parentNode.parentNode.previousSibling.id;
	return parent_id;
}
function getNextFromId(id) {
	var next_id = $("#" + id)[0].parentNode.nextSibling;
	if (next_id)
		return next_id.children[1].id;
	else
		return 0;
}
function getPreviousFromId(id) {
	var prev_id = $("#" + id)[0].parentNode.previousSibling;
	if (prev_id)
		return prev_id.children[1].id;
	else
		return 0;
}
//gets a deck_id and returns its last child id
function getLastChildFromId(id) {
	var children_no=$("#" + id+'-node')[0].children[2].children.length;
	var child_id = $("#" + id+'-node')[0].children[2].children[children_no-1].children[1].id;
	return child_id;
}
// to update (++) id of nodes that come after a given id (within a deck)
function updateIdsAfter(id) {
	var next, pos, parts;
	next = id;
	while (next = $('#' + next)[0].parentNode.nextSibling.children[1].id) {
		parts = getPropertiesFromId(next);
		pos = parseInt(parts['pos']) + 1;
		$('#' + next).attr(
				'href',
				'#tree-' + parts['deckId'] + '-' + parts['type'] + '-'
						+ parts['itemId'] + '-' + pos + '-view');
		$('#' + next).attr(
				'id',
				'tree-' + parts['deckId'] + '-' + parts['type'] + '-'
						+ parts['itemId'] + '-' + pos);
		next = 'tree-' + parts['deckId'] + '-' + parts['type'] + '-'
				+ parts['itemId'] + '-' + pos;
	}
}
function hideModal(id){
	$('#' + id).hide();
}
function open_slide_view(id){
    var node = $("#slide_viewer_"+id);
    var link = $("#link_view_slide_"+id);   
    if ( node.css("display") == 'none' ){
        
        node.show();
        link.empty().append('   Hide slides');
    }else {
        node.hide();
        link.empty().append('   View slides');
    }
}
function submitNewDeck(){
    var form = $('#newdeck');
    var lang_id = form.find('#language_id').val();
    if(lang_id){
        $('#true_submit').click();
    }else{
        alert('Please, set the language of the deck');
    }
}
function overlaySelected(selected_id) {
	var i = 0;
	$('#'+selected_id+' .slide').each(function() {
		$(this).attr({
			"targetId" : i,
			"onmouseout" : "hideOverlay()"
		});
		i++;
	});
	$('#'+selected_id+' .slide')
			.mouseover(
					function() {
						var div = $("<div>");
						var html = "<div class='popover fade below in' style='display: block;'><div class='arrow right' style='left:10%;'></div><div class='inner'><div class='content'><p>"
								+ $(this).html() + "</p></div></div></div>";
						div.html(html).attr({
							id : $(this).attr("targetId")
						}).appendTo($(this).parent().parent().parent());
						div.position({
							of : $(this),
							my : "left top",
							at : "left bottom",
							offset : "0",
							collision : "none"

						});

					});	
}
function overlay() {
	var i = 0;
	$('.deck-container .slide').each(function() {
            $(this).attr({
                    "targetId" : i,
                    "onmouseout" : "hideOverlay()"
            });
            i++;
	});
	$(".deck-container .slide")
            .mouseover(
                function() {
                    var div = $("<div>");
                    var html = "<div class='popover fade below in' style='display: block;'><div class='arrow right' style='left:10%;'></div><div class='inner'><div class='content'><p>"
                                    + $(this).html() + "</p></div></div></div>";
                    div.html(html).attr({
                            id : $(this).attr("targetId")
                    }).appendTo($(this).parent().parent().parent());
                    div.position({
                            of : $(this),
                            my : "left top",
                            at : "left bottom",
                            offset : "0",
                            collision : "none"

                    });

                });
        //prevent clicking on thumbnail content
        $('.deck-menu .slide').children().each(function(){
            $(this).unbind('click');
            $(this).bind('click',function(e){
                e.preventDefault();
            })
        })

}
function selectTab(tabName) {
	$(".active").removeClass('active');
	$("#" + tabName + "link").parent().addClass('active');
	$("#" + tabName).addClass('active');
}
function hideOverlay() {
	$('.popover').each(function() {
		$(this).remove();
	});
}
function getCurrenUserID() {
	var user; // get the current user_id
	$.ajax({
		async : false,
		url : 'ajax/currentUser',
		success : function(msg) {
			user = eval(msg);
		}
	});
	return user;
}
// created a list of decks until root
// output [deck11_id,deck1_id,0]
function getNestedDecksList(selected_id) {
	var node_arr = new Array();
	var temp, i = 0;
	var current = $("#" + selected_id + "-node");
	while (current[0].id != "tree") {
		current = $("#" + current[0].id).parent().parent();
		temp = getPropertiesFromId(current[0].id)['itemId'];
		if (temp) {
			node_arr[i] = temp;
		} else
			node_arr[i] = "0";
		i++;
	}
	return node_arr;
}
// checks if selected item is the last item in the deck
function isLastItem(selected_id) {
	if (!$('#' + selected_id + '-node')[0].nextSibling
			&& !$('#' + selected_id + '-node')[0].previousSibling)
		return true;
	else
		return false;
}
function get_node_id_from_parts(whole_id){
	var parts = whole_id.split("_");
	var selected_id=parts[2];	
	return selected_id;
}
function resetGolbalVars(){
	//to prevent re-rendering of decks
	is_deck_loaded = false;
	//to empty the change storage
	item_change=[];	
	//empty the active editor
	$('#active_editor_id').text('');
	//disable save button
	$("#save_changes_button").addClass('save_inactive');
	//reset selection range array
	loaded_range=[];	
	//all_slides=[];
	$.each(minor_changes, function(k,v){
		$.cookie(v,null);
	})
	minor_changes=[];
}
function searchSubmitForm(keywords){
    
    window.location='search/keyword/'+keywords;
    return false;
}
function updateShareLinks(input){
	var url='';
	switch(input){
	case 1:
		url=$('#link_current_item').val();
		break;
	case 2:
		url=$('#link_latest_item').val();
		break;
	case 3:
		url=$('#link_user_latest_item').val();
		break;
	}
	$('#facebook_share_link').attr('href','http://www.facebook.com/sharer.php?u='+url);
	$('#google_share_link').attr('href','https://plus.google.com/share?url='+url);	
	$('#linkedin_share_link').attr('href','http://www.linkedin.com/shareArticle?mini=true&url='+url);
	$('#twitter_share_link').attr('href','https://twitter.com/share?url='+url);	
}
function showShareLink(type,id){
	var user_id=getCurrenUserID();
	switch(type){
	case 'slide':
		$('#facebook_share_link').attr('href','http://www.facebook.com/sharer.php?u=http://slidewiki.org/slide/'+id+'&t='+window.document.title);
		$('#google_share_link').attr('href','https://plus.google.com/share?url=http://slidewiki.org/slide/'+id);	
		$('#linkedin_share_link').attr('href','http://www.linkedin.com/shareArticle?mini=true&url=http://slidewiki.org/slide/'+id+'&title='+window.document.title);
		$('#twitter_share_link').attr('href','https://twitter.com/share?url=http://slidewiki.org/slide/'+id+'&text='+window.document.title);
		
		$('#share_link_title').html('<b>Slide</b>-> <i>'+window.document.title+'</i>');
		$('#link_current_item').val('http://slidewiki.org/slide/'+id);
		$('#link_latest_item').val('http://slidewiki.org/slide/'+id+'/latest');
		if(user_id)
			$('#link_user_latest_item').val('http://slidewiki.org/slide/'+id+'/user/'+user_id);
		else
			$('#link_user_latest_item').val('You are not logged in!');		
		break;
		
	case 'deck':
		$('#facebook_share_link').attr('href','http://www.facebook.com/sharer.php?u=http://slidewiki.org/deck/'+id+'&t='+window.document.title);
		$('#google_share_link').attr('href','https://plus.google.com/share?url=http://slidewiki.org/deck/'+id);		
		$('#linkedin_share_link').attr('href','http://www.linkedin.com/shareArticle?mini=true&url=http://slidewiki.org/deck/'+id+'&title='+window.document.title);
		$('#twitter_share_link').attr('href','https://twitter.com/share?url=http://slidewiki.org/deck/'+id+'&text='+window.document.title);
		
		$('#share_link_title').html('<b>Deck</b>-> <i>'+window.document.title+'</i>');
		$('#link_current_item').val('http://slidewiki.org/deck/'+id);
		$('#link_latest_item').val('http://slidewiki.org/deck/'+id+'/latest');
		if(user_id)
			$('#link_user_latest_item').val('http://slidewiki.org/deck/'+id+'/user/'+user_id);
		else
			$('#link_user_latest_item').val('You are not logged in!');
	break;
	}
	 $("#modal_share_link").draggable({
		    handle: ".modal-header"
		});
	$('#modal_share_link').modal('show');
}
function showInvSources(obj){
	$(obj).remove();
	$('.slide-osource-item').show();
}

function IsLeapYear (Year) {
  return ((Year % 4) == 0) && (((Year % 100) != 0) || ((Year % 400) == 0));
}

function DaysPerMonth (Year, Month) {
   DaysInMonth = new Array (31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
   if (Month == -1) Month = 11;
   var days=DaysInMonth [Month];
   if ((Month == 1) && IsLeapYear(Year)) {
       days++;
   }
   return days;
}

function DiffDateTime (aDate, bDate) {  
   
   var aYear = aDate.getYear();         
   if (aYear<200) aYear = aYear+1900;
   var date_a = new Array (aYear, aDate.getMonth (), aDate.getDate ());
   
   var bYear = bDate.getYear(); 
   if (bYear<200) bYear = bYear+1900;
   var date_b = new Array(bYear, bDate.getMonth(), bDate.getDate());
   
   var maxValues = new Array (0, 12, 0)

   for (i=5; i>=0; i--) {
     if (date_a[i]<date_b[i] && i>0) {
        date_a[i-1]--;
        date_a[i] += maxValues[i] - date_b[i];
        if (i==2) 
            date_a[i] += DaysPerMonth (date_a[0], date_a[1]);
     } else {
       date_a[i] -= date_b[i];
     }
   }

   return date_a;
}

function DaysLeftText (Year, Month, Day) {
   var preValues = new Array (Year, Month, Day);
   var preTexts = new Array('year','month','day');
   for (i=0; i<=2; i++){
       if (preValues[i] > 1) preTexts[i] += 's';
   }
   var numPartsPresent=0;
   for (var i=2; i>=0; i--) 
       if (preValues[i]!=0) 
           numPartsPresent++;

   var text=''
   for (i=2; i>=0; i--) {
      if (preValues[i]!=0) {
         text=preValues[i]+' '+preTexts[i]+' '+text;
         if (numPartsPresent>1) {
            text='and '+text;
            numPartsPresent=0;
         } 
      }
   }
   return text;
}

function prettyDate(time){
	var date = new Date((time || "").replace(/-/g,"/").replace(/[TZ]/g," ")),
		diff = (((new Date()).getTime() - date.getTime()) / 1000),
		day_diff = Math.floor(diff / 86400);
	if ( isNaN(day_diff) || day_diff < 0 )
		return 0;
	if (day_diff >= 28) {
            var aDate = new Date();           
            var date_to_show = DiffDateTime (aDate, date);
            var text_to_show = DaysLeftText (date_to_show[0],date_to_show[1],date_to_show[2]) + ' ago';
            return text_to_show;		
        }
	return day_diff == 0 && (
			diff < 60 && "just now" ||
			diff < 120 && "1 minute ago" ||
			diff < 3600 && Math.floor( diff / 60 ) + " minutes ago" ||
			diff < 7200 && "1 hour ago" ||
			diff < 86400 && Math.floor( diff / 3600 ) + " hours ago") ||
		day_diff == 1 && "Yesterday" ||
		day_diff < 7 && day_diff + " days ago" ||
		day_diff < 31 && Math.ceil( day_diff / 7 ) + " weeks ago";
}

function pdfOpenModal(){
    $('#pdf_modal').modal('show');
}

function pdfExport(test_id){
    var radios = document.getElementsByName('show_answers');

    for (var i = 0, length = radios.length; i < length; i++) {
        if (radios[i].checked) {
            var show_answers = radios[i].value;
        }
    }
    
    $('#submitPdf').attr('href','./?url=pdf/test&id=' + test_id + '&show_answers=' + show_answers ) ;
}

function openSCOModal(deck_id){
    $('#exportToSco').modal('show');
    $('#exportToSco').find('#deck_id').val(deck_id);
}

