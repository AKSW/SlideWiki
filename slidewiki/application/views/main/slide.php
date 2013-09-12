<?php
 function prepareUsagePath($slide,$deck,$prev){
		$usage=$deck->getUsage();
		//echo count($usage).'<br>';
		if (count($usage)){
			foreach ($usage as $dc){
				prepareUsagePath($slide,$dc,'<a href="deck/'.$deck->id.'_' . $deck->slug_title .'">'.$deck->shortenTitle($deck->title).'<sub>'.$deck->getRevisionNumber($deck->id).'</sub></a><b> > </b>'.$prev);
			}				
		}else{
			if($deck->getLastRevisionID()!=$deck->id){
				$hiddenstat='class="hidden"';
				$shouldbr='';
			}else {
				$hiddenstat='';
				$shouldbr='<br/>';
			}
			echo '<span '.$hiddenstat.'><a href="deck/'.$deck->id. '_' . $deck->slug_title . '">'.$deck->shortenTitle($deck->title).'<sub>'.$deck->getRevisionNumber($deck->id).'</sub></a> <b> > </b>'.$prev.'<a class="slide-link-path" href="deck/'.$deck->id. '_' . $deck->slug_title . '#tree-'.$slide->deck.'-slide-'.$slide->id.'-'.$slide->position.'-view">['.$slide->shortenTitle($slide->title).']</a></span>'.$shouldbr;
		}
 }
?>
<link id="page_css" rel="stylesheet" href="ajax/css/1"> 
<style>
.slide-link-path{
	color:#412C84;
}
.hidden{
	display:none;	
}
</style>
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
		<?php
		if( is_array($usage) && count($usage) ){ ?>
		<div class="breadcrumb" style="z-index:10;padding-left:15px;">
			<?php echo "<b>This slide is part of the following presentations:</b><br/>";?>
			<?php foreach ($usage as $use){
				$slide->deck=$use->id;
				$slide->position=$slide->getPosition();
				echo "<small>";
				prepareUsagePath($slide,$use,'');
				echo "</small>";
		}?>
		</div>
		<div id="more_options"></div>
		<?php }elseif($slide->last_revision_id!=$slide->id){?>
		<div class="alert alert-block alert-warning" style="z-index:10;padding-left:15px;">
			<?php echo '<small>*There are new revisions of this slide. <a href="slide/'.$slide->last_revision_id.'">Go to the latest revision of this slide!</a></small>';?>
		</div>	
		<?php }?>
 <ul class="tabs" data-tabs="tabs">
    <li class="active" id="slide_content"><a href="#content">Slide</a></li>
    <li id="slide_comments"><a href="#comments" >Comments (<?php echo count($slide->comments);?>)</a></li>                            
</ul>
<div class="tab-content">
<div id="content" class="active">
<article class="deck-container content" id="slide-area">
		<div class="slide">
			<div class="slide-content">
				<div class="slide-header">
						<h2>
							<div class="slide-title">
									<?php echo $slide->title; ?>
							</div>
						</h2>
				</div>
				<div class="slide-body">
						<?php echo $slide->getBody(); ?>
				</div>
				<div class="slide-metadata">
					<?php if( $slide->note){ ?>
					<div id="slide-note" >
					*Speaker notes: <?php echo $slide->note; ?><br></div>
					<?php }?>
				</div>
			</div>	
		</div>			

</article>
<footer class="content">
<?php if( $slide->description){ ?>Original Source: <a><?php echo $slide->description; ?></a><br/><?php }?>
	Created by	<a href="user/<?php echo $slide->owner->id; ?>"><?php echo $slide->owner->username; ?></a>.
<?php
$contributors=$slide->getContributors();
if( is_array($contributors) && count($contributors)>1 ){ ?>
<div id="usage" >
Contributed by : 
<?php foreach ($contributors as $contr){
$tmp=split('\|',$contr);
echo "<a href='user/".$tmp[0]."'>".$tmp[1]."</a> / ";
}?>
</div>
<?php }?>
</footer>
	</div>
	<div id="comments" class="content">
	<?php if(!count($slide->comments)){?>
		There are no comments for this slide.
	<?php }?>
	<?php foreach($slide->comments as $c){?>
<li class="comment">
								<h3 class="title">
									<strong><?php echo $c->title;?></strong>
									<span class="meta">(by <a href="user/<?php echo $c->user->id;?>"><?php echo $c->user->username;?></a>, <?php echo $c->creationTime;?>)</span>
								</h3>
								
								<div>
									<?php echo $c->text;?>
								</div>
									<?php $c->replies=$c->getReplies();foreach($c->replies as $r){?>
										<div class="comment content" style="background-color:#f7f7f7;border-radious:15px;border-color:#e7e7e7;">
											<h4 class="title">
												<span><?php echo $r->title;?></span>
												<span class="meta">(by <a href="user/<?php echo $r->user->id;?>"><?php echo $r->user->username;?></a>, <?php echo $r->creationTime;?>)</span>
											</h4>
											
											<div>
												<?php echo $r->text;?>
											</div>
										</div>
									<?php }?>								
</li>
<hr/>
	<?php }?>
	</div>
</div>
<br/><br/>
<script>
$.deck('.slide');
$('#comments').linkify();
if($('.hidden').length){
	$('.breadcrumb').append('<a id="showmore_btn" class="btn mini" onclick="showmore();">Show usage in all deck revisions</a>');
}
function showmore(){
$('.hidden').after('<br/>');
$('.hidden').removeClass('hidden');
$('#showmore_btn').remove();
}
</script>