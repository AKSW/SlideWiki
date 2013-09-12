<!DOCTYPE html>
<html>
<head>
<base href="<?php echo BASE_PATH?>" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta content="minimum-scale=1.0, width=device-width, maximum-scale=1.0, user-scalable=no" name="viewport" />
<link rel="stylesheet" href="libraries/frontend/deck.js/core/deck.core.css" type="text/css" media="all" />
<link rel="stylesheet" href="libraries/frontend/deck.js/extensions/goto/deck.goto.css" media="all" />
<link rel="stylesheet" href="libraries/frontend/deck.js/extensions/menu/deck.menu.css" media="all" />
<link rel="stylesheet" href="libraries/frontend/deck.js/extensions/navigation/deck.navigation.css" media="all" />
<link rel="stylesheet" href="libraries/frontend/deck.js/extensions/status/deck.status.css" media="all" />
<link rel="stylesheet" href="libraries/frontend/deck.js/extensions/hash/deck.hash.css" media="all" />
<link rel="stylesheet" href="libraries/frontend/deck.js/extensions/scale/deck.scale.css" media="all" />
<link rel="stylesheet" href="libraries/frontend/twitter-bootstrap/bootstrap.min.css" media="all" />
<link rel="stylesheet" href="libraries/frontend/jpreloader/css/jpreloader.css" media="all" />
<!--link rel="stylesheet" href="static/css/main.css" type="text/css" media="all" />-->
<!-- dynamic theme style -->
<link rel="stylesheet" href="<?php echo isset($style) ? './?url=ajax/getCSS&id='.$style : './?url=ajax/getCSS&id='.$deckObject->default_theme ?>"> 
<link rel="stylesheet" href="<?php echo isset($transition)? './?url=ajax/getTransitionCSS&id='.$transition :'./?url=ajax/getTransitionCSS&id='.$deckObject->default_transition; ?>"> 
<!-- jquery -->
<script type="text/javascript" src="libraries/frontend/jquery.min.js"></script>
<script type="text/javascript" src="libraries/frontend/jquery-tmpl/jquery.tmpl.min.js"></script>
<script src="libraries/frontend/jquery-ui/js/jquery-ui-1.8.16.custom.min.js"></script>
<!-- deck js scripts -->
<script src="libraries/frontend/deck.js/modernizr.custom.js"></script>
<script src="libraries/frontend/deck.js/core/deck.core.js"></script>
<script src="libraries/frontend/deck.js/extensions/menu/deck.menu.js"></script>
<script src="libraries/frontend/deck.js/extensions/goto/deck.goto.play.js"></script>
<script src="libraries/frontend/deck.js/extensions/status/deck.status.js"></script>
<script src="libraries/frontend/deck.js/extensions/navigation/deck.navigation.js"></script>
<script src="libraries/frontend/deck.js/extensions/hash/deck.hash.play.js"></script>
<script src="libraries/frontend/deck.js/extensions/scale/deck.scale.js"></script>
<script src="libraries/frontend/deck.js/extensions/iframes/deck.iframes.js"></script>
<!-- MathJax -->
<script type="text/javascript" src="libraries/frontend/MathJax/MathJax.js?config=default"></script>
<script src="static/js/view-spec/playsync.js"></script>
<script src="static/js/scale.js"></script>
<script type="text/javascript" src="libraries/frontend/jpreloader/js/jpreLoader.min.js"></script>
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
<script src="slidewiki-sync/libs/socket.io.min.js"></script>
<script src="slidewiki-sync/public/deck.remote.js"></script>
<script src="libraries/frontend/jquery.qrcode.min.js"></script>
<title><?php echo $deckObject->title; ?></title>
<meta name="keywords" content="<?php echo join ( ',', $deckObject->getTags ( $deckObject->id ) ); ?>">
<meta name="description" content="<?php echo $deckObject->abstract; ?>">
<script>
var sid="<?php echo $sid;?>";
var deck="<?php echo $deckObject->id;?>";
var scaling=<?php echo $scaling;?>;
var style=<?php echo $style;?>;
var transition=<?php echo $transition;?>;
var all_slides=<?php echo $all_slides;?>;
var master=false;
var loaded_range=new Array();
var hash=window.location.hash;
if(!hash){
	progressiveLoadSlide(all_slides[0]);
}else{
	progressiveLoadSlide(hash.split('#')[1]);
}
</script>
<script id="slide_preview" type="text/x-jquery-tmpl">
	{{each slides}}
		<div class="slide {{if $value.position==1}}{{if $value.deck.id==$data.id}}first-slide{{else}}first-sub-slide{{/if}}{{/if}}" id="tree-${$value.deck.id}-slide-${$value.id}-${$value.position}-${$value.absolute_position}-view">
			<div class="slide-content">
                            {{if $value.translation_status!='original'}}
                                <a href="http://translate.google.com"><img src="static/img/google_translate.png" style="float:right;"></a>
                            {{/if}}
				<div class="slide-header">
						<h2>
							<div class="slide-title">
									{{if $value.title}}{{html $value.title}}{{else}}{{/if}}
							</div>
						</h2>
				</div>
				<div class="slide-body">
						{{if $value.body}}{{html $value.body}}{{else}}{{/if}}
				</div>
				<div class="slide-metadata">

				</div>
			</div>
			<div class="slide-footer">
    			<div class="slide-footer-text">
      				<?php echo $deckObject->footer_text;?>
    			</div>
				<div class="deck-status">
					<span class="deck-status-current"></span>
					/
					<span class="deck-status-total"></span>
   				</div>
			</div>	
		</div>			
	{{/each}}
	<a href="#" class="deck-prev-link" title="Previous">&#8592;</a>
	<a href="#" class="deck-next-link" title="Next">&#8594;</a>
			
	<form action="." method="get" class="goto-form">
		<label for="goto-slide">Go to slide:</label>
		<input type="number" name="slidenum" id="goto-slide">
		<input type="submit" value="Go">
	</form>	
</script>
<style>
#qrcodediv{
z-index:234;
text-align:center;
background-color:#FFF;
border-radius: 15px;
border: #aaa solid 2px;
width:280px;
font-size:14px;
position:absolute;
top:50%;
left:50%;
}
.qr-close-btn{
	text-align:right;	
}
</style>
</head>
<body style="height: 100%;padding: 0px;">	
	<div class="deck-container" id="slide-area" style="padding:0; top: 0;left: 0;bottom: 0;right: 0;">
	</div>
<script type="text/javascript">// <![CDATA[
   $(document).ready(function() {
       //scroll bar only if neccesary
        $("html").css('overflow-y','auto');
        $('body').jpreLoader();
	$(document).ajaxStart(function(){
		//$("#ajax_progress_indicator").show();
		$('#slide-area').append("<div id='play_progress_indicator'><h2>Loading data...</h2></div>");
	})	
	$(document).ajaxStop(function(){
		$("#play_progress_indicator").remove();
	})
        
});
// ]]></script>
</body>
</html>