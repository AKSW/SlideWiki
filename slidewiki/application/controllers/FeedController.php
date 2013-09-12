<?php
error_reporting ( 0 );
require_once ROOT . DS . 'libraries' . DS . 'backend' . DS . 'feedcreator' . DS . 'include' . DS . 'feedcreator.class.php';

class FeedController extends Controller {
	function __construct() {
		@parent::__construct ();
		$this->_noRender = true;
	}
	function activities() {
		$feed_type = $_GET ['output'];
		if (! isset ( $feed_type ))
			$feed_type = "RSS1.0";
		
		//define channel
		$rss = new UniversalFeedCreator ();
		$rss->useCached ();
		$title = "SlideWiki -- Latest activities";
		$description = "list of latest activities on SlideWiki";
		$link = "http://slidewiki.org/";
		$syndicationURL = "http://slidewiki.aksw.org/feed/activities";
		$rss->title = $title;
		$rss->description = $description;
		$rss->link = $link;
		$rss->syndicationURL = $syndicationURL;
		
		$stream = new Stream ();
		$stream->getMainPageStream ( 20 );
		//channel items/entries
		foreach ( $stream->activities as $i => $s ) {
			switch ($s->type) {
				case 'created_deck' :
					$s->type = 'created deck';
					break;
				case 'translated_deck_from' :
					$s->type = 'translated deck';
					break;
				case 'commented_deck_revision' :
					$s->type = 'commented deck';
					break;
				case 'followed_deck' :
					$s->type = 'started following deck';
					break;
				case 'translated_deck' :
					$s->type = 'translated deck';
					break;
				case 'created_deck_revision' :
					$s->type = 'created deck revision';
					break;
			}
			$item = new FeedItem ();
			$item->title = 'Activity '.($i+1);
			$item->link = "http://slidewiki.org/?url=main/deck_stream&deck=".$s->object->id;
			$item->description = '<a href="http://slidewiki.org/user/'.$s->subject->id.'">'.$s->subject->username . '</a> ' . $s->type . ' <a href="http://slidewiki.org/deck/'.$s->object->id.'_'.$s->object->slug_title.'">'.$s->object->title.'</a>';
			$item->source = "http://slidewiki.org/";
			$item->date = $s->timestamp;
			$item->author = '';
			
			$rss->addItem ( $item );
		}
		//Valid parameters are RSS0.91, RSS1.0, RSS2.0, PIE0.1 (deprecated),
		// MBOX, OPML, ATOM, ATOM1.0, ATOM0.3, HTML, JS
		

		$rss->outputFeed ( $feed_type );
	
	}
	function decks() {
		$criteria = $_GET ['show'];
		if (! isset ( $criteria ))
			die ( "error in receiving feed criteria!" );
		$feed_type = $_GET ['output'];
		if (! isset ( $feed_type ))
			$feed_type = "RSS1.0";
		$deckList = new DeckList ();
		switch ($criteria) {
			case "new" :
				$title = "SlideWiki -- New Presentations";
				$description = "list of new presentations";
				$link = "http://slidewiki.org/search/order/date";
				$syndicationURL = "http://slidewiki.org/feed/decks/new";
				$decks = $deckList->getAllDecks ( 15 );
				break;
			case "popular" :
				$title = "SlideWiki -- Popular Presentations";
				$description = "list of popular presentations";
				$link = "http://slidewiki.org/search/order/popularity";
				$syndicationURL = "http://slidewiki.org/feed/decks/popular";
				$decks = $deckList->getAllPopular ( 15 );
				break;
			case "featured" :
				$title = "SlideWiki -- Featured Presentations";
				$description = "list of featured presentations";
				$link = "http://slidewiki.org/search/order/featured";
				$syndicationURL = "http://slidewiki.org/feed/decks/featured";
				$decks = $deckList->getAllFeatured ( 15 );
				break;
		}
		//define channel
		$rss = new UniversalFeedCreator ();
		$rss->useCached ();
		$rss->title = $title;
		$rss->description = $description;
		$rss->link = $link;
		$rss->syndicationURL = $syndicationURL;
		
		//channel items/entries
		foreach ( $decks as $deck ) {
			$item = new FeedItem ();
			$item->title = $deck->title;
			$item->link = "http://slidewiki.org/deck/" . $deck->id . '_' . $deck->slug_title;
			$item->description = $deck->abstract;
			$item->source = "http://slidewiki.org/";
			$item->date = strtotime ( $deck->revisionTime );
			$item->author = $deck->owner->username;
			
			$rss->addItem ( $item );
		}
		//Valid parameters are RSS0.91, RSS1.0, RSS2.0, PIE0.1 (deprecated),
		// MBOX, OPML, ATOM, ATOM1.0, ATOM0.3, HTML, JS
		

		$rss->outputFeed ( $feed_type );
	
	}
}
