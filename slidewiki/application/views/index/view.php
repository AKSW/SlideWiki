<!-- scripts specific to this view -->
<script src="static/js/view-spec/index.js"></script>
<script src="libraries/frontend/deck.js/extensions/hash/deck.hash.tree.js"></script>
<script type="text/javascript" src="libraries/frontend/jquery-tmpl/jquery.tmpl.min.js"></script>
<link rel="stylesheet" href="static/css/stream.css" type="text/css" media="all" />
<!--activity stream scripts-->
<?php require_once (ROOT . DS . 'application' . DS . 'views' . DS . 'activity_templates.php'); ?>
<!-- begin of view -->
<header class="hero-unit row">
<div class="span10 slogan">
	<div class="row">
	 <div class="span4"><img src="static/img/slidewiki_logo.png" alt="SlideWiki"/></div>
	  <div class="span4">
	</div>
	</div>
    <p>SlideWiki aims to revolutionize the way how educational material is authored, shared and used.</p>
    <p><a class="btn info large" href="#about">Learn more ...</a> <a class="btn large" href="slide/10777/latest">Get involved</a> <a class="btn large" href="deck/create">Create a presentation</a></p>

</div>
<div class="span2 content newsfeed">
<?php
$url = "http://blog.aksw.org/category/projects/SlideWiki/feed/";
$rss = simplexml_load_file($url);
if($rss)
{
//echo '<h6>'.$rss->channel->title.'</h6>';
echo '<h6>SlideWiki News <a target="_blank" href = "'.$url.'" title="Feed"><img src="static/img/feed.gif" alt="feed"></a></h6>';
$items = $rss->channel->item;
$counter=0;
foreach($items as $item)
{
$counter++;
$title = $item->title;
$link = $item->link;
$published_on =strftime("%b %e", strtotime($item->pubDate));
$description = $item->description;
echo $published_on.': <b><a href="'.$link.'">'.$title.'</a></b><br/>';
if ($counter==3)
	break;
//echo '<p>'.$description.'</p>';
}
echo '<div class="newsfeedshowmore"> <a class="btn mini" href="http://blog.aksw.org/category/projects/SlideWiki">Show more...</a></div>';
}
?>
</div>

</header> <!-- /.hero-unit (teaser) -->
<section class="content row">
<?php if($static):?>
<?php 
$deckList = new DeckList();
$decks = $deckList->getAllFeatured(30);
echo '<h2>Featured presentations <a target="_blank" href = "feed/decks/featured" title="Feed"><img src="static/img/feed.gif" alt="feed"></a></h2>';
foreach ($decks as $d){
	echo "<h4><a href='deck/".$d->id . '_' . $d->slug_title . "'>" . $d->title . "</a></h4><br/>";
	echo $d->abstract . "<br/>";
}
$decks = $deckList->getAllDecks(30,true);
echo '<h2>New presentations <a target="_blank" href = "feed/decks/new" title="Feed"><img src="static/img/feed.gif" alt="feed"></a></h2>';
foreach ($decks as $d){
	echo "<h4><a href='deck/" . $d->id . '_' . $d->slug_title . "'>" . $d->title . "</a></h4><br/>";
	echo $d->abstract . "<br/>";	
}
?>
<?php else:?>
		<div class="span6"> <!-- featured and popular presentations -->
			<h2>Featured presentations <a target="_blank" href = "feed/decks/featured" title="Feed"><img src="static/img/feed.gif" alt="feed"></a></h2>
			<ul id="featured" class="deckOverviewList">
			</ul>		
			<a href = "search/order/featured" class="btn">Show more...</a>
			<!--h2>Popular presentations <a target="_blank" href = "feed/decks/popular" title="Feed"><img src="static/img/feed.gif" alt="feed"></a></h2>
			<ul id="popular" class="deckOverviewList">
			Will be available soon...
			</ul!-->
			<!-- href = "search/order/popularity" class="btn">Show more...</a-->
		</div>

		<div class="span6">
			<h2>New presentations <a target="_blank" href = "feed/decks/new" title="Feed"><img src="static/img/feed.gif" alt="feed"></a></h2>
			<ul id="new" class="deckOverviewList">
			</ul>
			<a href = "search/order/date" class="btn">Show more...</a>
		</div>
		<div class="span4">
			<h2>Latest activity <a target="_blank" href = "feed/activities" title="Feed"><img src="static/img/feed.gif" alt="feed"></a></h2>
			<div id="home_activity_stream" class="only_text"></div>
            <a href = "activities" class="btn mini" style="margin-top:3px;">Show all recent changes</a>            
			<?php 
				$stat=new Statistics();
				$stat->calculateAll();
			?>
			<br/><h2>Statistics</h2>
			<div id="home_statistics" class="home-statistics">
			</div>
		</div>
<?php endif;?>
</section> <!-- /.content -->

<hr/> <!-- horizontal line to separate footer -->

<footer id="about">
	<div class="page-header">
		<h2>About SlideWiki</h2>
	</div>
	<div class="row">
		<div class="span-two-thirds">
		    <p><b>SlideWiki aims to exploit the wisdom, creativity and productivity of the crowd for the creation of qualitative, rich, engaging educational content.</b> With SlideWiki users can create and collaborate on slides, diagrams, assessments and arrange this content in richly-structured course presentations.</p>
			<p><b>SlideWiki empowers communities of educators to author, share and re-use sophisticated educational content in a truly collaborative way.</b> Existing presentations can be imported and transformed into interactive courses using HTML and LaTeX. All content in SlideWiki is versioned thereby allowing users to revise, adapt and re-mix all content. Self-test questions can be attached to each individual slide and are aggregated on the presentation level into comprehensive self-assessment tests. Users can create their own presentation themes. Slidewiki supports the semi-automatic translation of courses in more than 50 languages.</p>
			<p><b>With SlideWiki we aim to make educational content dramatically more accessible, interactive, engaging and qualitative.</b> SlideWiki is developed and maintained by <a href="http://aksw.org" target="_blank">AKSW research group</a> at University of Leipzig. More information about SlideWiki can be found in the <a href="documentation/">documentation</a>.</p>
		</div>
		<div class="span-one-third">
			<ul>
				<li><a href="deck/8/latest">WYSIWYG slide authoring</a></li>
				<li><a href="slide/43/latest">Logical slide and deck representation</a></li>
				<li><a href="slide/46/latest">LaTeX/MathML integration</a></li>
				<li><a href="slide/15/latest">Multilingual decks / semi-automatic translation in 50+ languages</a></li>				
				<li><a href="slide/29/latest">PowerPoint/HTML import</a></li>
				<li><a href="slide/32/latest">Source code highlighting within slides</a></li>
				<li><a href="slide/51/latest">Dynamic CSS themability and transitions</a></li>
				<li><a href="slide/31/latest">Support of social networking activities</a></li>
				<li><a href="deck/3/latest">Full revisioning and branching of slides and decks</a></li>
				<li style="text-align:left;"><a href="slide/39/latest">E-Learning with self-assessment questionnaires</a></li>
				<li style="text-align:left;"><a href="slide/35/latest">Remote control of presentations</a></li>
			</ul>
		</div>
	</div>
</footer>
<script id="deck_preview" type="text/x-jquery-tmpl">
{{each(i, d) decks}}
	<div class="deck-action-bar"><a target="_blank" href="playSync/deck/${d.id}"><img title="Play" class="small-play" src="static/img/play_small.png"></a></div>
	<li class="deckOverviewItem">
		<h3 class="deckOverviewTitle">
		 <a href="deck/${d.id}_${d.slug_title}"> ${d.title} (${d.number_of_slides}  slides)</a>
		</h3>
		<div class="deck-menu deck-container">
		{{each(j, slide) d.slides}}						
			<div class="slide" onclick="goToSlide(this.id)" id="deck/${d.id}_${d.slug_title}#tree-${slide.deck.id}-slide-${slide.id}-${slide.position}-view">
				<div>
						{{html slide.content}}				            
						<span class="hideme">Go to Slide ${slide.id} of Deck &quot;${d.title}&quot;
				</div>
			</div>
		{{/each}}
			{{if d.abstract}}	
				<div class="home-deck-abstract">
					<p><small>${d.abstract}</small></p>
				</div>
			{{/if}}	
		</div>
	</li>
{{/each}}				
</script>
<script id="slidewiki_stats" type="text/x-jquery-tmpl">
	<ul>
		<li>Decks: <b>${number_of_decks}</b></li>
		<li>Deck revisions: <b>${number_of_deck_revisions}</b></li>
		<li>Slides: <b>${number_of_slides}</b></li>
		<li>Slide revisions: <b>${number_of_slide_revisions}</b></li>
		<li>Questions: <b>${number_of_questions}</b></li>
		<li>Active users: <b>${number_of_users}</b></li>
	</ul>
</script>