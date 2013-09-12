
<script type="text/javascript" src="libraries/frontend/jquery-tmpl/jquery.tmpl.min.js"></script>
<script src="libraries/frontend/deck.js/extensions/hash/deck.hash.tree.js"></script>
<script type="text/javascript" src="static/js/view-spec/search-view.js"></script>

<script type="text/javascript">
    
function appendItemById(title,id) {
	var selected_id = '<?php echo $selected_id; ?>';
	var type = '<?php echo $typeOfSearch; ?>';
	appendExistingItemByRevisionId(id,type,title,selected_id);
}
$("form").submit(function() {
	return false;

})
$("formOwn").submit(function() {
	return false;

})

$("input[type=edit][name=keywords]").keyup(function(event) {
	if(event.keyCode == '13')
		submitSearch('<?php echo $typeOfSearch?>','<?php echo $page?>');
})

</script>

<div style="padding:10px;">
<?php 

if ($selected_id!='0') { ?>	
	<a class="close" style="cursor:pointer" onclick="$('#search_modal').modal('hide')">x</a>
	<h2>Appending <?php echo $typeOfSearch?></h2>
<?php } ?>

<section >
    <form name="searchForm" id="searchForm" style="float:left; margin-right:5px;">
        <input type="hidden" name="type" value="<?php echo $typeOfSearch; ?>">
        <input type="hidden" name="language" value="<?php echo $language; ?>">            
        <input type="hidden" name="order" value="" />
        <input type="hidden" name="selected_id" value="<?php echo $selected_id; ?>">
        <input type="edit" name="keywords" value="<?php echo $keywords ?>" >
        <input type="button" value="Search" language="" onclick="submitSearch('<?php echo $typeOfSearch?>','<?php echo $page?>')">
        <br/>
        <input type="checkbox" name="own" id="own">Search only own items
    </form>
    <div id="searchResultsDecks"></div>
    <div id="searchResultsSlides"></div>
    
</section>


    <script id="search_decks_ex" type="text/x-jquery-tmpl">
        <select style="padding:5px !important;">
            <option>${current}</option>            
                {{each languages}}            
                    <option style="cursor:pointer" language="${$value.db}" onclick="setLanguage('<?php echo $typeOfSearch?>',$(this).attr('language'))">${$value.name}</option>
                {{/each}}
            </select>
        <div>
            {{html pager_code}}
        </div>
        
        <ol class="deckOverviewList" style="clear:both">
        {{each decks}}
            <li class="deckOverviewItem">
                <div>
                    <div style="width:100%"><div style="float:left">
                        <h3 class="deckOverviewTitle">
                         ${$value.title}   
                         (${$value.number_of_slides} slides)
                                                        
                        </h3>
                        </div>
                        {{if $value.loop=='no'}}
                        <div class="addButton" style="display:none; float:left;"><input type="button" id="${$value.id}" name="add${$value.id}" value="Add" onclick="appendItemById('${$value.title}',this.id)"></div>
                        {{else}}
                        <div style="float:left" >This insertion can't be done because it leads to endless loop</div>
                        {{/if}}
                        <div align="right"><a id = "link_view_slide_${$value.id}" style="cursor:pointer" onclick ="open_slide_view(${$value.id})" >
                            View slides
                        </a></div>
                    </div>
                    <div class="deck-container deck-menu" id="slide_viewer_${$value.id}" style="display:none; padding:0; width:100% !important;">
                    {{each $value.slides}}                                                            
                            <div class="slide" onclick="goToSlide(this.id)" id="./deck/${$value.deck.id}_${$value.deck.slug_title}#tree-${$value.deck.id}-slide-${$value.id}-${$value.position}-view">
                                {{html $value.thumbnailContent}}
                            </div>
                    {{/each}}
                    </div> 
                </div>
            </li>
        {{/each}}
        </ol>
    </script>
    
    <script id="search_slides_ex" type="text/x-jquery-tmpl">
        
        <select name="language_slides" style="padding:5px !important;">
            <option>${current}</option>
            {{each languages}}            
                <option style="cursor:pointer" language="${$value.db}" onclick="setLanguage('<?php echo $typeOfSearch?>',$(this).attr('language'))">${$value.name}</option>
            {{/each}}
        </select>

        {{html pager_code}}
        
        <ul class="deckOverviewList" style="clear:both">
            <li class="deckOverviewItem">
                <div class="deck-menu deck-container" style="width:100% !important; padding: 0 !important">
                    {{each slides}}
                        <div class="slide"  id="./deck/${$value.deck.id}_${$value.deck.slug_title}#tree-${$value.deck.id}-slide-${$value.id}-${$value.position}-view" onclick="goToSlide(this.id)" style = "font-size:0.22em; height: 65px; padding: 5px 1%; margin: 0 1% 5px 0; position: relative;">
                            {{html $value.thumbnailContent}}
                        </div>
                        <div class="addButton" style="float:left; font-size:10pt"><input type="button" id="${$value.id}" name="add${$value.id}" value="Add" onclick="appendItemById('${$value.title}',this.id)"></div>
                    {{/each}}
                </div>
            </li>
        </ul>
    </script>
</div>
