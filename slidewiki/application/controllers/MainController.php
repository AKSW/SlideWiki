<?php
class MainController extends Controller {
    function deck_stream(){
            $id = $_GET['deck'];
            $deck = new Deck ();            
            $deck->createFromIDLite ( $id ); 
            $deck->last_revision_id = $deck->getLastRevisionID();            
            $this->set('deck', $deck);
			$this->set ( 'page_title', 'Latest activities of deck "'.$deck->title.'" - SlideWiki' );            
     }
	function deck() {
            
        $deck_id = $_GET ['deck'];
		$user_id = $this->getCurrentUserID ();

		$deck = new Deck ();
		$deck->createFromIDLite ( $deck_id );
		//show error if deck does not exist
		if(!isset($deck->deck_id)){
			header('Location: '.BASE_PATH.'error/404');
			die();
		}
		$style = isset ( $_GET ['style'] ) ? $_GET ['style'] : $deck->default_theme;
		$transition = isset ( $_GET ['transition'] ) ? $_GET ['transition'] : $deck->default_transition;
		if ($user_id) {
			$user = new User ();
			$user->createFromID ( $user_id );
			if (! isset ( $_GET ['style'] ) && $deck_id)
				$style = $user->getPreferenceValue ( 'deck-' . $deck_id . '-theme' );
			else
				$user->setPreferenceValueNoPass ( 'deck-' . $deck_id . '-theme', $_GET ['style'] );
			if (! isset ( $_GET ['transition'] ) && $deck_id)
				$transition= $user->getPreferenceValue ( 'deck-' . $deck_id . '-transition' );
			else
				$user->setPreferenceValueNoPass ( 'deck-' . $deck_id . '-transition', $_GET ['transition'] );				
		}			
		$deck->last_revision_id = $deck->getLastRevisionID ();
                $deck->getTranslatedFromChanged();
		$brand=$deck->getBrand();
		$styleObj = new Style ();
		$styles = $styleObj->getAll ();
		$transitionObj = new Transition ();
		$transitions = $transitionObj->getAll ();		
		$current_user = new User ();
		$current_user->createFromID ( $this->getCurrentUserID () );
		$this->set ( 'isFollowing', $current_user->isFollowing ( 'deck', $deck->deck_id ) );
		$this->set ( 'styles', $styles );
		$this->set ( 'transitions', $transitions );
		$this->set ( 'deck', $deck );
		$this->set ( 'page_title', $deck->title .' - SlideWiki');
		$this->set ( 'page_keywords', join ( ',', $deck->getTags ( $deck->id ) ) );
		$this->set ( 'page_description', $deck->abstract );				
		if (! $style) {
			$style = $deck->default_theme;
		}
		$this->set ( 'style', $style );
		if (! $transition) {
			$transition = $deck->default_transition;
		}
		$this->set ( 'transition', $transition );	
		$this->set ( 'brand', $brand );	
		$this->set ( 'page_additional_footer','<p><a href="static/deck/'.$deck->id. '_' . $deck->slug_title . '">( Plain Deck )</a></p>');
	}
	function slide() {
		$slide_id = $_GET ['slide'];
		$slide = new Slide ();
		$slide->createFromID ( $slide_id );
		if($slide->description)
			$slide->description = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]","<a href=\"\\0\">\\0</a>", $slide->description);
		//show error if slide does not exist
		if(!isset($slide->slide_id)){
			header('Location: '.BASE_PATH.'error/404');
			die();
		}
		$usage=$slide->getUsage();
		$slide->comments = $slide->getComments ();
		$this->set ( 'usage', $usage );
		$this->set ( 'slide', $slide );
		$this->set ( 'page_title', $slide->getTitle().' - SlideWiki' );
	}
	function getCurrentUserID() {
		return $this->_user ['id'];
	}
	function play() {
		$this->_template->disableHeader();
		$this->_template->disableFooter();
		$deckid = $_GET ['deck'];
		
		$deck = new Deck ();
		$deck->createFromID ( $deckid );
		//show error if deck does not exist
		if(!isset($deck->deck_id)){
			header('Location: '.BASE_PATH.'error/404');
			die();
		}
		$current_user = new User ();
		$current_user->createFromID ( $this->getCurrentUserID () );
		$slides = $deck->getSlidesLite(1);
		$all_slides= array();
		foreach ($slides as $i=>$slide){
			$all_slides[]="tree-".$slide->deck->id."-slide-".$slide->id."-".$slide->position."-".$i."-view";
		}
		$deck->slides = $slides;
		$deck->is_followed_by_current_user = $current_user->isFollowing ( 'deck', $deckid );

		$scaling=isset($_GET ['scaling'])?$_GET ['scaling']:1;
		$style = isset ( $_GET ['style'] ) ? $_GET ['style'] : $deck->default_theme;
		$transition= isset ( $_GET ['transition'] ) ? $_GET ['transition'] : $deck->default_transition;
		
		$this->set ( 'style', $style );
		$this->set ( 'transition', $transition );
		$this->set ( 'scaling', $scaling );
		$this->set ( 'deckObject', $deck );
		$this->set ( 'all_slides', json_encode($all_slides) );
	}
	function playSync() {
		if(isset($_GET ['short'])){
			$u=new Url();
			$link=$u->get_short_url($_GET ['short']);
			if(preg_match('/deck\/(?P<digit>\d+)/', $link,$matches)){
				$tmp=$matches[1];
				$deckid = $tmp;	
				$deck = new Deck ();
				$deck->createFromID ( $deckid );					
			}else{
				die('No deck is determined!');
			}
			if(preg_match('/sid\/(?P<digit>\d+)/', $link,$matches)){
				$tmp=$matches[1];
				$sid = $tmp;
			}else 
				$sid =0;
			if(preg_match('/style\/(?P<digit>\d+)/', $link,$matches)){
				$tmp=$matches[1];
				$style=$tmp;
			}else 
				$style =  $deck->default_theme;
			if(preg_match('/transition\/(?P<digit>\d+)/', $link,$matches)){
				$tmp=$matches[1];
				$transition=$tmp;
			}else
				$transition= $deck->default_transition;							
		}else{
			$deckid = $_GET ['deck'];	
			$deck = new Deck ();
			$deck->createFromID ( $deckid );			
			$sid = isset($_GET ['sid'])?$_GET ['sid']:0;
			$style = isset ( $_GET ['style'] ) ? $_GET ['style'] : $deck->default_theme;
			$transition= isset ( $_GET ['transition'] ) ? $_GET ['transition'] : $deck->default_transition;
		}
		$this->_template->disableHeader();
		$this->_template->disableFooter();
		//show error if deck does not exist
		if(!isset($deck->deck_id)){
			header('Location: '.BASE_PATH.'error/404');
			die();
		}
		$current_user = new User ();
		$current_user->createFromID ( $this->getCurrentUserID () );
		$slides = $deck->getSlidesLite(1);
		$all_slides= array();
		foreach ($slides as $i=>$slide){
			$all_slides[]="tree-".$slide->deck->id."-slide-".$slide->id."-".$slide->position."-".$i."-view";
		}
		$deck->slides = $slides;
		$deck->is_followed_by_current_user = $current_user->isFollowing ( 'deck', $deckid );

		$scaling=isset($_GET ['scaling'])?$_GET ['scaling']:1;
		
		$this->set ( 'style', $style );
		$this->set ( 'transition', $transition );
		$this->set ( 'scaling', $scaling );
		$this->set ( 'deckObject', $deck );
		$this->set ( 'sid', $sid );
		$this->set ( 'all_slides', json_encode($all_slides) );
	}	
	function playImpress() {
		$this->_template->disableHeader();
		$this->_template->disableFooter();
		$deckid = $_GET ['deck'];
		
		$deck = new Deck ();
		$deck->createFromID ( $deckid );
		//show error if deck does not exist
		if(!isset($deck->deck_id)){
			header('Location: '.BASE_PATH.'error/404');
			die();
		}
		$user_id= $this->getCurrentUserID();
		if(!$user_id)
			die('This feature is only available for specific users!<br>');
		$slides = $deck->getSlidesLite(1);
		$deck->slides = $slides;

		$scaling=isset($_GET ['scaling'])?$_GET ['scaling']:1;
		$style = isset ( $_GET ['style'] ) ? $_GET ['style'] : $deck->default_theme;
		
		$t=new ImpressTransition();
		$t->user_id=$user_id;
		if(isset($_GET ['user']))
			$t->user_id=$_GET ['user'];
		$t->deck_id=$deckid;
		$transitions=$t->getStylesForUserDeck();
		$show_others_flag=0;
		$others_transitions=array();
		if(!count($transitions)){
			$others_transitions=$t->getAllStylesForDeck();
			if(!count($others_transitions))
				die('There is no impress transition available!<br>');
			else 
				$show_others_flag=1;
		}
			

		$this->set ( 'transitions', $transitions );
		$this->set ( 'others_transitions', $others_transitions );
		$this->set ( 'style', $style );
		$this->set ( 'scaling', $scaling );
		$this->set ( 'deckObject', $deck );
		$this->set ( 'total', count($deck->slides) );
		$this->set ( 'show_others_flag', $show_others_flag );
	}	
	//play using Google template
	//TODO: adopt it for deck.js
	function playG() {
		$this->_template->disableHeader();
		$this->_template->disableFooter();
		$deckid = $_GET ['deck'];
		
		$deck = new Deck ();
		$deck->createFromID ( $deckid );
		//show error if deck does not exist
		if(!isset($deck->deck_id)){
			header('Location: '.BASE_PATH.'error/404');
			die();
		}
		$slides = $deck->getSlidesLite(1);
		$deck->slides = $slides;
		$deck->owner->getProfile();
		$this->set ( 'deckObject', $deck );
	}
	function print_deck() {
		$this->_template->disableHeader();
		$this->_template->disableFooter();
		$deckid = $_GET ['deck_id'];
		$style = isset ( $_GET ['style'] ) ? $_GET ['style'] : 1;
		
		$deck = new Deck ();
		$deck->createFromID ( $deckid );
		//show error if deck does not exist
		if(!isset($deck->deck_id)){
			header('Location: '.BASE_PATH.'error/404');
			die();
		}
		$current_user = new User ();
		$current_user->createFromID ( $this->getCurrentUserID () );
		$slides = $deck->getSlidesFull ();
		$deck->slides = $slides;
		
		$this->set ( 'style', $style );	
		$this->set ( 'deckObject', $deck );
	}	
	function changeStyle(){
		$current_user = new User ();
		$current_user->createFromID ( $this->getCurrentUserID () );		
		$deckid = $_POST ['deck'];
                $deck = new Deck();
                $deck->id = $deckid;
                $deck->title = $deck->getTitle();
                $deck->slug_title = $deck->sluggify($deck->title);
		$styleid = isset ( $_POST ['id'] ) ? $_POST ['id'] : 1;
		if (isset ( $_POST ['submit'] )) {
			$response = 1;
			$s = $_POST ['style'];			
			if (isset ($_POST ['new'])) {
				$s ['based_on'] = $s ['id'];
				unset ( $s ['id'] );
				$new_style = new Style ();
				$new_style->name = $s ['name'];
				if(!$s ['name'])
					header('Location: '.BASE_PATH.'error/400');
				$new_style->user_id = $current_user->id;
				$new_style->based_on = $s ['based_on'];
				$new_style->css = $s ['css'];
				$new_style->scss_varfunc = $s ['scss_varfunc'];
				$new_style->scss = $s ['scss'];
				$new_style->comment = $s ['comment'];
				$new_style->create ();
				$styleid = $new_style->id;
			} else {
				$current_style = new Style ();
				$current_style->createFromID($s ['id']);
				$current_style->name = $s ['name'];
				//$current_style->user_id = $current_user->id;
				//$current_style->based_on = $s ['based_on'];
				$current_style->css = $s ['css'];
				$current_style->scss_varfunc= $s ['scss_varfunc'];
				$current_style->scss = $s ['scss'];
				$current_style->comment = $s ['comment'];
				$current_style->save();
				$styleid=$s ['id'];
			}
		}		
		header( 'Location: style/'.$styleid.'/deck/'.$deckid . '_' . $deck->slug_title ) ;
	}
	function style() {
		$deckid = $_GET ['deck'];
		$styleid = isset ( $_GET ['id'] ) ? $_GET ['id'] : 1;
		$response = 0;
		$deck = new Deck ();
		$deck->createFromIDLite ( $deckid );
		//show error if deck does not exist
		if(!isset($deck->deck_id)){
			header('Location: '.BASE_PATH.'error/404');
			die();
		}
		$slides = $deck->getSlidesFull ();
		$deck->slides = $slides;
		$style = new Style ();
		$style->createFromID ( $styleid );
		//show error if style does not exist
		if(!isset($style->name)){
			header('Location: '.BASE_PATH.'error/404');
			die();
		}
		$styles = $style->getAll ();
		//$this->set ( 'user', $current_user );
		$this->set ( 'response', $response );
		$this->set ( 'deck', $deck );
		$this->set ( 'styleObj', $style );
		$this->set ( 'styles', $styles );
		$this->set ( 'page_title', "SlideWiki Style - ".$style->name );
	}
	function newDeck() {
		// need a better access control here
		if (! $this->_user ['is_authorized']) {
			$this->set ( 'authorized', false );
			//die ( 'Please login in to create a new deck!' );
		} else {
			$this->set ( 'authorized', true );
		}
		if (isset ( $_POST ['submit'] )) {
			$data = $_POST ['deck'];
			$deck = new Deck ();
			$deck->title = $data ['title'];
                        $deck->slug_title = $deck->sluggify($deck->title);
			$user = new User ();
			$user->createFromID ( $this->getCurrentUserID () );
			$deck->user = $user;
			$deck->abstract = $data ['abstract'];
			$deck->default_theme = $data ['theme'];
			$deck->visibility = $data ['visibility'];
                        $deck->language['id'] = $data['language_id'];
                        $deck->language['name'] = $data['language_name'];
			$deck->create ();
			$slide_no=$data ['slideNo'];
			//add slides
			$content=array();
			for($i=1;$i<$slide_no;$i++){
				$slide=new Slide();
				$slide->user=$user;
				$slide->content="<h2>new slide</h2>";
                                $slide->language['id'] = $data['language_id'];
                                $slide->language['name'] = $data['language_name'];
                                $slide->translation_status = 'original';
				$slide->create();
				$content[]=$slide;
			}
			$deck->addContent($content);
			$tag = new Tag ();
			$tag->item_type = "deck";
			$tag->item_id = $deck->id;
			foreach ( explode ( ',', $data ['tags'] ) as $item ) {
				$tag->tag = $item;
				$tag->save ();
			}
			header ( 'Location:deck/' . $deck->id . '_' . $deck->slug_title);
		} else {
			$style = new Style ();
			$styles_list = $style->getAll ();
			$this->set ( 'styles', $styles_list );
                        $user_id = $this->getCurrentUserID();
                        $user = new User();
                        $user->id = $user_id;
                        $default_language = $user->getDefaultLanguage();
                        if(!$default_language['id']){
                        	$default_language['id']='en';
                        	$default_language['name']='English';
                        }
                        $this->set('default_language', $default_language);
                }
        $this->set ( 'page_title', 'SlideWiki - Create a new deck' );
	}
	//handling links for the items : considers last revision id
	function item(){
		$item_id = $_GET ['id'];
		$item_type = $_GET ['type'];
		$item_user = @$_GET ['user'];
		$redirect_link="";
		$hash="";
		switch ($item_type){
			case 'slide':
				$slide = new Slide ();
				$slide->createFromID($item_id);
				if (isset($item_user)){
					$last_revision=$slide->getLastRevisionID($item_user);
				}else{
					$last_revision=$slide->last_revision_id;
				}
				$slide2 = new Slide ();
				$slide2->createFromID($last_revision);
				$slide_usage=$slide2->getUsage();
				if(is_array($slide_usage) && count($slide_usage)==1){
					$hash='tree-'.$slide_usage[0]->id.'-slide-'.$slide2->id.'-'.$slide2->getPosition().'-view';
					$parent_deck = $slide2->getLastOuterParent($slide_usage[0]->id);
                                        $deck = new Deck();
                                        $deck->id = $parent_deck;
                                        $deck->title = $deck->getTitle();
                                        $deck->slug_title = $deck->sluggify($deck->title);
					$redirect_link=BASE_PATH . 'deck/' . $parent_deck . '_' . $deck->slug_title . '#' . $hash;
				}else{			
					$redirect_link=BASE_PATH.'slide/'.$last_revision;
				}
				break;
			case 'deck':
				$deck = new Deck ();
				$deck->createFromID($item_id);
				if (isset($item_user)){
					$last_revision=$deck->getLastRevisionID($item_user);
				}else{
					$last_revision=$deck->last_revision_id;
				}
                                $deck_for_title = new Deck();
                                $deck_for_title->id = $last_revision;
                                $deck_for_title->title = $deck_for_title->getTitle();
                                $deck_for_title->slug_title = $deck_for_title->sluggify($deck->title);
				$deck2 = new Deck ();
				$deck2->createFromID($last_revision);
				$deck_usage=$deck2->getUsage();
				if(is_array($deck_usage) && count($deck_usage)==1){
					$hash = 'tree-' . $deck_usage[0]->id . '-deck-' . $deck2->id . '-' . $deck2->getPosition() . '-view';
					$parent_deck = $deck2->getLastOuterParent($deck_usage[0]->id);
                                        $deck_for_title = new Deck();
                                        $deck_for_title->id = $parent_deck;
                                        $deck_for_title->title = $deck->getTitle();
                                        $deck_for_title->slug_title = $deck->sluggify($deck->title);
					$redirect_link = BASE_PATH . 'deck/' . $parent_deck . '_' . $deck_for_title->slug_title . '#'.$hash;
				}else{			
					$redirect_link = BASE_PATH . 'deck/' . $last_revision . '_' . $deck_for_title->slug_title;
				}				
								
				break;
		}
		header( 'Location: '.$redirect_link ) ;
	}
	function question() {
		$this->_template->disableHeader();
		$this->_template->disableFooter();
		if (isset($_GET['type'])){
			$type = $_GET['type'];
		}else{
			$type='slide';
		}
		$this->set('type', $type);
		
		if (isset($_GET['id'])){
			$id = $_GET['id'];
		}else{
			$id=0;
		}
		$this->set('id', $id);
		
		if (isset($_GET['quest_id'])){
			$quest_id = $_GET['quest_id'];
		}else{
			$quest_id = 0;
		}
		$this->set('quest_id', $quest_id);
		
		if (isset($_GET['mark'])){
			$mark = $_GET['mark'];
		}else{
			$mark=0;
		}
		$this->set('mark', $mark);	
	}
	
	function test() {
               
		if (isset($_GET['id'])){
			$id = $_GET['id'];
		}else{
			$id=0;
		}
                if (isset($_GET['type'])) {
                    $type = $_GET['type'];
                }else {
                    $type = 'auto';
                }
                if($type=='manual' || $type=='user') $type = 'list';
                $this->set('test_id', $id);
                $this->set('type', $type);
                if (isset($_GET['limit'])) {
                    $limit = $_GET['limit'];
                }else {
                    $limit = 0;
                }
		$this->set('limit', $limit);
                if (isset($_GET['mode'])) {
                    $mode = $_GET['mode'];
                }else {
                    $mode = 1;
                }
		$this->set('mode', $mode);
                $questions = array();
                $user_id = $this->getCurrentUserID();
                if (isset($_GET['type']) && $type=='list'){
                    $test = new QList();
                    $test->createFromID($id,$limit,$mode);
                    $questions = $test->questions;                    
                    $test->getMaxForUser($user_id);
                    $attempt = $test->evaluation($limit, $mode, $user_id);
                    $this->set('test_title', $test->title);
                }else {                  
                    $test = new Test();                    
                    $test->createFromItem($id,$limit,$mode);
                    $this->set('test_title', $test->title);
                    $slug_title = $test->sluggify($test->title);
                    $this->set('slug_title', $slug_title);
                    //$this->set('deck_slides', $deck->slides);
                    $attempt = $test->evaluation($limit, $mode, $user_id);
                    $test->getMaxForUser($user_id);
                    $questions = $test->getAllQuestions($mode);
                }
		$this->set('test', json_encode($test));		
		$this->set('questions', $questions);
		$count = count($questions);
		$this->set('count', $count);                
                $this->set('attempt', $attempt);
	}
        function tests(){
            if(isset($_GET['type']))
            $type = $_GET['type'];
            else $type='';
            $this->set('type',$type);
            if(isset($_GET['id']))
            $id=$_GET['id'];
            else $id=0;
            $this->set('id',$id);
            if(isset($_GET['mode']))
            $mode = $_GET['mode'];
            else $mode=1;
            $this->set('mode',$mode);
            if(isset($_GET['limit']))
            $limit = $_GET['limit'];
            else $limit=0;
            $this->set('limit',$limit);
            if($id>0){
                if ($type!='auto' && $type!='exam'){
                   $test = new QList();
                   $test->id = $id;
                }else {
                    $test = new Test();
                    $test->item_id = $id;
                }
                $title = $test->getTitle(); 
            }else $title = 'error';
            $this->set('title', $title);
        }
	

}
