$(document).ready(function(){

	$(document).ajaxStart(function(){
		$("#ajax_progress_indicator").show();
	})	
	$(document).ajaxStop(function(){
		$("#ajax_progress_indicator").hide();
	})
	
	// -- Twitter Bootstrap -----------------------------------------------
	
	$('.topbar').dropdown();
	
	// -- end -- Twitter Bootstrap --
	
	$('.deck-single').css('min-height', ($('.deck-single').width()/4 *3)+'px');
	//alert($('.deck-single').width());
	
});
