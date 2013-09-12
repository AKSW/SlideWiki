<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script type="text/javascript" src="libraries/frontend/MathJax/MathJax.js?config=default"></script>
<title><?php echo $deckObject->title; ?></title>
</head>
<body >
<?php
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
?>

</body>
