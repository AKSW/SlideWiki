 <div class="span14">
 <h1>
 <?php if ($profile->email): ?>
 <img src="http://www.gravatar.com/avatar/<?php echo md5(strtolower(trim($profile->email))); ?>?d=retro&amp;r=g" height="50" width="50" class="avatar" />
<?php endif; ?>
<a href="user/<?php echo $profile->id;?>"><?php echo trim($profile->username);?></a>    
 </h1>
</div>
<div class="content row">
<div class="span8">
<?php 
if($c=count($editoreddecks))
	echo "<h4>User has the 'editor' role in the following <b>".$c."</b> decks:</h4>";
foreach ($editoreddecks as $e){
	echo "<li><a href='deck/".$e->id. '_' . $e->slug_title . "'>".$e->title."</a> (".$e->revisionTime.") (".$e->language['name'].")</li>";
}
?>
</div>
<div class="span8">
<?php 
if($c=count($owndecks))
	echo "<h4>User has the 'owner' role in the following <b>".$c."</b> decks:</h4>";
foreach ($owndecks as $e){
	echo "<li><a href='deck/".$e->id. '_' . $e->slug_title . "'>".$e->title."</a> (".$e->revisionTime.") (".$e->language['name'].")</li>";
}
?>
</div>
</div>