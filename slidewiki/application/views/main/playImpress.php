<head>
<base href="<?php echo BASE_PATH?>" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script type="text/javascript" src="libraries/frontend/MathJax/MathJax.js?config=default"></script>
<link rel="stylesheet" href="<?php echo isset($style) ? './?url=ajax/getCSS&id='.$style : './?url=ajax/getCSS&id='.$deckObject->default_theme ?>"> 
<title><?php echo $deckObject->title; ?></title>
</head>
<body class="deck-container" id="slide-area">
<div id="impress">
    <div data-scale="10" data-y="1500" data-x="3000" class="step past" id="overview" style="position: absolute; -moz-transform: translate(-50%, -50%) translate3d(3000px, 1500px, 0px) rotateX(0deg) rotateY(0deg) rotateZ(0deg) scale(10); -moz-transform-style: preserve-3d;">
    </div>
<?php
$i=1;
if(!$show_others_flag){

	foreach ($deckObject->slides as $index=>$slide)
	{
		$j=$i*550;
		$k=$i*650;
	 echo'<div '.$transitions[$index]->css.' class="step '.($slide->position==1?($slide->deck==$deckObject->id?' first-slide':' first-sub-slide'):'').'" id="slide'.$index.'">
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
	<div class="slide-footer">
	    <div class="slide-footer-text">
	      '. $deckObject->footer_text.'
	    </div>
		<div class="deck-status"><span class="deck-status-current">'.($index+1).'</span>/<span class="deck-status-total">'.$total.'</span></div>    
	</div>	
	</div>'.PHP_EOL;	
	 $i++;
	}
}else{
	echo "<ul> <h2>Select one of the existing impress transitions for this deck:</h2>";
	foreach ($others_transitions as $item)
	{
		echo '
		<li><a href="./?url=main/playImpress&deck='.$deckObject->id.'&user='.$item->id.'">
		Created by '.$item->username.'
		</a></li>
		';
		
	}
	echo "</ul>";
}
?>

</div>
<script src="libraries/frontend/impress.js/js/impress.js"></script>
<script>impress().init();</script>
</body>
