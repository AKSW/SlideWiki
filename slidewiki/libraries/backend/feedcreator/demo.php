/*

This demo is to illustrate how to use feedcreator 
using outputFeed function, ATOM1.0 and enclosure 
support.



<enclosure> support is useful if you are into 
podcasting or publishing photoblog.

the required parameter for enclosure is url, length
and type (as in MIME-type)

*/

<?php
include ("include/feedcreator.class.php");

//define channel
$rss = new UniversalFeedCreator();
$rss->useCached();
$rss->title="Personal News Site";
$rss->description="daily news from me";
$rss->link="http://mydomain.net/";
$rss->syndicationURL="http://mydomain.net/$PHP_SELF";


//channel items/entries
$item = new FeedItem();
$item->title = "test berita pertama";
$item->link = "http://mydomain.net/news/somelinks.html";
$item->description = "hahaha aku berjaya!";
$item->source = "http://mydomain.net";
$item->author = "my_email@mydomain.net";


//optional enclosure support
$item->enclosure = new EnclosureItem();
$item->enclosure->url='http://mydomain.net/news/picture.jpg';
$item->enclosure->length="65905";
$item->enclosure->type='image/jpeg';

$rss->addItem($item);


//Valid parameters are RSS0.91, RSS1.0, RSS2.0, PIE0.1 (deprecated),
// MBOX, OPML, ATOM, ATOM1.0, ATOM0.3, HTML, JS

$rss->outputFeed("ATOM1.0"); 
//$rss->saveFeed("ATOM1.0", "news/feed.xml"); 

?>