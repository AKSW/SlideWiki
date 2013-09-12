<?php

class StaticController extends Controller {
	function deck() {
		$deck_id=$_GET['id'];
		$deck=new Deck();
		$deck->createFromIDLite($deck_id);
		$deck->last_revision_id = $deck->getLastRevisionID ();
		$usage=$deck->getUsage();
		$deck->comments=$deck->getComments();
		$translations=$deck->getTranslatedTo();
		$this->set ( 'deck', $deck );
		$this->set ( 'translations', $translations );
		$this->set ( 'usage', $usage );
		$this->set ( 'page_title', $deck->title.' - SlideWiki' );
		$this->set ( 'page_keywords', join ( ',', $deck->getTags ( $deck->id ) ) );		
	}
	function activities() {
		$stream = new Stream ();
		$stream->getMainPageStream ( 50 );
		$this->set ( 'stream', $stream );
		$this->set ( 'page_title', 'Latest activities - SlideWiki' );
		$this->set ( 'page_keywords', 'SlideWiki, activities, presentations');	
	}
        
        function test(){
            $item_id=$_GET['id'];
            $test = new Test();
            $test->createFromItem($item_id);
            $test->questions = $test->getAllQuestions();
            $this->set('test',$test);
            $this->set ( 'page_title', 'SlideWiki - List of questions for '.$test->title );
			$this->set ( 'page_keywords', 'SlideWiki, questions');	
        }
}