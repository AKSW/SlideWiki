<?php
include_once ROOT . DS . 'libraries' . DS . 'backend' . DS . 'SlideCompare' . DS . 'SlideCompare.php';
class CompareController extends Controller {
	
	private function compare($slide1, $slide2) {
	
	}
	//written by Ivan ------------------
	function mergeSlides() {
		$slide1 = new Slide ();
		$id = 327;
		$slide1->createFromID ( $id ); // revision id
		$slide2 = new Slide ();
		$slide2->id = $slide1->getPreviousRevisionID ();
		if ($slide2->id != NULL) {
			$slide2->createFromID ( $slide2->id );
			;
			$comparison_result = $this->compare ( $slide1, $slide2 );
			$this->set ( 'first_slide_content', $comparison_result [0] );
			$this->set ( 'second_slide_content', $comparison_result [1] );
			$this->set ( 'comparison_result', $comparison_result [2] );
		}
	}
	//----------------------------------
	function tidyhtml($input) {
		$config = array(
			   'show-errors'         => 0,
			   'show-warnings'         => false,
			   'break-before-br'         => true,
			   'indent'         => true,
			   'indent-attributes'         => true,
			   'add-xml-decl'   => false,
			   'force-output'   => true,
			   'fix-backslash'   => false,
			   'merge-divs'   => false,
			   'merge-spans'   => false,
			   'doctype'   => 'omit',
			   'enclose-block-text'   => true,
			   'drop-empty-paras'   => false,
			   'output-html'   => true,
			   'show-body-only'   => true,
			   'wrap'           => 1);

		$tidy = new tidy;
		$tidy->parseString($input, $config, 'utf8');
		$tidy->cleanRepair();

		// Output
		return $tidy;
	}
        function compareParentTranslations(){
            $this->_template->disableHeader();
            $this->_template->disableFooter();
            $id = @$_GET ['deck']; //the  source revision for the current translation
            $compareTo= @$_GET ['compareTo']; //the last revision of the source deck
            //language of the current deck, if we want to update the translation
            $language_id = @$_GET['language_id'];
            $language_name = @$_GET['language_name'];
            if(!isset($id))
                    die("Error in receiving Deck id!");
            $deck1=new Deck();
            $deck2=new Deck();
            $deck1->createFromIDLite($id);
            if(!isset($compareTo))
                    $deck2->id = $deck1->getPreviousRevisionID ();
            else
                    $deck2->id = $compareTo;
            $deck2->createFromIDLite($deck2->id);
            $deck1->content = $deck1->fetchDeckContentLite ();
            $deck2->content = $deck2->fetchDeckContentLite ();

            $this->set ( 'page_title', 'SlideWiki - Compare Decks/Slides') ;
            $this->set ( 'new_content', json_encode($this->getTree($deck1)) );
            $this->set ( 'old_content', json_encode($this->getTree($deck2)) );
            $this->set ( 'last_id', $id);
            $this->set ( 'language_id', $language_id);
            $this->set ( 'language_name', $language_name);
        }
	//written by Ali ------------------
	function compareDecks() {
		//$this->_template->disableHeader();
		//$this->_template->disableFooter();
		$id = @$_GET ['deck'];
		$compareTo= @$_GET ['compareTo'];
		if(!isset($id))
			die("Error in receiving Deck id!");
		$deck1=new Deck();
		$deck2=new Deck();
		$deck1->createFromIDLite($id);
		if(!isset($compareTo))
			$deck2->id = $deck1->getPreviousRevisionID ();
		else
			$deck2->id = $compareTo;
		$deck2->createFromIDLite($deck2->id);
		$deck1->content = $deck1->fetchDeckContentLite ();
		$deck2->content = $deck2->fetchDeckContentLite ();

		$this->set ( 'page_title', 'SlideWiki - Compare Decks/Slides') ;
		$this->set ( 'new_content', json_encode($this->getTree($deck1)) );
		$this->set ( 'old_content', json_encode($this->getTree($deck2)) );
	}
	private function getTree($deck) {
		$output [] = array ("attr" => array ('id' => 'tree-0-deck-' . $deck->id . '-1' . '-node' ), 'state' =>'open', 'data' => array ("title" => $deck->shortenTitle ( $deck->title ), 'icon' => $deck->getIcon (), 'attr' => array ('id' => 'tree-0-deck-' . $deck->id . '-1', 'class' => 'root-node tnode tdeck deck-' . $deck->id, 'title'=>$deck->title, 'href'=>"deck/".$deck->id.'_'.$deck->slug_title) ), 'children' => $this->getTreeNodes ($deck) );
		return $output;
	}
	private function getTreeNodes($deck) {
		static $index_counter=0;
		$output = $childs = array ();
		foreach ( $deck->content as $v ) {
			if (get_class ( $v ) == 'Deck'){				
				$childs = $this->getTreeNodes ($v);
				$output [] = array ("attr" => array ('id' => 'tree-' . $deck->id . '-deck-' . $v->id . '-' . $v->position . '-node' ), 'data' => array ("title" => $v->shortenTitle ( $v->title ), 'icon' => $v->getIcon (), 'attr' => array ('id' => 'tree-' . $deck->id . '-deck-' . $v->id . '-' . $v->position, 'class' =>   'tnode tdeck deck-' . $v->id,  'title'=>$v->title, 'href'=>"deck/".$v->id.'_'.$v->slug_title ) ), 'children' => $childs );
			}else{
				$output [] = array ("attr" => array ('id' => 'tree-' . $deck->id . '-slide-' . $v->id . '-' . $v->position .'-node' ), 'data' => array ("title" => $v->shortenTitle ( $v->title ), 'icon' => $v->getIcon (), 'attr' => array ('id' => 'tree-' . $deck->id . '-slide-' . $v->id . '-' . $v->position, 'class' => 'tnode tslide slide-' . $v->id, 'title'=>$v->title, 'href'=>"slide/".$v->id) ) );
				$index_counter++;
			}
			$childs = array ();
		}
		return $output;
	}	
	function reportSlideChanges() {
		$this->_template->disableHeader();
		$this->_template->disableFooter();
		$id = @$_GET ['slide'];
		$compareTo= @$_GET ['compareTo'];
		if(!isset($id))
			die("Error in receiving Slide id!");
		$slide1=new Slide();
		$slide2=new Slide();
		$slide1->createFromIDLite($id);
		if(!isset($compareTo))
			$slide2->id = $slide1->getPreviousRevisionID ();
		else
			$slide2->id = $compareTo;
		$slide2->createFromIDLite($slide2->id);
		//$slide2->content=$this->tidyhtml($slide2->content);
		//$slide1->content=$this->tidyhtml($slide1->content);
		$this->set ( 'new_content', htmlspecialchars($slide1->content) );	
		$this->set ( 'old_content', htmlspecialchars($slide2->content) );
		$this->set ( 'slide_id', $id );
		//echo $this->htmlDiff($slide2->content, $slide1->content);
	}
	function reportDeckChanges() {
		//$this->_template->disableHeader();
		//$this->_template->disableFooter();	
		$this->_noRender = true;
		$id = $_GET ['deck'];
		$deck = new Deck ();
		
		$deck->createFromIDLite ( $id ); // revision id
		$deck2 = new Deck ();
		$deck2->id = $deck->getPreviousRevisionID ();
		if ($deck2->id != NULL) {
			$deck2->createFromIDLite ( $deck2->id );
			
			$deck->content = $deck->fetchDeckContentLite ();
			$deck2->content = $deck2->fetchDeckContentLite ();
			$deck_count = count ( $deck->content );
			$deck2_count = count ( $deck2->content );
			//var_dump($deck->content);
			//var_dump($deck2->content);
			if ($deck_count > $deck2_count) {
				echo ($deck_count - $deck2_count) . " slide(s) added to the deck.";
			} elseif ($deck_count < $deck2_count) {
				echo ($deck2_count - $deck_count) . " slide(s) removed from the deck.";
			} else {
				$flag = 0;
				$changes = array ();
				foreach ( $deck->content as $index => $val ) {
					if ($val->id != $deck2->content [$index]->id) {
						$flag ++;
						array_push ( $changes, array ($index, $deck2->content [$index]->id, $val->id ) );
					}
				
				}
				if ($flag) {
					echo "Content of the following " . $flag . " slide(s) has been modified:<br>";
					echo "<ol>";
					foreach ( $changes as $v ) {
						echo "<li>";
						echo "<a href='slide/" . $v [1] . "' target='_blank'>Slide</a> at position <b>" . ($v [0] + 1) . "</b> <a href='slide/" . $v [2] . "' target='_blank'>changed</a> (<small><a style='color:#888855;' target='_blank' href='?url=compare/reportSlideChanges&slide=".$v [2]."&compareTo=".$v [1]."'>details</a></small>).";
						echo "</li>";
					}
					echo "</ol>";
				} else {
					echo "Just a copy of the previous revision!";
				}
			}
		}
	}
}