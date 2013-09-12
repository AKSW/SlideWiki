<!-- dynamic theme style -->
<link id="page_css" rel="stylesheet" href="<?php echo isset($style) ? 'ajax/css/'.$style : 'ajax/css/'.$deck->default_theme ?>"> 
<link id="page_transition" rel="stylesheet" href="<?php echo isset($transition)? 'ajax/transition/css/'.$transition :'ajax/transition/css/'.$deck->default_transition; ?>"> 

<!--tags input-->
<script src="libraries/frontend/tags-input/tagsinput.min.js"></script>
<link rel="stylesheet" type="text/css" href="libraries/frontend/tags-input/tagsinput.css" />

<link rel="stylesheet" href="static/css/deck.css" type="text/css" media="all" />
<!-- scripts specific to the view -->
<script src="static/js/view-spec/deck.js"></script>
<script src="static/js/scale.js"></script>
<script src="static/js/questions.js"></script>
<!-- Searchable drop down list -->   
<link rel="stylesheet" href="libraries/frontend/chosen/chosen.css" type="text/css" media="all" />
<script src="libraries/frontend/chosen/chosen.jquery.min.js"></script> 
<script>
	var style="<?php echo $style;?>";
	if (style=="")
		style=$('#selected_style').val();
	var transition="<?php echo $transition;?>";
	if (transition=="")
		transition=$('#selected_transition').val();	
	var style_address="<?php echo $style?'ajax/css/'.$style:'libraries/frontend/deck.js/themes/style/web-2.0.css'; ?>";
	var deck="<?php echo $deck->id;?>";
	//a golbal var for preventing multiple slide load
	var is_deck_loaded=false;
	//list of changes on slides title,body or note
	var item_change=new Array();
	//global variable for step by step loading of slides
	var loaded_range=new Array();
	var all_slides=new Array();
	var minor_changes=new Array();
	var findreplace_results=new Array();
        
</script>
<!-- main script for decks -->
<script type="text/javascript" src="static/js/main.js"></script>
<script src="libraries/frontend/jquery.hotkeys.js"></script>
<script src="libraries/frontend/deck.js/extensions/hash/deck.hash.tree.js"></script>
<script type="text/javascript" src="libraries/frontend/jquery-tmpl/jquery.tmpl.min.js"></script>
<!-- Includes editing libraries only if user is logged in to system -->
<?php if ($user['is_authorized']): ?>
<!-- ALOHA inline editor -->
<link rel="stylesheet" href="libraries/frontend/alohaeditor/aloha/css/aloha.css" type="text/css">
<script src="static/js/aloha-config.js" language="javascript"></script>
<script src="static/js/translation.js" language="javascript"></script>
<script src="libraries/frontend/alohaeditor/aloha/lib/aloha.js" data-aloha-plugins="common/format,common/align,common/highlighteditables,common/list,common/link,common/undo,common/block,common/characterpicker,common/table,common/imageManager,common/no-translate,common/sourceManager,common/easyQuestion"></script>
<?php endif; ?>
<!-- Code Mirror -->
<link rel="stylesheet" href="libraries/frontend/codemirror/lib/codemirror.css">
<script src="libraries/frontend/codemirror/lib/codemirror.js"></script>
<script src="libraries/frontend/codemirror/mode/javascript/javascript.js"></script>
<script src="libraries/frontend/codemirror/mode/xml/xml.js"></script>
<script src="libraries/frontend/codemirror/mode/htmlmixed/htmlmixed.js"></script>
<script src="libraries/frontend/codemirror/mode/css/css.js"></script>
<script src="libraries/frontend/codemirror/mode/php/php.js"></script>
<script src="libraries/frontend/codemirror/mode/ntriples/ntriples.js"></script>
<script src="libraries/frontend/codemirror/mode/sparql/sparql.js"></script>
<script src="libraries/frontend/codemirror/lib/util/formatting.js"></script>
<!-- Code Mirror for deck.js -->
<link rel="stylesheet" href="libraries/frontend/deck.js/extensions/codemirror/deck.codemirror.css">
<script src="libraries/frontend/deck.js/extensions/codemirror/deck.codemirror.js"></script>
<!-- Search and highlight -->
<script src="libraries/frontend/jquery.highlight.js"></script>

<!--activity stream scripts-->
<?php require_once (ROOT . DS . 'application' . DS . 'views' . DS . 'activity_templates.php'); ?>

<form id="svg_form_form" action="libraries/frontend/svg-edit/svg-editor.php" method="post" target="_blank">
  <input id="svg_code" name="svg_code" type="hidden"/>
  <input id="svg_codeid" name="svg_codeid" type="hidden" />
</form>
<!-- the purpose of slide_revision_status is to show exclamation mark when a new revision is available -->
<script id="slide_revision_status" type="text/x-jquery-tmpl">
	{{each slides}}
	<div id="tree-${$value.deck.id}-slide-${$value.id}-${$value.position}-hasNewRevision">
		{{if $value.last_revision_id==$value.id}}0{{else}}1{{/if}}	
	</div>
	{{/each}}
</script>
<div style="display:none;" id="tmp_preserved_area"></div>
<div style="display:none;" id="after_refresh_node"></div>
<div style="display:none;" id="slides_revision_status"></div>
<div id="modal_dialog1" class="modal hide fade">
    <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3>New revision alert</h3>
    </div>
    <div class="modal-body">
<p style="text-align:justify;">You are not currently in the editors list of this deck. Your changes to this slide might create a new revision of the deck. Please select one the following actions.</p>
<ul>
<li>
<b>Perform minor edit</b>: <small>Select this option if you just want to fix a typo or contribute a small piece to this individual slide. The original author will be notified and can directly incorporate your revision.</small>
</li>
<li>
<b>Apply for editorship</b>: <small>Select this option if you would like to contribute several changes or regularly to this presentation. The current editor will be notified and can grant you complete editing rights on this presentation. Until the original editor grants you editor permission, you can already add minor edits.</small>
</li>
<li>
<b>Create a new revision</b>: <small>This option will create a copy of this deck just for you - select this option, if you plan to substantially change the presentation. This will create a new fork/branch of this presentation. Please consider carefully whehter this is required, since merging changes back into the original revision is not (yet) well supported.</small>
</li>
</ul>
 <br/> 
    <a class="btn" id="dialog_minor_edit">Perform a minor edit</a>
    <a class="btn success" id="dialog_apply_editorship">Apply for editorship</a>
    <a class="btn info" id="dialog_new_revision">Create a new revision</a>
    <a class="btn primary" id="dialog_cancel">Cancel</a>
    </div>
</div>
<div id="modal_dialog_editorship" class="modal hide fade">
    <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3>Confirmation</h3>
    </div>
    <div class="modal-body">
<p style="text-align:justify;">Your editorship request sent to the owner of the corresponding deck.
</p>
    </div>
	<div class="modal-footer">
		<a class="btn" id="dialog_editorship_minor_edit">Perform a minor edit</a>
		<a class="btn success" onclick="$('#modal_dialog_editorship').modal('hide');">OK</a> 
	</div>      
</div>
<div id="modal_findreplace" class="modal hide fade">
    <div class="modal-header">
    <button type="button" class="close" onclick="$('#modal_findreplace').modal('hide');$('.tree-node-highlighted').removeClass('tree-node-highlighted');$('.slide').removeHighlight();" aria-hidden="true">&times;</button>
    <p><b>Find and Replace --</b> "<span id="findreplace_node_title"></span>"</p>
    </div>
    <div class="modal-body">
    	<div class="row" id="row_find_term"><div class="span2" style="text-align:right;">Find what</div><div class="span3"><input type="text" id="findreplace_term" class="input span3 search-query" placeholder="Search"/></div><div class="span3" style="text-align:left;"><a class="btn success find-next-btn">Find Next</a> <a class="btn success find-prev-btn" style="display:none;"> Find Previous</a></div></div>
    	<div class="row"><div class="span2" style="text-align:right;">Replace with</div><div class="span3"><input type="text" id="findreplace_replace" class="input span3 search-query" placeholder="Replace"/></div><div class="span3" style="text-align:left;"><a class="btn info" onclick="find_replace();">Replace</a><a class="btn primary" onclick="find_replace_all();">Replace All</a></div></div>
    	<input type="hidden" id="findreplace_node"/>
    	<div class="row"><div class="span5" style="text-align:right;"><div id="find_result_no"></div></div></div>
    </div>  
	<div class="modal-footer">
		<div><a class="btn" onclick="$('#modal_findreplace').modal('hide');$('.tree-node-highlighted').removeClass('tree-node-highlighted');$('.slide').removeHighlight();">Cancel</a></div> 
	</div>       
</div>
<div id="modal_dialog_minoredit" class="modal hide fade">
    <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3>Confirmation of Minor Changes</h3>
    </div>
    <div class="modal-body">
<p style="text-align:justify;">Your minor changes have been saved. The owner of the deck will check the changes for confirmation.
</p>
    </div>
	<div class="modal-footer">
		<a class="btn success" onclick="$('#modal_dialog_minoredit').modal('hide');">OK</a> 
	</div>    
</div>
<div id="modal_html_code" class="modal hide fade in" style="display: none;">
	<div class="modal-header">
		<a class="close pointer-cursor">×</a>
			<h3>HTML Code</h3>
	</div>
	<div class="modal-body slide-code-modal">
		<div class="input">
			<textarea id="html_source_code" class="span8">HTML source code</textarea>
		</div>
	</div>
	<div class="modal-footer">
		<a class="btn" onclick="$('#modal_html_code').modal('hide');">Close</a><a class="btn info btn-small" onclick="revert_slide_changes();"><i class="icon-repeat"></i> Revert Changes</a> <!--a class="btn primary" onclick="apply_source_code()">Apply</a--> 
	</div>
</div>
<div id="modal_math_code" class="modal hide fade in" style="display: none;">
	<div class="modal-header">
		<a class="close pointer-cursor">×</a>
			<h3>MathJax</h3>
	</div>
	<div class="modal-body">
		<div class="input">
			<textarea id="math_source_code" class="span8">\[ your LaTeX Code \]</textarea>
		</div>
		<span onclick="previewMathCode();" style="cursor:pointer;color:green;">Click here to Preview</span>
		<div class="input">
			<div id="math_source_preview" class="span8"></div>
		</div>		
	</div>
	<div class="modal-footer">
		<a class="btn primary" onclick="insert_math_code()">Insert</a>
	</div>
</div>
<div id="modal_code_snippet" class="modal hide fade in" style="display: none;">
	<div class="modal-header">
		<a class="close pointer-cursor">×</a>
			<h3>Code Snippet</h3>
	</div>
	<div class="modal-body">
		<div class="clearfix">
			<div class="input">
				<select id="coding_mode"  class="span2">
						<option value="htmlmixed">HTML</option>
						<option value="css">CSS</option>
						<option value="javascript">JavaScript</option>
						<option value="xml">XML</option>
						<option value="php">PHP</option>
						<option value="ntriples">N-Triples</option>
						<option value="sparql">SPARQL</option>
				</select>
			</div>			
		</div>		
		<div class="clearfix">
			<div class="input">
				<textarea id="source_code_snippet" class="span8"></textarea>
			</div>
		</div>		
	</div>
	<div class="modal-footer">
		<a class="btn primary" onclick="insert_code_snippet();">Insert</a>
	</div>
</div>
<div id="modal_share_link" class="modal hide fade in" style="display: none;">
	<div class="modal-header">
		<a class="close pointer-cursor">×</a>
			<h3>Share Link</h3><span id="share_link_title"></span>
	</div>
	<div class="modal-body">
		<div class="clearfix">
			<div class="input">
				<input onclick="updateShareLinks(1)" type="radio"  name="selected_share_link" value="1" checked> <input type="text" id="link_current_item"  value="" class="span5"  style="cursor:text;" onclick="this.focus();this.select();" readonly="readonly" /><span class="help-inline">Link to the current item</span>
			</div>
		</div>
		<div class="clearfix">
			<div class="input">
				<input onclick="updateShareLinks(2)" type="radio"  name="selected_share_link" value="2" > <input type="text" id="link_latest_item"  value="" class="span5" style="cursor:text;" onclick="this.focus();this.select();" readonly="readonly" /><span class="help-inline">Link to the latest revision of this item</span>
			</div>
		</div>	
		<div class="clearfix">
			<div class="input">
				<input onclick="updateShareLinks(3)" type="radio"  name="selected_share_link" value="3"> <input type="text" id="link_user_latest_item"  value="" class="span5" style="cursor:text;" onclick="this.focus();this.select();" readonly="readonly" /><span class="help-inline">Link to your latest revision of this item</span>
			</div>
		</div>	
	</div>
	<div class="modal-footer">
		<a href="" id="facebook_share_link" target="_blank"><img src="static/img/facebook_share.png" alt="Share on Facebook"></a>
		<a href="" id="google_share_link" target="_blank"><img src="static/img/google_share.png" alt="Share on Google+"></a>
		<a href="" id="linkedin_share_link" target="_blank"><img src="static/img/linkedin_share.png" alt="Share on LinkedIn"></a>
		<a href="" id="twitter_share_link" target="_blank"><img src="static/img/twitter_share.png" alt="Share on Twitter"></a>
	</div>	
</div>

<div id="compare_revisions" class="modal hide" style="width:800px !important; height:600px !important">
    <div class="modal-header">
	<a class="close pointer-cursor">×</a>
	<h3>Compare decks</h3>
    </div>
    <div class="modal-body" style="max-height:none !important;">        
    </div>
</div>
<div id="exportToSco" class="modal hide">
    <div class="modal-header">
	<a class="close pointer-cursor">×</a>
	<h3>Download a deck as a SCORM package</h3>
    </div>
    <div class="modal-body" style="max-height:none !important;">
        <form class="form-stacked" action="./">
            <legend> Choose a format:</legend>
            <input type="hidden" id="deck_id" name="deck_id" value="">
            <input type="hidden" id="url" name="url" value="export/toSCO">
            <input type="radio" name="format" value="scorm12" checked>Scorm 1.2<br>
            <input type="radio" name="format" value="scorm2004_2nd">Scorm 2004 2nd Edition<br>
            <input type="radio" name="format" value="scorm2004_3rd">Scorm 2004 3rd Edition<br>
            <input type="radio" name="format" value="scorm2004_4th">Scorm 2004 4th Edition<br>
            <div class="actions">
                <button type="submit" class="success btn">Download</button>
            </div>
        </form>
    </div>
</div>
<div id="breadcrumb" style="z-index:10;"></div>

<header class="page-header row">
		<div id="deck_title_span" class="span13" style="position:relative;"><?php if ($deck->last_revision_id!=$deck->id):?><span class="yellow-bar"><a href="deck/<?php echo $deck->last_revision_id . '_' . $deck->slug_title;?>" title="Go to the latest revision of this deck"><img src="static/img/yellow_bar.gif" onmouseover="$(this).addClass('yellow-bar-over');" onmouseout="$(this).removeClass('yellow-bar-over');"/></a></span><?php endif; ?>
			<h1 class="deck_header_link"><a class="pointer-cursor" onclick="selectNode('<?php echo "tree-0-deck-".$deck->id."-1"?>');">
			
			<span class="r_entity r_creativework" itemscope itemtype="http://schema.org/CreativeWork">
				<span class="r_prop r_name" itemprop="name"><?php echo $deck->title?$deck->title:'Untitled';?></span>
					<meta itemprop="description" content="<?php echo $deck->description;?>" />
					<meta itemprop="url" content="http://slidewiki.org/deck/<?php echo $deck->id .'_'. $deck->slug_title;?>" />
					<meta itemprop="dateCreated" content="<?php $tmp=explode(' ',$deck->revisionTime);echo $tmp[0];?>" />
					<meta itemprop="inLanguage" content="<?php echo join("-",$deck->language);?>" />
					<meta itemprop="keywords" content="<?php echo join(",",$deck->getTags($deck->id));?>" />
					<span itemprop="author" itemscope itemtype="http://schema.org/Person">
						<meta itemprop="name" content="<?php echo $deck->owner->username;?>" />
						<meta itemprop="url" content="http://slidewiki.org/user/<?php echo $deck->owner->id;?>" />
					</span>
			</span>
			</a></h1>
		</div>
    
               <div id="current_language" style="display:<?php if ($deck->language['id']!=''): echo 'block'; else: echo 'none'; endif;?>" lang="<?php echo $deck->language['id'];?>">
                    
              <ul class="nav">
                <li id="language_list_trigger" class="dropdown">
                    <a style="cursor: pointer;" id="get_all_translations_link" onclick="getAllTranslations()"><span id="current_language_name">Language: <b><?php echo $deck->language['name'];?></b></span><i id="lang_icon" class="icon-chevron-down"></i></a>
                    <ul aria-labelledby="drop4" role="menu" class="dropdown-menu" id="languages_list"></ul>
              </li>
             </ul>
                   
                   <div id="languages_list" class="modal" style="display:none;"></div>
               </div>
                
                <div id="current_language_null" style="display:<?php if ($deck->language['id']=='' && $user['is_authorized']): echo 'block'; else: echo 'none'; endif;?>" lang="">
                    <a style="cursor: pointer;" onclick="getLanguagesList('#select_language')">Set language</a>
                </div>     
                
                <!-- Select language table for decks without language -->
                <script id="select_language" type="text/x-jquery-tmpl">
                    
                    <div style="float:left;">
                     <table>
                    {{each languages}}
                        <tr><td class="" name="" style="cursor:pointer" onclick="setDeckLanguage(this.id, <?php echo $deck->id;?>)" id="${$value.language}">${$value.name}</td></tr>
                        {{if ($index + 1) % 10 == 0}}
                            </table></div><div style="float:left"><table>
                        {{/if}}
                    {{/each}}
                    </table></div>
                </script>
                
                 <!-- Table with existed translations and Translate to... button -->
                <script id="existed_translations_deck" type="text/x-jquery-tmpl">
                    <div id="existed">
                        <div class="close" onclick="$('#language_list_trigger').removeClass('open')">x</div>
                    {{each languages}}
                    <li role="presentation">
                    {{if $value.first && $value.first != $value.last }}
                    <a role="menuitem" title="The parent translation was changed!" style="cursor:pointer" onclick="goToTranslation(${$value.first})" id="${$value.language['id']}">${$value.language['name']}
                    <img src="/static/img/exclamation_b.gif" alt="The parent translation was changed!">
                    {{else}}
                    <a role="menuitem" style="cursor:pointer" onclick="goToTranslation(${$value.last} + '_' + '${$value.slug_title}')" id="${$value.language['id']}">${$value.language['name']}
                    {{/if}}
                        </a></li>
                    {{/each}}
                    <?php if ($user['is_authorized']): ?>
                    <li class="divider"></li>
                    <li id="translate_button" style="clear:both; width:100%; text-align: center"><a style="cursor:pointer" onClick="getLanguagesList('#lang_translation')"><img  style="vertical-align: middle; margin-right: 5px;" src="static/img/translate_button.png">Translate</a></li>
                    <?php endif;?>
                </script>
                
                <script id="existed_translations_slide" type="text/x-jquery-tmpl">
                    <div id="existed">
                        <div class="close" onclick="$('#language_list_trigger').removeClass('open')">x</div>                        
                    {{each languages}}
                    <li role="presentation"><a role="menuitem" style="cursor:pointer" onclick="goToTranslation('${$value.last}'+'#'+'${$value.slideLink}')" id="${$value.language['id']}">${$value.language['name']}</a></li>
                    {{/each}}                   
                </script>
                
                <!-- Select language table for translate function -->
                <script id="lang_translation" type="text/x-jquery-tmpl">
                    <div id="languages_to_translate">
                    <div style="float:left;"><table>
                    {{each languages}}
                        <tr><td class="" name="" style="cursor:pointer" onclick="translateDeck(this.id)" id="${$value.language}">${$value.name}</td></tr>
                        {{if ($index + 1) % 10 == 0}}
                            </table></div><div style="float:left"><table>
                        {{/if}}
                    {{/each}}
                    </table></div></div>
                </script>                    
                <div id="deck_follow_status" class="span2">
					<?php if ($user['is_authorized'] && $user['id']!=$deck->owner->id && $isFollowing): ?>
					<a class="btn mini danger" onclick="follow($(this),'deck',<?php echo $deck->deck_id;?>)">Unfollow deck</a>
					<?php endif; ?>
					<?php if ($user['is_authorized'] && $user['id']!=$deck->owner->id && !$isFollowing): ?>
					<a class="btn mini success" onclick="follow($(this),'deck',<?php echo  $deck->deck_id;?>)">Follow deck</a>
					<?php endif; ?>
		</div>   
</header>
<section class="row" id="deck-main-content">
	<section class="span-one-third" id="control-elements">
		<header> <!-- theme selector -->
			<form>
			<table>
			<tr><td>
			
				Theme <?php if($user['is_authorized']){?>&nbsp;(<a href="style/<?php echo $style; ?>/deck/<?php echo $_GET['deck']; ?>">Edit</a>)<?php }?>
				<select id="selected_style" name="style" onchange="changeDeckStyle();" class="input-small" style="width: 140px">
					<?php
						foreach($styles as $r) {
							echo('<option ');
							if($r['id']==$style){
								echo('selected ');
							}
							echo(' value="'.$r['id'].'" '.'>'.$r['name']);
							echo('</option>');
						}
					?>
				</select>
				</td><td>
				Transition
				<select id="selected_transition" name="transition" onchange="changeDeckTransition();" class="input-small" style="width: 130px">
					<?php
						foreach($transitions as $r) {
							echo('<option ');
							if($r['id']==$transition){
								echo('selected ');
							}
							echo(' value="'.$r['id'].'" '.'>'.$r['name']);
							echo('</option>');
						}
					?>
				</select></td><td>&nbsp;			
				
			</td></tr>
			</table>
			</form>
                    </header>
		<div style="text-align:right;" id="tree_tools">
			<small>[<span id="tree_collapse" title="Collapse All" class="collapse-nodes" onclick="$('#tree').jstree('close_all');">Collapse all</span>]</small>
			<small>[<span id="tree_expand" title="Expand All" class="expand-nodes" onclick="$('#tree').jstree('open_all');">Expand all</span>]</small>
			<small>[<span id="tree_scroll" title="Enable/Disable Scrollbar for Navigation" class="expand-nodes" onclick="toggleScrollToTree();">Scrollable</span>]</small>
			&nbsp;<a title="search within slides" onclick="showSearchInput();" style="cursor:pointer;"><img src="static/img/search.png" alt="Search"/></a>
		</div>
		<nav id="tree"></nav>
	</section> <!-- End of #control-elements section -->

	<section class="span-two-thirds" style="z-index:5;"> <!-- content section - tabs and tab content here -->
		<header>
			<nav>
				<ul class="tabs" data-tabs="tabs" id="item_tabs">
					<li class="active"><a href="#view" id="viewlink"><span>View</span></a></li>
					<li><a href="#edit" id="editlink"><span>Edit</span></a></li>
					<li><a href="#discussion" id="discussionlink"><span>Discussion</span></a></li>
					<li><a href="#questions" id="questionslink"><span>Questions</span></a></li>
                                        <li><a href="#history" id="historylink"><span>History</span></a></li>
					<li><a href="#usage" id="usagelink"><span>Usage</span></a></li>
					<li><a href="playSync/deck/<?php echo $deck->id;?>" id="playSlide" target="_blank" title="Play Slides" class="btn mini"><i class="icon-play"></i></a></li>
					<li><a href="#" id="downloadDeck" title="Download Deck" class="btn mini"><i class="icon-download-alt"></i></a></li>
                                        <li><a style="cursor:pointer" onclick="openSCOModal(<?php echo $deck->id;?>)" id="toSco" title="Download as SCO" class="btn mini"><b>SCO</b></a></li>
					<li><a href="#" id="printDeck" target="_blank" title="Print Deck" class="btn mini"><i class="icon-print"></i></a></li>
					<li><a id="fullscreen_button" onclick="show_fullscreen();" title="Fullscreen" class="fullscreen btn mini pointer-cursor"><i class="icon-resize-full"></i></a></li>
					<li><a id="shareLink" title="Share Link" class="btn mini"><i class="icon-bullhorn"></i></a></li>
					<li><a class="pointer-cursor" id="saveToolbar" onclick="save_changes();"><img id="save_changes_button" title="Save (CTRL+S)" src="static/img/save_btn.png" alt="Save changes"></a></li>
				</ul>
			</nav>
                        <div id="search_modal" class="modal" style="display:none; overflow: scroll !important; height:600px; "></div>
		</header>			
		<div id="license_bar"><a href="http://creativecommons.org/licenses/by-sa/2.0/" target="_blank" title="CC BY SA License" onmouseover="$('#license_bar').css('opacity',1);" onmouseout="$('#license_bar').css('opacity',0.6);"><img src="static/img/ccbysa.png" alt="CC BY SA License"/></a>
		</div> 
		<div id="additional-elements"></div> 
		<section class="tab-content">
			<!-- View Tab -->
			<div id="view" class="active"> 
				<div id="deck-view">
				<?php if(!$user['is_authorized']){?>
       				<div id="edit_login_alert" class="alert-message fade in warning" data-alert="alert">
					<a class="close pointer-cursor">×</a>
					<p>Everybody can edit all content on SlideWiki. Please <a href="#login-register-modal" data-controls-modal="login-register-modal" data-backdrop="true" data-keyboard="true">login or register </a> to edit slides!</p>
				</div>	 
				<?php }?> 
		<!-- Branding -->
		<?php if (count($brand)){?><div id="deck-brand" class="deck-brand-bar" style="display:none;"><a href="<?php echo $brand['url'];?>" id="deck-brand-url" target="_blank"><img id="deck-brand-image" class="deck-brand-image" src="<?php echo $brand['image'];?>" alt="brand" /></a> <span class="deck-brand-text" id="deck-brand-text"><?php echo $brand['text'];?></span></div><?php }?>				
				<div id="slideview" class="deck-container"></div>
                                <div style='float:right; margin:5px' id='deck_stream_button_div'>
                                    <button id='deck_stream_button' class='btn success mini' onclick='showShortDeckStream($(this).attr("deck_id"))' deck_id='<?php echo $deck->id;?>'>Show latest activity</button>
                                </div>
                                <div id="activity_stream" style='clear:both;'></div>                         
					
					<script id="deck_preview" type="text/x-jquery-tmpl">
                       {{if $data.translation_status=="translated"}}
                               <div class="google_banner" >The deck contains slides that were translated by <a href="http://translate.google.com">Google Translation</a> service</div>
                       {{/if}}
                       {{if $data.translation_status=="in_progress"}}
                               <div class="google_banner_wide">The deck is currently being translated and will be available in about ten minutes! Your changes to the current deck might be lost. See history for more details</div>
                       {{/if}}
						<div class="deck-contributors" style="display:none;">
									{{if description}}Original author:<br/><span id="original_bar">${description}</span><br/><br/>{{/if}}
									<small> {{if owner.id != initiator.id}}Initiator{{else}}Creator{{/if}}:</small><br/>
									<span class="avarat-text"><a href="user/${initiator.id}"><img src="./?url=ajax/getAvatarSrc&id=${initiator.id}" height="{{if description}}50{{else}}60{{/if}}" width="{{if description}}50{{else}}60{{/if}}" title="${initiator.username}" class="deck-owner-avatar">&nbsp;${initiator.username}</a></span><br/>
									{{if owner.id != initiator.id}}
										<br/><small> Creator of the revision: </small>	<br/>				   		
										<span class="avarat-text"><a href="user/${owner.id}"><img src="./?url=ajax/getAvatarSrc&id=${owner.id}" height="{{if description}}50{{else}}55{{/if}}" width="{{if description}}50{{else}}55{{/if}}" title="${owner.username}" class="deck-owner-avatar">&nbsp;${owner.username}</a></span><br/>
									{{/if}}
									{{if slide_contributors}}{{if slide_contributors.length>0}}
								  			 <br/><small> Contributors: </small></br>
											{{each(i, value2) slide_contributors}}
												{{if value2.split('|')[0]}}
														<span class="avarat-text"><a href="user/${value2.split('|')[0]}"><img src="./?url=ajax/getAvatarSrc&id=${value2.split('|')[0]}" height="40" width="40" title="${value2.split('|')[1]}" class="slide-contributor-avatar">&nbsp;${value2.split('|')[1]}</a></span><br/>
												{{/if}}
											{{/each}}
									{{/if}}{{/if}}
								{{if sources.length}}<br/><small>Sources (${sources.length})</small>
								{{if sources.length<11}}
									<ul>
									{{each(i, value2) sources}}
										<li class="slide-osource-item">
											${value2}
										</li>
									{{/each}}
									</ul>
								{{else}}
									<ul>
									{{each(i, value2) sources}}
										{{if i<9}}
											<li class="slide-osource-item">
												${value2}
											</li>
										{{/if}}
										{{if i==9}}
											<li class="slide-osource-item">
												${value2}
											</li>
											<li id="source-show-btn"><a class="btn mini" onclick="showInvSources(this);">Show more...</a></li>
										{{/if}}
										{{if i>9}}
											<li class="slide-osource-item" style="display:none;">
												${value2}
											</li>
										{{/if}}
									{{/each}}
									</ul>
								{{/if}}
								{{/if}}
						</div>
						<div class="deck-follow" style="display:none;">
								{{if current_user_id}}
									{{if current_user_id!=owner.id}}
										<a class="btn mini {{if is_followed_by_current_user==1}}danger{{else}}success{{/if}}" id="followItem_${item_name}_${id}" onclick="follow($(this),'${item_name}','${deck_id}')">
										{{if is_followed_by_current_user==1}}
												Unfollow ${item_name}
										{{else}}Follow ${item_name}
										{{/if}}
										</a>
									{{/if}}
								{{/if}}
						</div>
						<br/><span class="deck-title"><b>${title}</b></span><br/>
                        {{if abstract}}
						<div class="deck-abstract">
							<small>{{html abstract}}</small>
						</div>{{/if}}<br/>
						{{if description}}
						<div class="deck-description"><small>Original Author: <i>${description}</i></small></div><br/>
						{{/if}}
						{{if tags}}
						<div class="deck-tags"><small>Tags: {{html tags}}</small></div><br/>
						{{/if}}
                       {{each slides}}
							<div class="slide"  id="tree-${$value.deck.id}-slide-${$value.id}-${$value.position}-view" onclick="selectNode('tree-${$value.deck.id}-slide-${$value.id}-${$value.position}');hideOverlay();">{{html $value.thumbnailContent}}</div>
					   {{/each}}					
					</script>	
					<script id="deck_preview_partial" type="text/x-jquery-tmpl">
                       {{each slides}}
							<div class="slide"  id="tree-${$value.deck.id}-slide-${$value.id}-${$value.position}-view" onclick="selectNode('tree-${$value.deck.id}-slide-${$value.id}-${$value.position}');hideOverlay();">{{html $value.thumbnailContent}}</div>
					   {{/each}}	
					</script>
					<script id="slide_preview" type="text/x-jquery-tmpl">
						{{each slides}}
                                                    
							<div class="slide {{if $value.position==1}}{{if $value.deck.id==$data.id}}first-slide{{else}}first-sub-slide{{/if}}{{/if}}" id="tree-${$value.deck.id}-slide-${$value.id}-${$value.position}-view">
                                                         {{if $value.translation_status!="original"}}
                                                        
                                                            <div class="google_banner" id="google_banner_${$value.id}">
                                                                The slide was translated from ${$data.parent_language.name} by <a href="http://translate.google.com">Google Translation</a> service
                                                            {{if $value.translation_status=="revised"}}
                                                                    <button class="btn small danger remove" onclick="remove_google(${$value.id})">X</button>
                                                            {{/if}}    
                                                            </div>
                                                         
                                                        {{/if}}    
                                                        <!--  <span onclick="filterLatex(this.id)" id="${$value.id}"> Click me! </span> -->
                                                                <div class="slide-content"><div class="slide-scaler">
                                                                    
									<div class="slide-header">
									<h2>
										<div class="slide-title" id="slide_title_tree-${$value.deck.id}-slide-${$value.id}-${$value.position}">
											{{if $value.title}}{{html $value.title}}{{else}}<?php if($user['is_authorized']){?>« Click to add title »<?php }?>{{/if}}
										</div>
									</h2>
                                                                            
									</div>
									<div class="slide-body" id="slide_body_tree-${$value.deck.id}-slide-${$value.id}-${$value.position}" onclick="enableWYSISWYG(this)">
										{{if $value.body}}{{html $value.body}}{{else}}<?php if($user['is_authorized']){?><p>« Click to add text »</p><?php }?>{{/if}}
									</div>
								</div></div>
								<div class="slide-metadata">
									<hr>
										<div class="slide-description" style="{{if !$value.description}}display:none;{{/if}}">
											<small>Original Source: <div class="slide-original-source" {{if current_user_id}}{{if current_user_id==$value.initiator.id}}contenteditable onclick="showDescBtns('tree-${$value.deck.id}-slide-${$value.id}-${$value.position}-view')" {{else}}{{if $value.is_editable_by_current_user}}contenteditable onclick="showDescBtns('tree-${$value.deck.id}-slide-${$value.id}-${$value.position}-view')" {{/if}}{{/if}}{{/if}}>{{html $value.description}}</div>&nbsp;&nbsp;<a class="btn mini" style="display:none;" onclick="saveSlideOriginalSource('tree-${$value.deck.id}-slide-${$value.id}-${$value.position}-view',${$value.slide_id})"> Save</a><a class="btn mini" style="display:none;" onclick="cancelOriginalSource('tree-${$value.deck.id}-slide-${$value.id}-${$value.position}-view')"> Cancel</a></small>
										</div>
						   	 		<small>Speaker Notes:</small>
									<div class="slide-note" id="slide_note_tree-${$value.deck.id}-slide-${$value.id}-${$value.position}">
										{{if $value.note}}{{html $value.note}}{{else}}<?php if($user['is_authorized']){?><p>« Click to add note »</p><?php }?>{{/if}}	
									</div>
									{{if !$value.description}}
										{{if current_user_id}}{{if current_user_id==$value.initiator.id}}
											<small><a onclick="showSlideOriginalSource(this,'tree-${$value.deck.id}-slide-${$value.id}-${$value.position}-view');" style="cursor:pointer;" title="in case your slide is copied from another source, add the name or the related URL of the original source"> +Add original source</a></small>
										{{else}}{{if $value.is_editable_by_current_user}}
											<small><a onclick="showSlideOriginalSource(this,'tree-${$value.deck.id}-slide-${$value.id}-${$value.position}-view');" style="cursor:pointer;" title="in case your slide is copied from another source, add the name or the related URL of the original source"> +Add original source</a></small>
										{{/if}}
										{{/if}}{{/if}}
									{{/if}}
									<div class="slide-follow" style="display:none;">
									{{if current_user_id}}
										{{if current_user_id!=owner.id}}
											<a class="btn mini {{if $value.is_followed_by_current_user==1}}danger{{else}}success{{/if}}" id="followItem_${$value.item_name}_${$value.id}" onclick="follow($(this),'${$value.item_name}','${$value.slide_id}')">
											{{if $value.is_followed_by_current_user==1}}
												Unfollow ${$value.item_name}
											{{else}}Follow ${$value.item_name}
											{{/if}}
											</a>
										{{/if}}
									{{/if}}
									</div>
                                                                        <div class="slide-lang" style="display:none;" lang="${$value.language.id}" lang_name="${$value.language.name}"></div>
								<div class="slide-contributors" style="display:none;"><small> Creator:</small><br/>
									<span class="avarat-text"><a href="user/${$value.owner.id}"><img src="./?url=ajax/getAvatarSrc&id=${$value.owner.id}" height="60" width="60" title="${$value.owner.username}" class="slide-owner-avatar">&nbsp;${$value.owner.username}</a></span><br/>
									{{if $value.translator}}
                                                                        <small> Translator:</small><br/>
                                                                        <span class="avarat-text"><a href="user/${$value.translator.id}"><img src="./?url=ajax/getAvatarSrc&id=${$value.translator.id}" height="60" width="60" title="${$value.translator.username}" class="slide-owner-avatar">&nbsp;${$value.translator.username}</a></span><br/>
                                                                        {{/if}}
                                                                        {{if $value.contributors}}
										{{if $value.contributors.length>1}}	<br/><small> Contributors:</small><br/>					   		
											{{each(i, value2) $value.contributors}}
												{{if value2.split('|')[0]}}
													{{if value2.split('|')[0]!=$value.owner.id}}
														<span class="avarat-text"><a href="user/${value2.split('|')[0]}"><img src="./?url=ajax/getAvatarSrc&id=${value2.split('|')[0]}" height="40" width="40" title="${value2.split('|')[1]}" class="slide-contributor-avatar">&nbsp;${value2.split('|')[1]}</a></span><br/>
													{{/if}}
												{{/if}}
											{{/each}}
										{{/if}}
									{{/if}}
								</div>
							</div>
								</div>
						
						{{/each}}
						<a class="deck-prev-link pointer-cursor" title="Previous">&#8592;</a><a class="deck-next-link pointer-cursor" title="Next">&#8594;</a>
					</script>	
					
					<script id="follow_status_deck" type="text/x-jquery-tmpl">
						{{if current_user_id}}{{if current_user_id!=owner.id}}<a class="btn small {{if is_followed_by_current_user==1}}danger{{else}}success{{/if}}" onclick="follow($(this),'${item_name}','${deck_id}')">{{if is_followed_by_current_user==1}}Unfollow ${item_name}{{else}}Follow ${item_name}{{/if}}</a>{{/if}}{{/if}}
					</script>                                       
                
                                        
				</div>
			</div>	
			<!-- Edit Tab -->
			<!-- TODO: split into two templates -->
                        <div id="news">
                            
                        </div>
			<div id="edit">
				<div id="deck-edit" >
					<div id="response_msg"></div>
					<div id="itemedit"></div>
					<script id="editor_list" type="text/x-jquery-tmpl">
						<ul>
							{{each users}}
								<li style="clear:both;margin-left:30%;">	
									<a target="_blank" href="user/${id}">${username}</a> <a class="pointer-cursor" onclick="remove_user_from_deck(${id},${$data.deck_id})"><img title="remove user" src="static/img/remove.png"></a> &nbsp;<input type="checkbox" id="apply_to_all_subdecks_${id}" name="apply_to_all_subdecks_${id}" value="1"> Delete from all sub decks
								</li>
							{{/each}}
						</ul>
					</script>
					<script id="editor_list_ro" type="text/x-jquery-tmpl">
						<ul>
							{{each users}}
								<li style="clear:both;margin-left:30%;">	
									<a target="_blank" href="user/${id}">${username}</a>
								</li>
							{{/each}}
						</ul>
					</script>					
					<script id="add_editor" type="text/x-jquery-tmpl">
							<br/>
							<div class="clearfix">
								<label for="add_editor">Username or Email address:</label>
								<div class="input">
									<input type="text" id = "editor_useremail" name="editor_useremail" class="span6" /><input type="button" class="btn" onclick="add_editor_action(${deck_id},1);" value="Add as editor" />
								</div>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" id="apply_to_all_subdecks" name="apply_to_all_subdecks" value="1" checked> Assign editor to all sub decks belonging to owner
							</div>					
					</script>
					<script id="add_editor_no" type="text/x-jquery-tmpl">
							<br/>
							<div class="clearfix">
								<label for="add_editor">Username or Email address:</label>
								<div class="input">
									<input type="text" id = "editor_useremail" name="editor_useremail" class="span6" /><input type="button" class="btn" onclick="add_editor_action(${deck_id},0);" value="Add as editor" />
								</div>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" id="apply_to_all_subdecks" name="apply_to_all_subdecks" value="1" checked> Assign editor to all sub decks belonging to editor
							</div>					
					</script>										
					<script id="deck_edit" type="text/x-jquery-tmpl">
						<form id="editdeck">
							<div class="clearfix">
								<label for="title">Title:</label>
								<div class="input">
									<input type="text" id = "title" name="deck[title]" value="${title}" class="span6" />
								</div>
							</div>
				
							<div class="clearfix">
								<label for="abstract">Abstract:</label>
								<div class="input">
										<textarea id="abstract" name="deck[abstract]" class="span8">${abstract}</textarea>
								</div>
							</div>
                                                    
							<div class="clearfix">
								<label for="tags">Tags:</label>
								<div class="input">
									<input id = "tags" type="text" name="deck[tags]" value="${tags}" class="span6" />
								</div>
							</div>                                                        
<!--                                                        <div class="clearfix">
                                                            <label for="language_visible">Language</label> 
                                                            <input id="language_id" type="hidden" name="deck[language_id]" value="${language['id']}">
                                                            <input id="language_name" type="hidden" name="deck[language_name]" value="${language['name']}">
                                                                <span id="language_visible">${language['name']}</span>
                                                                {{if initiator=='yes'}}
                                                                    <a style="cursor: pointer;" onclick="getLanguagesList('#profile_languages')"><i id="lang_icon" class="icon-chevron-down"></i></a>
                                                                {{/if}}
                                                        </div>
                                                    
-->                                                     <div class="clearfix">
								<label for="tags">Footer text:</label>
								<div class="input">
									<input id = "footer_text" type="text" name="deck[footer_text]" value="${footer_text}" class="span6" />
								</div>
							</div>
							{{if initiator=='yes'}}
								<div class="clearfix">
									<label for="tags">Original Author:</label>
									<div class="input">
										<input id = "originator" type="text" name="deck[description]" value="${description}" class="span6"/><br><span class="help-inline">if the original author of the deck is different from the current owner</span>
									</div>
								</div>
							{{else}}
								{{if description!=null}}
									<div class="clearfix">
										<label for="tags">Original Author:</label>
									<div class="input">
										<p>${description}</p>
									</div>
							</div>
								{{/if}}
							{{/if}}
							<div class="clearfix">
								<label for="theme">Default theme:</label>
								<div class="input">
									<select id="theme" name="deck[theme]" class="span6">
										{{each styles}}
											<option value="${id}" {{if id==$data.default_theme}}selected{{/if}}>${name}</option>
										{{/each}}
									</select>
								</div>
							</div>
							<div class="clearfix">
								<label for="theme">Default transition:</label>
								<div class="input">
									<select id="transition" name="deck[transition]" class="span6">
										{{each transitions}}
											<option value="${id}" {{if id==$data.default_transition}}selected{{/if}}>${name}</option>
										{{/each}}
									</select>
								</div>
							</div>	
							{{if owner=='yes'}}
								<div class="clearfix">
									<label for="visibility">Visibility:</label>
									<div class="input">
										<input type="radio" name="deck[visibility]" value="1" {{if $data.visibility==1}}checked{{/if}}> visible 
										<input type="radio" name="deck[visibility]" value="0" {{if $data.visibility==0}}checked{{/if}}> invisible
 										<br /><span class="help-inline">prevents your deck to get appeared in SlideWiki home page</span>
									</div>
								</div>	
								<div class="clearfix">
									<label for="editor"></label>
									Number of editors: <b><a class="pointer-cursor" id="editor_no" title="show editors list" onclick="show_editors(${id},0)">${editor}</a></b>
									-> <a class="pointer-cursor" title="add editor" id="add_desc" onclick="add_editor(${id},1)">{{if editor==0}} Add one{{else}} Add more{{/if}}</a>
										<div id="editors"></div>
								</div>	
							{{else}}
								{{if editors=='yes'}}
								<div class="clearfix">
										<label for="visibility">Visibility:</label>
										<div class="input">
											<input type="radio" name="deck[visibility]" value="1" {{if $data.visibility==1}}checked{{/if}}> visible 
											<input type="radio" name="deck[visibility]" value="0" {{if $data.visibility==0}}checked{{/if}}> invisible
 											<br /><span class="help-inline">prevents your deck to get appeared in SlideWiki home page</span>
										</div>
									</div>	
								{{/if}}	
								<div class="clearfix">
									<label for="editor"></label>
									Number of editors: <b><a class="pointer-cursor" id="editor_no" title="show editors list" onclick="show_editors(${id},1)">${editor}</a></b>
								{{if editors=='yes'}}
									-> <a class="pointer-cursor" title="add editor" id="add_desc" onclick="add_editor(${id},0)">{{if editor==0}} Add one{{else}} Add more{{/if}}</a>
								{{/if}}	
								<div id="editors"></div>
								</div>	
							{{/if}}													
							<div class="actions">
								{{if owner=='yes'}}
									{{if cascade_update==1}}
										<input type="checkbox" name="deck[cascade_update]" value="1" /> Update all instances
									{{/if}}
									<input type="hidden" value="${save_as_new_revision}" name="deck[save_as_new_revision]" />
									<input type="button" class="btn primary" onclick="saveDeck(${id});" value="Save" />
								{{else}}	
									{{if editors=='yes'}}
									<input type="hidden" value="${save_as_new_revision}" name="deck[save_as_new_revision]" />
									<input type="button" class="btn primary" onclick="saveDeck(${id});" value="Save" />
									{{/if}}							
								{{/if}}
								<input type="button" class="btn primary" onclick="newDeckRevision(${id})" value="Create copy & Save!" />
							</div>
						</form> <!-- ./ for	form id="editdeck" -->			
					</script> <!-- ./ for <script id="deck_edit" type="text/x-jquery-tmpl"> -->			
                                        
                                        <script id="profile_languages" type="text/x-jquery-tmpl">
                                                <div style="float:left;"><table>
                                                {{each languages}}
                                                    <tr><td class="" name="" onclick="setLanguage('#editdeck',this.id)" id="${$value.language}">${$value.name}</td></tr>
                                                    {{if ($index + 1) % 10 == 0}}
                                                        </table></div><div style="float:left"><table>
                                                    {{/if}}
                                                {{/each}}
                                                </table></div>                            
                                        </script>
                               </div>		    
			</div>
			
			<!-- Discussion Tab -->
			<div id="discussion">
				<div id="item-discussion">
					<h2>Comments</h2>
					<ul id="itemdiscussion" class="simple separated">
					</ul>
					<script id="item_discussion" type="text/x-jquery-tmpl">
						{{each comments}}
							<li class="comment content{{if current_rev==$value.item_id}} highlight-comment{{/if}}" id="comment_${$value.id}">
								<h3 class="title">
									<span id="com_title_${$value.id}">${$value.title}</span>
									<span class="meta">(by <a href="user/${$value.user.id}">${$value.user.username}</a>, ${$value.creationTime}) <a href="{{if $value.item_type=='slide'}}slide{{else}}deck{{/if}}/${$value.item_id}" target="_blank">‡</a></span>
								</h3>
								
								<div class="separated">
									{{html $value.text}}
									<br/><a class="btn small" onclick="addComment($(this),'comment',${$value.id})">Reply</a>
									{{each $value.replies}}
										<div class="comment content" id="comment_${$value.id}">
											<h4 class="title">
												<span class="com_title_${$value.id}">${$value.title}</span>
												<span class="meta">(by <a href="user/${$value.user.id}">${$value.user.username}</a>, ${$value.creationTime})</span>
											</h4>
											
											<div>
												{{html $value.text}}
											</div>
										</div>
									{{/each}}
								</div>
							</li>				
						{{/each}}
						<li>
						<a class="btn" onclick="addComment($(this),'{{if item_name=='slide'}}slide{{else}}deck{{/if}}',${id})">Add new Comment</a>
						</li>
						{{if item_name=='deck'}}
							<br/><li><a class="btn primary small" onclick="show_subdecks_comments(${id})"> Show sub deck/slide comments</a></li>
							<div id="subdeck_comments"></div>
						{{/if}}
					</script>
					<script id="subdeckComments" type="text/x-jquery-tmpl">
						{{each comments}}
							<li class="comment content{{if current_rev==$value.item_id}} highlight-comment{{/if}}" id="comment_${$value.id}">
								<h3 class="title">
									<span id="com_title_${$value.id}">${$value.title}</span>
									<span class="meta">(by <a href="user/${$value.user.id}">${$value.user.username}</a>, ${$value.creationTime}) <a href="{{if $value.item_type=='slide'}}slide{{else}}deck{{/if}}/${$value.item_id}" target="_blank">‡</a></span>
								</h3>
								
								<div class="separated">
									{{html $value.text}}
									<br/><a class="btn small" onclick="addComment($(this),'comment',${$value.id})">Reply</a>
									{{each $value.replies}}
										<div class="comment content" id="comment_${$value.id}">
											<h4 class="title">
												<span class="com_title_${$value.id}">${$value.title}</span>
												<span class="meta">(by <a href="user/${$value.user.id}">${$value.user.username}</a>, ${$value.creationTime})</span>
											</h4>
											
											<div>
												{{html $value.text}}
											</div>
										</div>
									{{/each}}
								</div>
							</li>				
						{{/each}}
					</script>					
					<ol class="simple separated">

					</ol>
					<script id="comment_template" type="text/x-jquery-tmpl">
							<li class="comment">
								<h3 class="title">
									<strong>${title}</strong><br/>
									<span class="meta">(by <a href="user/${user.id}">${user.username}</a>, ${creationTime})</span>
								</h3>
								
								<div>
									{{html text}}
								</div>
							</li>
					</script>					
				</div>    			    	
			</div>
			
			<!-- Questions Tab -->
			<div id="questions">
                            
				<div id="itemquestions"><a href="static/test/<?php echo $deck->id;?>">(Plain list of questions)</a></div>
				
	     	   	<script id="item_questions" type="text/x-jquery-tmpl">
			    	
                            {{if item_name=='slide'}}
                            <!-- addNewQuestion button -->
                            <div class="form-stacked" align="right">
                                    <div>
                                            <button class="btn primary" onclick="addQuestion(${id})">Add new Question</button>
                                    </div>
                            </div>
                            {{/if}}

                            <!-- questions list -->
                            <ol id="accepted">
                                    {{each questions.accepted}}                                    
                                            <li>
                                               <div >
                                                    <div class="diff_star_small">
                                                        <div class="diff_small">                                                                    
                                                                <div class = "diff_bar_small" id = "diff_bar_${$value.id}"></div> 
                                                        </div>
                                                        <div class="diff_info_small">
                                                            <a id = ${$value.id} class="pointer-cursor" onclick="addQuestion(${$value.slide_revision},${$value.id})">
                                                                    <span class="jstree-draggable"><b question="true" quest_id = ${$value.id}>${$value.question}</b></span>
                                                            </a>							
                                                            by <a href="user/${$value.user.id}">
                                                                    ${$value.user.username}
                                                            </a>
                                                            <?php if($user['is_authorized']){?>
                                                            <a title="Add to a list" class="pointer-cursor" id ="showList_${$value.id}" onClick="showLists(${$value.id})"> 
                                                                <img src="./static/img/tolist.png" align="top">
                                                            </a>
                                                            <a title="Copy" class="pointer-cursor" id ="copy_${$value.id}" onClick="copyQuestion(${$value.id})"> 
                                                                <img src="./static/img/copy.gif"  align="top">
                                                            </a>
                                                            
                                                           {{if <?php echo $user['id'];?> == $value.user.id || <?php echo $user['id'];?> == $value.item_owner}}
                                                           <a title="Remove" class="pointer-cursor" id ="delete_${$value.id}" onClick="deleteQuestion(${$value.id})"> 
                                                                <img src="./static/img/remove.png"  align="top">
                                                            </a>   
                                                           {{/if}}
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                            </div>
                                           </li>
                                    {{/each}}						
                            </ol>
                            <!-- slide modal-->
                            <div class="modal hide fade in" style="display: none; width:auto" id="slide_modal">
                                
                                <div class="modal-body">
                                    <div class="close">x</div>
                                    <div id="slide_modal_body"></div>
                                </div>
                            </div>
                            
                            <div class="modal hide fade in" style="display: none;" id="pdf_modal">
                                
                                <div class="modal-body">
                                    <div class="close">x</div>
                                    <div>
                                        <form id="pdf_form" name="pdf_form">
                                            <legend>
                                                <h4>Choose, how to show the alternatives for questions:</h4>
                                            </legend>
                                            <fieldset>
                                                <div class="input">
                                                    <input type="radio" name="show_answers" value="2" onChange="pdfExport(${id})" checked>
                                                    Highlight correct alternatives 
                                                </div>
                                                <div class="input">
                                                    <input type="radio" name="show_answers" value="1" onChange="pdfExport(${id})">
                                                    Show alternatives without highlighting 
                                                </div>
                                                <div class="input">
                                                    <input type="radio" name="show_answers" value="0" onChange="pdfExport(${id})">
                                                    Don't show alternatives 
                                                </div>
                                            </fieldset>
                                            <div class="form-actions" align="center">
                                                <a id="submitPdf" class="btn primary" style="cursor:pointer" href="./?url=pdf/test&id=${id}&show_answers=2" target="_blank">Export in PDF</a>
                                            </div>
                                                
                                        </form>
                                    </div>
                                </div>
                            </div>
                             
                            
                           
                            <!-- go to testing -->

                            {{if item_name=='deck' && questions.accepted.length}}
                                <a style="float:left;" class="btn success" href="./?url=main/tests&id=${id}&type=auto" onclick = "return !window.open(this.href)">Go to test</a>
                                <a style="float:left;" class="btn success" href="./?url=main/tests&id=${id}&type=exam" onclick = "return !window.open(this.href)">Exam mode</a>
                                <a style="float:left;" class="btn primary" style="cursor:pointer" onclick="pdfOpenModal()">Export in PDF</a>
                                <a style="float:right" href="/static/test/${id}" style="cursor:pointer">( Plain list )</a>
                                
                            {{/if}}
                            {{if item_name=='deck' && !questions.accepted.length}}
                                <center><div data-alert="alert" class="alert-message error">There is no questions for chosen deck. Go to any of its slides to add questions.</div></center>
                            {{/if}}

                            <!-- edit question form -->					
                            <?php if ($user['is_authorized']) {?>
                                    <div class="inner-modal" id="question_form" style="clear:both; display:{{if item_name=='deck'}} none {{else}} block {{/if}}">
                                            <a class="close pointer-cursor" onclick="hideModal('question_form')">x</a>
                                            <div class="form-stacked" id="questions-new-question">
                                                <fieldset>
                                                    <legend id="add_edit_title">Add new Question</legend>
                                                    <ul class="tabs" data-tabs="tabs">
                                                        <li class="active"><a href="#q_edit" id="qeditlink"><span>Edit</span></a></li>
                                                        <li><a href="#q_revisions" id="qrevlink"><span>History</span></a></li>					
                                                    </ul>
                                                  
                                                    <section class="tab-content">
                                                    
                                                    <div id="q_edit" class="active">
                                                        <div class="row">
                                                            <div class="span6">
                                                                <div class="clearfix">
                                                                    <label for="quest_text">Question</label>
                                                                    <div class="input">
                                                                            <textarea rows="1"  class="span6" style="min-height:34px; resize:vertical !important; overflow:hidden !important" type="text" name="quest_text" id="quest_text"/>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="span3">
                                                                <div class="clearfix">
                                                                    <label for="difficulty">Difficulty</label>
                                                                    <div class="input">
                                                                        <input type="hidden" name="difficulty" id="difficulty" value="3"></input>
                                                                    </div>
                                                                    <div class="diff_star" id="diff_star">
                                                                        <div class="diff" id="diff">
                                                                            <div class = "diff_blank" id = "diff_blank"></div> 
                                                                            <div class = "diff_hover" id = "diff_hover"></div> 
                                                                            <div class = "diff_bar" id = "diff_bar"></div> 
                                                                        </div>
                                                                        <div class="diff_info">
                                                                            Select difficulty
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>										
                                                        </div>

                                                        <!-- saveQuestion button -->
<!--                                                        <div class="actions" id="save_button">
                                                            <button class="btn primary" onclick="saveQuestion('${id}','')">Save</button>
                                                        </div>-->

                                                        <!-- answers info -->
                                                        <div id="answer_row">
                                                                <div class="row">
                                                                    <div class="span10">
                                                                        <div class="clearfix">											
                                                                            <legend><br/>Edit Answers</legend>

                                                                            <table id="answers" class="condensed-table">
                                                                                <thead class="blue">
                                                                                    <tr><th>Answer</th><th>Correct?</th><th>Explanation</th><th>Delete</th></tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    <tr id="0">
                                                                                        <td><textarea class="onerow" name="answer_text"/></td>
                                                                                        <td><input class= "answerRow" type="checkbox" name="is_right"></td>
                                                                                        <td><textarea rows="1" class="onerow" name="explanation"/></td>
                                                                                        <td><button name="delete_answer" class="btn small answerRow" onclick="removeAnswer($(this))">-</button></td>
                                                                                    </tr>
                                                                                    <tr id="0">
                                                                                        <td><textarea class="onerow" name="answer_text"/></td>
                                                                                        <td><input class= "answerRow" type="checkbox" name="is_right"></td>
                                                                                        <td><textarea rows="1" class="onerow" name="explanation"/></td>
                                                                                        <td><button name="delete_answer" class="btn small answerRow" onclick="removeAnswer($(this))">-</button></td>
                                                                                    </tr>
                                                                                    <tr id="0">
                                                                                        <td><textarea class="onerow" name="answer_text"/></td>
                                                                                        <td><input class= "answerRow" type="checkbox" name="is_right"></td>
                                                                                        <td><textarea rows="1" class="onerow" name="explanation"/></td>
                                                                                        <td><button name="delete_answer" class="btn small answerRow" onclick="removeAnswer($(this))">-</button></td>
                                                                                    </tr>
                                                                                </tbody>
                                                                            </table>											

                                                                            <!-- addMoreAnswer button -->
                                                                            <button class="btn small answerRow" quest_id="" id="addAnswer" onclick="addMoreAnswer()">+</button>											
                                                                        </div>
                                                                    </div>											
                                                                </div>

                                                                <!-- saveAllAnswers button -->
                                                                <div class="actions" id="save_question">
                                                                        <button class="btn primary" onclick="saveQuestion('${id}','')">Save</button>
                                                                </div>
                                                        </div>
                                                    </div>
                                                    <!-- History tab -->
                                                    <div id="q_revisions">
                                                        <div id="quest_rev"></div>
                                                        
                                                    </div>
                                                    </section>
                                                </fieldset>
                                            </div>						
                                    </div>
                            <?php } ?>

                    </script>                                
              <script id="quest_revisions" type="text/x-jquery-tmpl">
                            <ul class="largeheight">
                                {{each questions}} 
                                <li> <div id="questRevisionMain">
                                    <a id = ${$value.id} class="pointer-cursor" onclick="toggleAnswers($(this).attr('id'))">
                                        ${$value.timestamp} 
                                    </a> by <a href="user/${$value.user.id}">
                                        ${$value.user.username}
                                    </a>
                                    <span id="useQuestRev_${$value.id}"></span>
                                    </div>
                                    <div id="answers${$value.id}" style="display:none; margin-top:5px !important; margin-bottom:5px !important;">
                                        <div class="diff_star_small">
                                            <div class="diff_small">                                                                    
                                                    <div class = "diff_bar_small" id = "diff_bar_rev_${$value.id}"></div> 
                                            </div>
                                            <div class="diff_info_small">
                                                <b>${$value.question}</b>
                                                 <a title="Add to a list" class="pointer-cursor" id ="showList_${$value.id}" onClick="showLists(${$value.id})"> 
                                                    <img src="./static/img/add.png" width="18px" align="top">
                                                </a>
                                            </div>
                                        </div>                                       
                                        <div style="margin-top:5px !important; margin-bottom:5px !important;">
                                            <ul>
                                                {{each $value.answers}}
                                                <li><div {{if $value.is_right=='yes'}} class="correctAnswerSmall" {{/if}}>${$value.answer}</div>
                                                {{if $value.explanation!=""}}<div class="explanationSmall">${$value.explanation}</div>{{/if}}</li>
                                                {{/each}}
                                            </ul>
                                        </div>
                                    </div>
                                    </li>
                                {{/each}}
                            </ul>
                        </script>
			</div>   
			
			
			<!-- History Tab -->
			<div id="history">
				<div id="item-history" class="deckOverviewItem">
					<div id="itemhistory"></div>
					<script id="slide_history" type="text/x-jquery-tmpl">
						<ul>
							{{each(i, value) revisions}}
								<li style="clear:both">
									{{if value.id==$data.id}}
										<b>${value.revisionTime}</b>
									{{else}}
										${value.revisionTime}
									{{/if}}
									by <a href="user/${value.user.id}">${value.user.username}</a>
                                                                        {{if value.translated_from_revision && $data.parent_language.name}}
                                                                            (Translated from <a href="slide/${value.translated_from_revision}">${$data.parent_language.name} version
                                                                            {{if $data.translated_from_changed == 'true' }}
                                                                                 <img style="cursor:pointer" src="/static/img/exclamation_b.gif" alt="The parent translation was changed!">
                                                                            {{/if}}
                                                                            </a>)
                                                                        {{/if}}
									{{if i!=$data.revisions.length-1}}
										(<small><a style="color:#888855;" target="_blank" href="?url=compare/reportSlideChanges&slide=${value.id}">Diff with previous revision</a></small>)
									{{/if}}
									<br />
									<a href="slide/${value.id}" target="_blank"><div class="sliderevision deck-menu deck-container"><div class="slide">{{html value.content}}</div></div><div>${value.comment}</div></a><br style="clear:both" />
									{{if value.id!=$data.id}}
										{{if $data.current_user!=0}}
											<a class="pointer-cursor" onclick="replaceItem('${value.id}','slide')">Use this revision</a>
										{{/if}}
									{{/if}}
								</li>
							{{/each}}
						</ul>
						
						<br style="clear:both" />						
					</script>

					<script id="deck_history" type="text/x-jquery-tmpl">
                                            {{if translated_from_changed}}
                                            <div class="alert-message fade in warning">
                                                <img src="/static/img/exclamation_b.gif" alt="">
                                                The source deck for the translation was modified! <a style="cursor:pointer" onClick="showParentChanges(${id})">Click</a> to see the changes and update the deck.
                                            </div>
                                            {{/if}}
						<ul>
							{{each(i, value) revisions}}
								<li style="clear:both">
									{{if value.id==$data.id}}
										<b>${value.revisionTime}</b>
									{{else}}
										<a href="deck/${value.id}_${value.slug_title}"> ${value.revisionTime}</a>
									{{/if}}
									
									by <a href="user/${value.user.id}">${value.user.username}</a>
                                                                        {{if value.parent_language.name}}
                                                                           (Translated from <a href="deck/${value.translated_from_revision}">${value.parent_language.name} version</a>)
                                                                        {{/if}}
									{{if i!=$data.revisions.length-1}}
										(<small><a style="color:#888855;" class="pointer-cursor" href="?url=compare/compareDecks&deck=${value.id}" target="_blank">Diff with previous revision</a></small>)
										<!--div id="changelog_${value.id}_${i}" style="display:none;" class="alert-message"></div-->
									{{/if}}
									<br />
									{{if value.id!=$data.id}}
										{{if $data.current_user!=0}}
											-><a class="pointer-cursor" onclick="replaceItem('${value.id}','deck')">Use this revision</a>
										{{/if}}
									{{/if}}
								</li>
							{{/each}}
						</ul>
						<br style="clear:both" />

					</script>										
				</div>  	    	
			</div>
			
			<!-- Usage Tab -->
			<div id="usage">
				<div id="item-usage">
					<ul id="itemusage"></ul>
					<script id="item_usage" type="text/x-jquery-tmpl">
						{{each usage}}
							<li><a href="deck/${$value.id}_${$value.slug_title}"><b>${$value.title}</b></a> by <a href="user/${$value.user.id}">${$value.user.username}</a></li>
						{{/each}}
					</script>				
				</div>    		
			</div>

			<script id="root_sibling_usage" type="text/x-jquery-tmpl">
				{{each usage}}
					<li><a href="deck/${$value.id}_${$value.slug_title}">${$value.title}</a></li>
				{{/each}}
			</script>						        
			
			<script id="user_alert" type="text/x-jquery-tmpl">
				<div class="alert-message fade in ${alert_type}" data-alert="alert">
					<a class="close pointer-cursor">×</a>
					<p>${msg}</p>
				</div>					
			</script>	
		</section>
	</section> <!-- End of content section -->
</section>	

<script type="text/javascript">
/*
//temprary: disable some transitions for Chrome
if(Utilities.isSupportedUA()){
	$('#selected_transition').find('option[value="2"]').remove();
	$('#selected_transition').find('option[value="3"]').remove();
	$('#selected_transition').find('option[value="4"]').attr('selected','selected');
	$("#page_transition").attr('href','ajax/transition/css/4');
}
*/
$("#selected_style").chosen();
$("#selected_transition").chosen();
</script>                                              
