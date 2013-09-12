<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=1024" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <title>builder4impress - <?php echo $deckObject->title; ?></title>
    
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:regular,semibold,italic,italicsemibold|PT+Sans:400,700,400italic,700italic|PT+Serif:400,700,400italic,700italic" rel="stylesheet" />

    <!-- style.css is needed for the presentation to look ok, the rest is up to you -->
    <link href="libraries/frontend/builder4impress/style.css" rel="stylesheet" />
    <link href="libraries/frontend/builder4impress/css/builder.css" rel="stylesheet" />
    
    <link rel="shortcut icon" href="libraries/frontend/builder4impress/favicon.png" />
    <link rel="apple-touch-icon" href="libraries/frontend/builder4impress/apple-touch-icon.png" />
</head>


<body class="impress-not-supported">

<!--
    For example this fallback message is only visible when there is `impress-not-supported` class on body.
-->
<div class="fallback-message">
    <p>Your browser <b>doesn't support the features required</b> by impress.js, so you are presented with a simplified version of this presentation.</p>
    <p>For the best experience please use the latest <b>Chrome</b>, <b>Safari</b> or <b>Firefox</b> browser.</p>
</div>


<div id="impress">
    
    <div id="overview" class="step" data-x="3000" data-y="1500" data-scale="10">
    </div>
 <?php
$i=0;
	foreach ($slides as $index=>$slide)
	{  
?> 
    <div id="<?php echo 'slide'.$i;?>" class="step slide" data-x="<?php echo 50+200*$i;?>" data-y="<?php echo 100+200*$i;?>">
	<?php echo $slide->content;?>
    </div>
<?php $i++;}?>
   
</div>


<script src="libraries/frontend/builder4impress/js/impress.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>

<script src="libraries/frontend/builder4impress/js/FileSaver.min.js"></script>
<script src="libraries/frontend/builder4impress/js/builder.js"></script>
<script>
  var iAPI=impress();
  iAPI.init();

Builder.init({
  "goto":iAPI['goto'], //it makes me feel better this way
  creationFunction:iAPI.newStep, //future API method that adds a new step
  redrawFunction:iAPI.initStep, //future API method that (re)draws the step
  setTransformationCallback:iAPI.setTransformationCallback //future API method that lets me know when transformations change
});

</script>

</body>
</html>
