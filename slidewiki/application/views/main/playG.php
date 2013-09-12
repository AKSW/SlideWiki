<!--
Google IO 2012/2013 HTML5 Slide Template

Authors: Eric Bidelman <ebidel@gmail.com>
         Luke Mahé <lukem@google.com>

URL: https://code.google.com/p/io-2012-slides
-->
<!DOCTYPE html>
<html>
<head>
<base href="<?php echo BASE_PATH?>" />
  <title><?php echo $deckObject->title; ?></title>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="chrome=1">
  <!--<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">-->
  <!--<meta name="viewport" content="width=device-width, initial-scale=1.0">-->
  <!--This one seems to work all the time, but really small on ipad-->
  <!--<meta name="viewport" content="initial-scale=0.4">-->
  <meta name="apple-mobile-web-app-capable" content="yes">
  <link rel="stylesheet" media="all" href="libraries/frontend/google-slide-template/theme/css/default.css">
  <link rel="stylesheet" media="only screen and (max-device-width: 480px)" href="libraries/frontend/google-slide-template/theme/css/phone.css">
  <base target="_blank"> <!-- This amazingness opens all links in a new tab. -->
  <script data-main="libraries/frontend/google-slide-template/js/slides" src="libraries/frontend/google-slide-template/js/require-1.0.8.min.js"></script>
<script>
var SLIDE_CONFIG = {
		  // Slide settings
		  settings: {
		    title: '<?php echo $deckObject->title; ?>',
		    subtitle: '',
		    //eventInfo: {
		    //  title: 'Google I/O',
		    //  date: '6/x/2013'
		    //},
		    useBuilds: true, // Default: true. False will turn off slide animation builds.
		    usePrettify: true, // Default: true
		    enableSlideAreas: true, // Default: true. False turns off the click areas on either slide of the slides.
		    enableTouch: true, // Default: true. If touch support should enabled. Note: the device must support touch.
		    //analytics: 'UA-XXXXXXXX-1', // TODO: Using this breaks GA for some reason (probably requirejs). Update your tracking code in template.html instead.
		    favIcon: '',
		    fonts: [
		      'Open Sans:regular,semibold,italic,italicsemibold',
		      'Source Code Pro'
		    ],
		    //theme: ['mytheme'], // Add your own custom themes or styles in /theme/css. Leave off the .css extension.
		  },

		  // Author information
		  presenters: [{
		    name: ' <?php echo $deckObject->user->first_name.' '.$deckObject->user->last_name; ?>',
		    company: '',
		    gplus: '',
		    twitter: '',
		    www: '',
		    github: ''
		  }/*, {
		    name: 'Second Name',
		    company: 'Job Title, Google',
		    gplus: 'http://plus.google.com/1234567890',
		    twitter: '@yourhandle',
		    www: 'http://www.you.com',
		    github: 'http://github.com/you'
		  }*/]
		};
</script>
</head>
<body style="opacity: 0">

<slides class="layout-widescreen">

  <slide class="title-slide segue nobackground">
    <aside class="gdbar"><img src="libraries/frontend/google-slide-template/images/swiki_logo.png"></aside>
    <!-- The content of this hgroup is replaced programmatically through the slide_config.json. -->
    <hgroup class="auto-fadein">
      <h1 data-config-title><!-- populated from slide_config.json --></h1>
      <h2 data-config-subtitle><!-- populated from slide_config.json --></h2>
      <p data-config-presenter><!-- populated from slide_config.json --></p>
    </hgroup>
  </slide>
<?php 
foreach ($deckObject->slides as $index=>$slide){
?>
  <slide>
    <hgroup>
      <h2><?php echo $slide->title;?></h2>
    </hgroup>
    <article>
<?php echo $slide->getBody()?>
    </article>
  </slide>
<?php 
}
?>
  <slide class="backdrop"></slide>

</slides>
</body>
</html>
