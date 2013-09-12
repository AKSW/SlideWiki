<style>
.slide-link-path{
	color:#412C84;
}
.hidden{
	display:none;	
}
</style>
<?php 
 function prepareUsagePath($deckorg,$deck,$prev){
		$usage=$deck->getUsage();
		//echo count($usage).'<br>';
		if (count($usage)){
			foreach ($usage as $dc){
				prepareUsagePath($deckorg,$dc,'<a href="deck/'.$deck->id. '_' . $deck->slug_title.'">'.$deck->shortenTitle($deck->title).'<sub>'.$deck->getRevisionNumber($deck->id).'</sub></a><b> > </b>'.$prev);
			}				
		}else{
			if($deck->getLastRevisionID()!=$deck->id){
				$hiddenstat='class="hidden"';
				$shouldbr='';
			}else {
				$hiddenstat='';
				$shouldbr='<br/>';
			}			
			echo '<span '.$hiddenstat.'><a href="deck/'.$deck->id. '_' . $deck->slug_title.'">'.$deck->shortenTitle($deck->title).'<sub>'.$deck->getRevisionNumber($deck->id).'</sub></a> <b> > </b>'.$prev.'<a class="slide-link-path" href="deck/'.$deck->id. '_' . $deck->slug_title.'#tree-'.$deckorg->parent_deck.'-deck-'.$deckorg->id.'-'.$deckorg->position.'-view">['.$deckorg->shortenTitle($deckorg->title).']</a></span>'.$shouldbr;
		}
 }
?>
<div class="content">
		<?php
		if( is_array($usage) && count($usage) ){ ?>
		<div class="breadcrumb" style="z-index:10;padding-left:15px;">
			<?php echo "<b>This deck is part of the following presentations:</b><br/>";?>
			<?php foreach ($usage as $use){
				$deck->parent_deck=$use->id;
				$deck->position=$deck->getPosition();
				echo "<small>";
				prepareUsagePath($deck,$use,'');
			echo "</small> ";
			}?>
		</div>
		<?php }elseif($deck->last_revision_id!=$deck->id){?>
		<div class="alert alert-block alert-warning" style="z-index:10;padding-left:15px;">
			<?php echo '<small>*There are new revisions of this deck. <a href="static/deck/'.$deck->last_revision_id.'">Go to the latest revision of this deck!</a></small>';?>
		</div>	
		<?php }?>		
 <ul class="tabs" data-tabs="tabs">
    <li class="active" id="deck_content"><a href="#content">Deck</a></li>
    <li id="deck_comments"><a href="#comments" >Comments (<?php echo count($deck->comments);?>)</a></li>                            
</ul>
<div class="tab-content">
<div id="content" class="active">
		<?php
echo "<h2><a href='deck/".$deck->id. '_' . $deck->slug_title."'>".$deck->title."</a></h4><br/>";
$deck->abstract = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]","<a href=\"\\0\">\\0</a>", $deck->abstract);
echo $deck->abstract."<br/><br/>";
$initiator=$deck->getInitiator();
if($initiator->id != $deck->owner->id){
	echo 'Initiator:'." <a href='user/".$initiator->id."'>".$initiator->username."</a><br/>";
}
echo 'Creator:'." <a href='user/".$deck->owner->id."'>".$deck->owner->username."</a><br/>";
$contributors=$deck->getSlideContributors();
if( is_array($contributors) && count($contributors)>1 ){ 
	echo '<div id="usage">';
	echo 'Contributors:'; 
	foreach ($contributors as $contr){
		$tmp=split('\|',$contr);
		echo " <a href='user/".$tmp[0]."'>".$tmp[1]."</a> / ";
	}
	echo '</div>';
}
$deck->tags=$deck->getTags($deck->id);
$tmp=array();
foreach($deck->tags as $t){
	$t=trim($t);
	if($t!=''){
			$tmp[]='<a href="search/keyword/'.$t.'" target="_blank">'.$t.'</a>';
	}
}

if (count($tmp)){
	$deck->tags= join ( ', ', $tmp );
	echo 'Tags: '.$deck->tags;
}
$revisions=$deck->getRevisions();
if (count($revisions)>1){
	echo '<br/>Other revisions: ';
	foreach ($revisions as $r){
		if($r->id !=$deck->id)
			echo '<a href="deck/'.$r->id. '_' . $r->slug_title.'">R_'.$r->id.'</a> /';
	}		
}
if (count($translations)){
	echo '<br/>Translations: ';	
	foreach ($translations as $t){
		echo '<a href="deck/'.$t['last']. '_' . $t['slug_title'] . '">'.$t['language']['name'].'</a>, ';
	}	
}	
$slides=$deck->getSlidesLite();
echo "<hr/><b>Slides:</b><ol>";
foreach ($slides as $s){
	//if($s->id==$s->getLastRevisionID ())
		//echo '<li><a href="slide/'.$s->id.'/latest">'.($s->getTitle()?$s->getTitle():'Untitled').'</a></li>';
	//else 
		echo '<li><a href="slide/'.$s->id.'">'.($s->getTitle()?$s->getTitle():'Untitled').'</a></li>';
}
echo "</ol>";
$sources = array ();
foreach ($deck->slides as $s){
	$dsc=$s->getDescription();
	if($dsc)
		$sources[]=trim($dsc);
}	
$deck->sources= array_unique($sources);
if($c=count($deck->sources)){
	echo "<hr/><b>Sources (".$c.")</b><ul>";
	foreach ($deck->sources as $s){
		$s = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]","<a href=\"\\0\">\\0</a>", $s);
		echo '<li>'.$s.'</li>';
	}
	echo "</ul>";
}	
?>
	</div>
	<div id="comments" class="content">
	<?php if(!count($deck->comments)){?>
		There are no comments for this deck.
	<?php }?>
	<?php foreach($deck->comments as $c){?>
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
</div>
<script>
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