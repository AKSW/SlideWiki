<!-- script src="static/js/view-spec/search-view.js"></script -->
<!-- TODO: @Darya: move script to separate file -->
<script type="text/javascript" src="libraries/frontend/jquery-tmpl/jquery.tmpl.min.js"></script>
<script type="text/javascript" src="static/js/view-spec/search-view.js"></script>

<!--tags input-->
<script src="libraries/frontend/tags-input/tagsinput.min.js"></script>
<link rel="stylesheet" type="text/css" href="libraries/frontend/tags-input/tagsinput.css" />

<script type="text/javascript" src="libraries/frontend/tagcloud/jquery.tagcloud.js"></script>
<script src="libraries/frontend/deck.js/extensions/hash/deck.hash.tree.js"></script>
<script type="text/javascript">

$.getJSON("./?url=ajax/getTagCloudJSON&callback=?", function(data) {  
		//create list for tag links  
		$("<ul>").attr("id", "tagList").attr('class', 'inline-block').appendTo("#tagCloud");  
	  
		//create tags  
		$.each(data.tags, function(i, val) {  
	  
			//create item  
			var li = $("<li>&nbsp;</li>");  
	  
			//create link  
			$('<span class="hideme">,</span>').prependTo(li);
			$("<a>").text(val.tag).attr({rel:val.freq, title:"See all pages tagged with " + val.tag, href:"javascript:setTag('deck','"+val.tag+"')", class:"tag_in_cloud"}).prependTo(li);
			//add to list  
			li.appendTo("#tagList");  
		});
		$('#tagList li a').first().prepend('&nbsp;');
                $.fn.tagcloud.defaults = {
                    size: {start: 9, end: 18, unit: 'pt'},
                    color: {start: '#4C78A4', end: '#8B0000'}
               };
               $('#tagList li a').tagcloud();
               $('#tags_mirror').tagsInput({
                   'height' : 'auto',
                   'interactive' : false,
                   'onRemoveTag' : function(){ submitSearch('deck','1'); }
               }); 
               $('#tags_mirror_tagsinput').css('border','none');
               $('#tags_mirror_tagsinput').hide();
});

$(function(){
	submitSearch('<?php echo $typeOfSearch?>','<?php echo $page?>');
});

</script>

<h1 class="hideme">Search in SlideWiki</h1>   

<form name="searchForm" id="searchForm" action="./" method="get" class="well form-stacked">
	<label for="keywords" class="hideme">Search SlideWiki for</label>        
	<input type="hidden" name="url" value="search/view" />
        <input type="hidden" name="selected_id" value="">
	<input type="hidden" name="order" value="<?php echo $order ?>" />
        <input type="hidden" name="own" value="" />
        <input type="hidden" name="language" value="<?php echo $language ?>" />
	<input type="text" name="keywords" id="keywords" value="<?php echo $keywords ?>" class="xlarge span13"  />
	<input type="button" value="Search" class="btn span2" onclick="submitSearch('<?php echo $typeOfSearch?>','<?php echo $page?>')" />
</form>

<div class="content row">
    <div class="searchList">
        <ul class="tabs" data-tabs="tabs">
            <li class="active">
                <a href="#decks" id="deckslink"><span>Presentations</span></a>
            </li>
            <li>
                <a href="#slides" id="slideslink"><span>Slides</span></a>
            </li>					
        </ul>

        <div class="tab-content">
            <div id="decks" class="active">
                <div class="span-two-thirds" style="float:left">
                    <h2>Presentations ordered by <span class="order"><?php echo $order?></span></h2>
                    <div id="searchResultsDecks"></div>
                    <script id="search_decks" type="text/x-jquery-tmpl">
                         
                        <select id="language-select" style="padding:5px !important; margin-bottom: 18px;">
                            <option>${current}</option>
                                {{each languages}}            
                                    <option style="cursor:pointer" language="${$value.db}" onclick="setLanguage('deck',$(this).attr('language'))">${$value.name}</option>
                                {{/each}}
                        </select>
                        {{html pager_code}}
                        
                        <ol class="deckOverviewList" style="clear:both">
                        {{each $data.decks}}
                                
                            <li class="deckOverviewItem">
                                <div>
                                    <div>
                                        <h3 class="deckOverviewTitle"><a href="deck/${$value.id}_${$value.slug_title}">${$value.title} (${$value.number_of_slides} slides)</a></h3>

                                    </div>
                                    <div class="deck-container deck-menu" style="padding:0">
                                    {{each(i, slide) $value.slides}}                      
                                            <div class="slide" onclick="goToSlide(this.id)" id="deck/${slide.deck.id}_${$value.slug_title}#tree-${slide.deck}-slide-${slide.id}-${slide.position}-view">
                                                {{html slide.thumbnailContent}}
                                            </div>
                                    {{/each}}
                                    </div> 
                                </div>
                            </li>
                        {{/each}}
                        </ol>
                    </script>
                </div>
                <div class="span-one-third" style="float:left; padding-left:15px;"">
                    <h3>Order results</h3>
                    <ul class="unstyled">
                            <li><a class="label" onclick = "setOrder('deck','title')">by title</a></li>
                            <li><a class="label" onclick = "setOrder('deck','date')">by date</a></li>
                            <li><a class="label" onclick = "setOrder('deck','popularity')">by popularity</a></li>
                            <li><a class="label" onclick = "setOrder('deck','featured')">by featured</a></li>
                    </ul>
                    <div id="tagCloud">
                        <h3>Tag Cloud</h3>
                        <div id="tags_mirror" class="input" style="display:none;"></div>    
                    </div>
                </div>
            </div>	

                <div id="slides">
                    <div class="span-two-thirds" style="float:left; ">
                        <h2>Slides ordered by <span class="order"><?php echo $order?></span></h2>
                        <div id="searchResultsSlides"></div>
                        <script id="search_slides" type="text/x-jquery-tmpl">
                            <select name="language_slides" style="padding:5px !important; margin-bottom: 18px;">
                                <option>${current}</option>
                                    {{each languages}}            
                                        <option style="cursor:pointer" language="${$value.db}" onclick="setLanguage('slide',$(this).attr('language'))">${$value.name}</option>
                                    {{/each}}
                            </select>
                            {{html pager_code}}
                            <ul class="deckOverviewList" style="clear:both">
                                <li class="deckOverviewItem">
                                    <div class="deck-menu deck-container" style="width:100% !important; padding: 0 !important">
                                        {{each slides}}
                                            <div class="slide"  id="deck/${$value.deck.id}_${$value.deck.slug_title}#tree-${$value.deck.id}-slide-${$value.id}-${$value.position}-view" onclick="goToSlide(this.id)" style = "font-size:0.22em; height: 65px; padding: 5px 1%; margin: 0 1% 5px 0; position: relative;">
                                                {{html $value.thumbnailContent}}
                                            </div>
                                         {{/each}}
                                    </div>
                                </li>
                            </ul>
                        </script>
                    </div>
                    <div class="span-one-third" style="float:left; padding-left:15px;">
                        <h3>Order results</h3>
                        <ul class="unstyled">
                                <li><a class="label" onclick = "setOrder('slide','title')">by title</a></li>
                                <li><a class="label" onclick = "setOrder('slide','date')">by date</a></li>
                                <li><a class="label" onclick = "setOrder('slide','popularity')">by popularity</a></li>
                        </ul>
                    </div>

                </div>

        </div>
    </div>	
</div>	


	
		

		
	</div>
</div>
