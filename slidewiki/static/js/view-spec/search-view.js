function setTag(type,tag){	
        if (tag != 'all'){ 
            $('#tags_mirror_tagsinput').show();
            $('#tags_mirror').addTag(tag);
        }
        submitSearch(type,'1');
}
function setOrder(type,order){
	document.searchForm.order.value=order;
        submitSearch(type,'1');
}
function setLanguage(type,language){    
        document.searchForm.language.value=language;
        submitSearch(type,'1');
}

function goToSlide(url){
    window.location = url;
}

function submitSearch(type,page) {
	var typeOfSearch = type;
        if (document.searchForm) {
            var keywords=document.searchForm.keywords.value;
            var tag = $('#tags_mirror').val();
            var language = document.searchForm.language.value
            var order = document.searchForm.order.value;
            var own = false;
            if (document.searchForm.own.checked == true){
                own = true;
            }
            var selected_id = document.searchForm.selected_id.value;
            $('.order').each(function() {		
                $(this).html(order);	
            })
        }else{
            own = false;
            tag = 'all';
            $('#tags_mirror_tagsinput').hide();
            language = 'all languages';
            order = '';
        }
        if (selected_id){
            $(this).remove();
            tag = 'all';
        }        
	if (typeOfSearch=='both' || typeOfSearch=='deck') {
		
		$('#searchResultsDecks').html('<div id="searchStrings"></div>');
		if (keywords.length>2 || keywords=='') {
                    
                    $.ajax({
                        url: './?url=ajax/searchMatchItem&own=' + own +'&selected_id='+selected_id+'&language=' + language + '&tag='+encodeURIComponent(tag)+'&order='+order+'&page='+page+'&typeOfSearch=deck&keywords='+encodeURIComponent(keywords),
                        async : false,
                        success : function(msg){
                            if (msg!=-1){
                                var data= eval('(' + msg + ')');
                                if (selected_id){
                                   $('#search_decks_ex').tmpl(data).appendTo($('#searchStrings'));                        
                                   if (selected_id!='0') $('.addButton').show();
                                }else{
                                    $('#search_decks').tmpl(data).appendTo($('#searchStrings'));
                                }                                
                                overlay();
                            }else {
                                $('#searchStrings').empty().append('<div>Your search did not match any decks</div>');
                            }
                        }
                    })	
		}
	}
	if (typeOfSearch=='both' || typeOfSearch=='slide') {		
		$('#searchResultsSlides').html('<div id="searchStringsSlides"></div>');	
		if (keywords.length>2 || keywords=='') {		
			$.ajax({
                        url: './?url=ajax/searchMatchItem&own=' + own + '&selected_id='+selected_id+'&language=' + language + '&tag='+encodeURIComponent(tag)+'&order='+order+'&page='+page+'&typeOfSearch=slide&keywords='+encodeURIComponent(keywords),
                        success : function(msg){
                            if (msg!=-1){
                                var data= eval('(' + msg + ')'); 
                                if (selected_id){
                                   $('#search_slides_ex').tmpl(data).appendTo($('#searchStringsSlides'));                        
                                   if (selected_id!='0') $('.addButton').show();
                                }else{
                                    $('#search_slides').tmpl(data).appendTo($('#searchStringsSlides'));
                                }
                                overlay();
                            }else {
                                $('#searchStringsSlides').empty().append('<div>Your search did not match any slides</div>');
                            }
                        }
                    })
		}
	} 
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