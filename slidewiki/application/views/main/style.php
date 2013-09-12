<!-- scripts specific to this view -->
<script src="static/js/view-spec/style.js"></script>
<link rel="stylesheet" href="<?php echo './?url=ajax/getTransitionCSS&id='.$deck->default_transition; ?>"> 
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

<?php if ($user['is_authorized']){ ?>
<style type="text/css" id="slidestyle">
<?php echo $styleObj->css; ?>
</style> 
<?php if($response==1){?>
<div class="alert-message fade in success" data-alert="alert">
<a class="close" href="#">×</a>
<p>Style was successfully saved! ---> <a href=deck/<?php  
echo $deck->id;?>/style/<?php echo $styleObj->id; ?>><b>Go back to "<?php echo $deck->title;?>".</b></a></p>
</div>	
<?php }?>	

<div class="content">
    <div class="page-header">
		<h1><a href="deck/<?php  
                echo $deck->id . '_' . $deck->slug_title; ?>"><?php echo $deck->title; ?></a> : Edit Theme / <a href="./?url=transition/builder4impress&deck=<?php echo $deck->id;?>">Builder4Impress</a><span align="right" id="go_impress"> / <a href="./?url=transition/impressionist&deck=<?php echo $deck->id;?>">Impressionist</a></span></h1>
    </div>
<form method="post" action="./?url=main/changeStyle">
<input type="hidden" name="url" value="main/style" />
<input type="hidden" name="deck" value="<?php echo $deck->id; ?>" />
<div class="clearfix">
<label for="theme:">Theme:</label>
<div class="input">
<select name="id" onchange="this.form.submit()">
<?php
foreach($styles as $r) {
	echo('<option value="'.$r['id'].'" '.($_GET['id']==$r['id']?'selected':'').'>'.$r['name'].'</option>');
}
?>
</select>
</div>
</div>
</form>
<form method="post" action="./?url=main/changeStyle">
<input type="hidden" name="deck" value="<?php echo $deck->id; ?>" />
<input type="hidden" name="style[id]" value="<?php echo $styleObj->id; ?>" />
<div class="clearfix">
<label for="style:">Style:</label>
<div class="input">
	<ul class="tabs" data-tabs="tabs">
		<li class="active"><a href="#scsstab" id="scsslink"><span>SCSS</span></a></li>
		<li><a href="#csstab" id="csslink"><span>CSS</span></a></li>
	</ul>
	<div class="tab-content">
		<div id="scsstab" class="active">
		<b>Mixins and Variables</b> (For more info go <a href="http://sass-lang.com/" target="_blank">here</a>)
			<textarea id="scss_varfunc" name="style[scss_varfunc]" onchange="compileSCSS();" style="background:#eeffee;width:100%; height:200px;"><?php echo $styleObj->scss_varfunc; ?></textarea>
			.deck-container {
			<textarea id="scss_input" name="style[scss]" onchange="compileSCSS();" style="background:#eeffff;width:100%; height:500px;"><?php echo $styleObj->scss; ?></textarea>
			}
		</div>
		<div id="csstab"> 
			<textarea id="css_input" name="style[css]"  onchange="$('#slidestyle').text(this.value);" style="width:100%; height:500px;" readonly="readonly"><?php echo $styleObj->css; ?></textarea>
		</div>
	</div>

</div>
</div>
<div class="clearfix">
<label for="name:">Name:</label>
<div class="input">
<input type="text" name="style[name]" value="<?php echo $styleObj->name; ?>">
</div>
</div>
<div class="clearfix">
<label for="comment:">Comment:</label>
<div class="input">
<input type="text" name="style[comment]" value="<?php echo $styleObj->comment; ?>">
</div>
</div>
<div class="clearfix">
<div class="input">
<input type="checkbox" name="new" <?php 		if ($styleObj->owner != $user['id']) {
			echo 'checked readonly="true" title="This style has to be saved as a new style, since it was created originally by somebody else."';
		} ?> />&nbsp;Save as new theme
	
<input type="submit" name="submit" value="Save" />
</div>
</div>
</form>
<hr>
<div id="slide-area" class="deck-container" style="min-height:850px">
<?php
foreach ($deck->slides as $slide)
{
 echo'<div class="slide'.($slide->position==1?($slide->deck==$deck->id?' first-slide':' first-sub-slide'):'').'" id="tree-'.$slide->deck.'-slide-'.$slide->id.'-'.$slide->position.'-view">
	<div class="slide-content">
		<div class="slide-header">
		<h2>
			<div class="slide-title">'.$slide->title.'
			</div>
		</h2>
		</div>
		<div class="slide-body">'.$slide->body.'
		</div>
		<div class="slide-metadata">
		</div>
	</div>
</div>'.PHP_EOL;
}
?>
<div class="slide-footer">
    <div class="slide-footer-text">
      <?php echo $deck->footer_text?>
    </div>
	<div class="deck-status">
	<span class="deck-status-current"></span>
		/
	<span class="deck-status-total"></span>
     </div>
</div>
<a href="#" class="deck-prev-link" title="Previous">&#8592;</a><a href="#" class="deck-next-link" title="Next">&#8594;</a><form action="." method="get" class="goto-form">	<label for="goto-slide">Go to slide:</label><input type="number" name="slidenum" id="goto-slide"><input type="submit" value="Go"></form>
</div>
<script>
$(document).ready(
		  function(e)
		  {
		      if(Utilities.isSupportedUA())
		      {
		          //alert("Webkit");
		          $("#go_impress").css("visibility","visible");
		      }
		      else
		      {
		          $("#go_impress").css("visibility","hidden");
		      }
		  $.deck('.slide');
		  $.deck('iframes');
		  MathJax.Hub.Queue(["Typeset",MathJax.Hub,'slide-area']);
})

</script>
</div>
<?php }?>