show_full_flag=0;
function show_fullscreen(){
	var selected_id;
	if (!$("#tree").jstree("get_selected")[0]) {
		selected_id = $('.jstree-clicked')[0].id;
	} else {
		selected_id = $("#tree").jstree("get_selected")[0].children[1].id;
	}	
	if(!show_full_flag){
		//$('.topbar').hide();
		$('#top-toolbar').hide();
		$('#deck-brand').hide();
		$('#additional-elements').hide();
		$('#license_bar').hide();
		$('.page-header').hide();
		$("#ue-feedback-tab").hide();
		$("#control-elements").hide();
		$(".tabs").hide();
		$("footer").hide();
		$('.span-two-thirds').css('width','100%').css('height','100%');
		apply_fullscreen_slide();
		applyScaling(selected_id);
		//$('.container').css('width','auto').css('height','auto');
		//$('.content').css('width','auto').css('height','auto');
		//$('.deck-container').css('width','auto').css('height','auto');	
		show_full_flag=1;
		$("#view").before('<center><a id="tmp_fullscreen_button" onclick="show_fullscreen();" title="Fullscreen" class="fullscreen btn pointer-cursor"><i class="icon-resize-small"></i></a></center>');
		//$('html,body').animate({scrollTop: $("#view").offset().top},'slow');
	} else {
		//$('.topbar').show();
		$('#top-toolbar').show();
		$('#deck-brand').show();
		$('#additional-elements').show();
		$('#license_bar').show();
		$('.page-header').show();
		$("#ue-feedback-tab").show();
		$("#control-elements").show();
		$(".tabs").show();
		$("footer").show();
		$('.span-two-thirds').css('width','620px').css('height','auto');
		undo_fullscreen_slide();
		applyScaling(selected_id);
		//$('.container').css('width','940px');
		//$('.content').css('width','auto').css('height','auto');
		//$('.deck-container').css('width','auto').css('height','auto');		
		show_full_flag=0;
		$('#tmp_fullscreen_button').remove();
	}
}
function scaleSlide(id,predefined_height){
	var h_title=$("#"+id+"-view .slide-header").height();
	var h_body=$("#"+id+"-view .slide-body").height();
	var h=h_title+h_body;
	var error_h=10;
	predefined_height=predefined_height-error_h;
	var scale_p_h;
	if((h>predefined_height)){
		scale_p_h=predefined_height/h;
		$("#"+id+"-view .slide-scaler").css('-moz-transform-origin','left top').css('-moz-transform','scale(1,'+scale_p_h+')').css('-webkit-transform-origin','left top').css('-webkit-transform','scale(1,'+scale_p_h+')');
		//$("#"+id+"-view .slide-header").css('top','-75px').css('position','absolute');
	}else{
			$("#"+id+"-view .slide-scaler").css('-moz-transform','scale(1,1)').css('-webkit-transform','scale(1,1)');
			//$("#"+id+"-view .slide-header").css('top','0px').css('position','relative');	
	}
}
//check if we are in fullscreen mode
function is_fullscreen_mode(){
	if($('#top-toolbar').is(":visible"))
		return false;
	else
		return true;
}
function applyScaling(selected_id){
	if(is_fullscreen_mode())
	    scaleSlide(selected_id,750);
	else
        scaleSlide(selected_id,620);	
}
function apply_fullscreen_slide(){
	$('#slideview').css('max-height' ,'100%').css('max-width' ,'1000px').css('width','1000px').css('height','100%');
	$('.slide-content').css('height' ,'750px').css('min-height' ,'750px').css('max-height' ,'750px').css('min-width' ,'1000px').css('max-width' ,'1000px').css('width' ,'1000px');
	$('.slide-metadata').css('display' ,'none');
	//$('.slide-metadata').css('max-height' ,'100%').css('max-width' ,'100%').css('width','100%').css('height','100%');
	//$('.slide-note').css('max-height' ,'100%').css('max-width' ,'100%').css('width','100%').css('height','100%');
}
function apply_play_fullscreen(){
	$('.slide-content').css('height' ,'100%').css('min-height' ,'100%').css('max-height' ,'100%').css('min-width' ,'100%').css('max-width' ,'100%').css('width' ,'100%');
	$('.slide-metadata').css('max-height' ,'100%').css('max-width' ,'100%').css('width','100%').css('height','100%');
	$('.slide-note').css('max-height' ,'100%').css('max-width' ,'100%').css('width','100%').css('height','100%');
}
function undo_fullscreen_slide(){
	$('#slideview').css('max-height' ,'720px').css('max-width' ,'100%').css('width','590px');
	$('.slide-content').css('min-width' ,'100%').css('min-height' ,'620px').css('max-height' ,'620px').css('max-width' ,'100%').css('width','100%').css('height','620px');
	$('.slide-metadata').css('display' ,'');
	//$('.slide-metadata').css('max-height' ,'100%').css('max-width' ,'620px').css('width','100%').css('height','100%');
	//$('.slide-note').css('max-height' ,'100px').css('max-width' ,'100%').css('width','100%').css('height','100%');
}
