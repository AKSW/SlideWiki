<!DOCTYPE html>
<html>
<head>
<base href="<?php echo BASE_PATH?>" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" href="libraries/frontend/deck.js/core/deck.core.css" type="text/css" media="all" />
<link rel="stylesheet" href="static/css/main.css" type="text/css" media="all" />
<!-- MathJax -->
<script type="text/javascript" src="libraries/frontend/MathJax/MathJax.js?config=default"></script>
	<!-- jquery -->
	<script type="text/javascript" src="libraries/frontend/jquery.min.js"></script>
<script src="static/js/view-spec/print_deck.js"></script>
<title><?php echo $deckObject->title; ?></title>
<style>
@media print{@page {size: landscape}}
</style>
</head>
<body class="deck-container" id="slide-area">
<?php
foreach ($deckObject->slides as $slide)
{
 echo'<div class="slide'.($slide->position==1?($slide->deck==$deckObject->id?' first-slide':' first-sub-slide'):'').'" id="tree-'.$slide->deck.'-slide-'.$slide->id.'-'.$slide->position.'-view">
	<div class="slide-content">
		<div class="slide-header">
		<h2>
			<div class="slide-title">'.$slide->title.'
			</div>
		</h2>
		</div>
		<div class="slide-body">'.$slide->body.'
		</div>
		<div class="slide-footer">'.$deckObject->footer_text.'
		</div>
		<div class="slide-metadata">
		</div>
	</div>
</div>'.PHP_EOL;	
}
?>		
</body>
</html>
