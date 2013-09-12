<?php
session_start ();
class TransitionController extends Controller {
	function impressionist() {
		$this->_template->disableHeader ();
		$this->_template->disableFooter ();
		$deck_id = isset ( $_GET ['deck'] ) ? $_GET ['deck'] : 0;
		$deck = new Deck ();
		$slides = array ();
		if ($deck_id) {
			$deck->createFromID ( $deck_id );
			$slides = $deck->getSlides ();
			$_SESSION ['deck'] = $deck_id;
		}
		$user_id = $this->_user ['id'];
		if ($user_id) {
			$_SESSION ['user'] = $user_id;
		}
		$this->set ( 'deckObject', $deck );
		$this->set ( 'slides', json_encode ( $slides ) );
	}
	function preview() {
		$this->_template->disableHeader ();
		$this->_template->disableFooter ();
		$deckid = $_GET ['deck'];
		
		$deck = new Deck ();
		$deck->createFromID ( $deckid );
		$user_id = $this->_user ['id'];
		if (! $user_id)
			die ( 'This feature is only available for specific users!' );
		
		$t = new ImpressTransition ();
		$t->user_id = $user_id;
		$t->deck_id = $deckid;
		
		$others_transitions = array ();
		$others_transitions = $t->getAllStylesForDeck ();
		if (! count ( $others_transitions ))
			die ( 'There is no impress transition available!' );
		else
			$show_others_flag = 1;
		
		$this->set ( 'others_transitions', $others_transitions );
		$this->set ( 'deckObject', $deck );
	}
	function saveToDB() {
		$this->_template->disableHeader ();
		$this->_template->disableFooter ();
		$this->_noRender = true;
		if(isset($_POST ['styles'])){
			$styles = json_decode ( $_POST ['styles'] );
			//first clear the previous transitions
			if (! isset ( $_SESSION ['user'] ) && ! isset ( $_SESSION ['deck'] ))
				die ( 'please login first!' );
			$tmp = new ImpressTransition ();
			$tmp->user_id = $_SESSION ['user'];
			$tmp->deck_id = $_SESSION ['deck'];
			$tmp->deleteAllCSS ();
			foreach ( $styles as $index => $style ) {
				$t = new ImpressTransition ();
				$t->user_id = $_SESSION ['user'];
				$t->deck_id = $_SESSION ['deck'];
				$t->slide_position = $index + 1;
				$t->css = $style;
				$t->create ();
			}
		}else{
			echo 'All the transitions are saved! See it in action by clicking <a href="./?url=main/playImpress&deck=' . $_SESSION ['deck'] . '&style=9">here</a>!<br><br>';
		}
		//var_dump ( $styles );
	
		//insert styles into db
	}
	function builder4impress() {
		$this->_template->disableHeader ();
		$this->_template->disableFooter ();
		$deck_id = isset ( $_GET ['deck'] ) ? $_GET ['deck'] : 0;
		$deck = new Deck ();
		$slides = array ();
		if ($deck_id) {
			$deck->createFromID ( $deck_id );
			$slides = $deck->getSlides ();
			$_SESSION ['deck'] = $deck_id;
		}
		$user_id = $this->_user ['id'];
		if ($user_id) {
			$_SESSION ['user'] = $user_id;
		}
		$this->set ( 'deckObject', $deck );
		$this->set ( 'slides', $slides  );
	}	
}
