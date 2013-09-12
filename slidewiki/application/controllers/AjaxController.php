<?php
require_once ROOT . DS . 'libraries' . DS . 'backend' . DS . 'PHaml' . DS . 'sass' . DS . 'SassParser.php';
require_once ROOT . DS . 'libraries' . DS . 'backend' . DS . 'html2pdf' . DS . 'html2pdf.class.php';
require_once (ROOT . DS . 'application' . DS . 'library' . DS . 'CacheAPC.php');

class AjaxController extends Controller {
	
	function __construct() {
		@parent::__construct ();
		$this->_noRender = true;
	}
	
	function getDeckTree() {
		$deckId = $_GET ['deck'];
		$cache=new CacheAPC();
		$cache->iTtl=600;
		//if user is not logged in cache tree
		if(!$this->getCurrentUserID() && $cache->bEnabled && $output=$cache->getData('deck_tree_'.$deckId)){
			echo $output;
		}else{
			$deck = new Deck ();
			$deck->createFromIDLite ( $deckId );
			$deck->content = $deck->fetchDeckContentLite ();
			$output=$deck->getTree ();
			if($cache->bEnabled){
				$cache->delData('deck_tree_'.$deckId);
				$cache->setData('deck_tree_'.$deckId, json_encode($output));
			}			
			echo json_encode ($output);	
		}
	}
	function getDeckChildrenTree() {
		$deckId = $_GET ['deck'];
		
		$deck = new Deck ();
		$deck->createFromIDLite ( $deckId );
		$deck->content = $deck->fetchDeckContentLite ();
		echo json_encode ( $deck->getTreeNodes () );
	}	
	function getShortPlaySyncURL(){
			$q=$_GET['q'];
			$u=new Url();
			$u->user_id=$this->getCurrentUserID();
			$short=$u->addget_short_url($q);
			echo "http://slidewiki.org/sp/".$short;
	}
	function getAvatarSrc(){
		$user_id = $_GET ['id'];
		$cache=new CacheAPC();
		$cache->iTtl=36000;
		// cache avatar
		header('content-type: image/jpeg');
		if($cache->bEnabled && $output=$cache->getData('user_avatar_'.$user_id)){
			print $output;
		}else{
			$user=new User();
			$user->createFromID($user_id);
			$email=$user->getEmail();
			$output=file_get_contents("http://www.gravatar.com/avatar/".md5(strtolower(trim($email)))."?d=retro&amp;r=g");
			if($cache->bEnabled){
				$cache->delData('user_avatar_'.$user_id);
				$cache->setData('user_avatar_'.$user_id, $output);
			}			
			print $output;	
		}	
	}
	//TODO: enable multiple deck search
	function searchInDeckContent(){
		$decks= $_GET ['decks'];
		$term = $_GET ['term'];
		$deck_ids=explode(',', $decks);
		foreach ($deck_ids as $id){
			$deck=new Deck();
			$deck->id=$id;
			$results=$deck->searchInSlides($term);
			if(count($results))
				echo json_encode($results);
			else 
				echo 0;
		}
	}
	//TODO: enable multiple slide search
	function searchInSlideContent(){
		$slides= $_GET ['slides'];
		$term = $_GET ['term'];
		$slide_ids=explode(',', $slides);
		foreach ($slide_ids as $id){
			$slide=new Slide();
			$slide->id=$id;
			$results=$slide->searchInContent($term);
			if(count($results))
				echo $slide->id;
			else 
				echo 0;
		}
	}	
	function showDeckContent() {
		$id = $_GET ['id'];
		$deck = new Deck ();
		$deck->createFromID ( $id );
		//send the current user id to deck object as its user_id
		$user = new User ();
		$user->createFromID ( $this->getCurrentUserID () );
		$deck->user = $user;
		$slides = $deck->getSlidesFull ();
		$deck->slides = $slides;
		$deck->is_followed_by_current_user = $deck->user->isFollowing ( 'deck', $deck->deck_id );
		echo json_encode ( $deck );
	}
	function getSlidesByRange() {
		$id = $_GET ['id'];
		$from = $_GET ['from'];
		$to = $_GET ['to'];
		$cache=new CacheAPC();
		$cache->iTtl=600;
		//if user is not logged in cache tree
		if(!$this->getCurrentUserID() && $cache->bEnabled && $output=$cache->getData('deck_'.$id.'_slides_'.$from.'_'.$to)){
			echo $output;
		}else{
			$deck = new Deck ();
			$deck->createFromIDLite ( $id );
			$deck->content=$deck->fetchDeckContentLite ();
			//send the current user id to deck object as its user_id
			$user = new User ();
			$user->createFromID ( $this->getCurrentUserID () );
			$deck->user = $user;
			$slides = $deck->getSlidesByRange ( $from, $to );
                        $deck->slides = $slides;
                        $deck->parent_language = $deck->getParentLanguage();                
			$deck->is_followed_by_current_user = $deck->user->isFollowing ( 'deck', $deck->deck_id );
			if($cache->bEnabled){
				$cache->delData('deck_'.$id.'_slides_'.$from.'_'.$to);
				$cache->setData('deck_'.$id.'_slides_'.$from.'_'.$to, json_encode($deck));
			}			
			echo json_encode ($deck);	
		}		
	}
	//be careful: it uses original slide_id not slide revision!
	function updateSlideDescription(){
		$slide_id = $_GET ['slide_id'];
		$desc = $_GET ['desc'];
		$slide=new Slide();
		$slide->slide_id=$slide_id;
		$slide->setDescription($desc);
	}
	//light version of showDeck content
	function showDeckPreview() {
		$id = $_GET ['id'];
		$deck = new Deck ();
		$deck->createFromIDLite ( $id );
		$deck->content=$deck->fetchDeckContentLite ();
		//send the current user id to deck object as its user_id
		$user = new User ();
		$user->createFromID ( $this->getCurrentUserID () );
		$deck->user = $user;
		$slides = $deck->getSlides ();
		$deck->slides = $slides;
		$deck->is_followed_by_current_user = $deck->user->isFollowing ( 'deck', $deck->deck_id );
		echo json_encode ( $deck );
	}
	private function trimurl_middle($input)
	{
		$input=preg_replace('#^ (?>((?:.*:/+)?[^/]+/.{8})) .{4,} (.{8}) $#x','$1...$2', $input);
		return $input;

	}
	function showDeckPreviewProgressive() {
		$id = $_GET ['id'];
		$minSize = 16;
		$from = $_GET ['from'];
		$cache=new CacheAPC();
		$cache->iTtl=600;
		//if user is not logged in cache deck range
		if(!$this->getCurrentUserID() && $cache->bEnabled && $output=$cache->getData('deck_'.$id.'_range_'.$from)){
			echo $output;
		}else{
			$deck = new Deck ();
			$deck->createFromIDLite ( $id );
			$deck->abstract=nl2br(htmlspecialchars($deck->abstract));
			$deck->initiator=$deck->getInitiator();
			$deck->description=$deck->getDescription();
			$deck->tags=$deck->getTags($id);
			$tmp=array();
			foreach($deck->tags as $t){
				$t=trim($t);
				if($t!=''){
					$tmp[]='<a href="search/keyword/'.$t.'" target="_blank">'.$t.'</a>';
				}
			}
			$deck->tags= join ( ', ', $tmp );
			$deck->last_revision_id = $deck->getLastRevisionID ();
			$deck->content = $deck->fetchDeckContentLite ();
	                $deck->translated_from = $deck->getTranslatedFrom();
	                $deck->translated_from_revision = $deck->getTranslatedFromRevision();
                        $deck->getTranslatedFromChanged();
                        
	                $deck->translation_status = $deck->getTranslationStatus();
			//send the current user id to deck object as its user_id
			$user = new User ();
			$user->createFromID ( $this->getCurrentUserID () );
			$deck->user = $user;
			$slideNumbers = $deck->getNumberOfSlides ();
			if ($slideNumbers > $minSize)
				$slides = $deck->getSlidesByRangeLite ( $from, $from + $minSize - 1 );
			else
				$slides = $deck->getSlidesLite ();
			
			$deck->slide_contributors=$deck->getSlideContributors();
			//remove initiator and creator from contributors
			$contributors_except_oi= array();
			foreach ($deck->slide_contributors as $contibutor){
				$tmp=explode("|", $contibutor);
				if(($tmp[0]!=$deck->owner->id) && ($tmp[0]!=$deck->initiator->id))
					$contributors_except_oi[]=$contibutor;
			}	
			$sources = array ();
			foreach ($deck->slides as $s){
				$dsc=$s->getDescription();
				if($dsc)
					$sources[]=$dsc;
			}	
			$deck->sources= $sources;
			$deck->slide_contributors=$contributors_except_oi;		
			$deck->slides = $slides;
			$deck->is_followed_by_current_user = $deck->user->isFollowing ( 'deck', $deck->deck_id );
			if($cache->bEnabled){
				$cache->delData('deck_'.$id.'_range_'.$from);
				$cache->setData('deck_'.$id.'_range_'.$from, json_encode($deck));
			}			
			echo json_encode ($deck);	
		}		
	}
        
	function getCurrentUserID() {
		return $this->_user ['id'];
	}
	function getCurrentUser() {
		echo $this->getCurrentUserID ();
	}
        
        function getEmailByUsername(){
            $username = $_GET['username'];
            $user = new User();
            $user->username = $username;
            $email = $user->getEmailByUsername();
            echo json_encode($email);
        }
        function passwordRecovery(){
            $userOrMail = $_GET['userOrMail'];
            $user = new User();
            $user->createFromUsernameOrEmail($userOrMail);
            //$email = $user->getEmail();
            $user->passwordRecovery();
            //echo $email;
        }
	//a repeat of Revision model just for increasing performance
	function checkCreatingNewDeckRev() {
		$deck_id = $_GET ['deck'];
		echo $this->needNewDeckRev ( $deck_id );
	}
	//gets multiple decks as input and check if at least one of them cause to new revision
	function checkCreatingNewDeckRevMultiple() {
		$decks = $_GET ['decks'];
		$deck_ids=explode(',', $decks);
		foreach ($deck_ids as $d){
			if($this->needNewDeckRev ($d)){
				echo 1;
				return 1;
			}
		}
		echo 0;
		return 0;
	}
	function needNewDeckRev($deck_id) {
		$deck = new Deck ();
		$deck->createFromIDLite ( $deck_id );
		$current_user_id = $this->getCurrentUserID ();
		if (! $current_user_id)
			return - 1; // user is not logged in
		$user = new User ();
		$user->createFromID ( $current_user_id );
		if (($current_user_id != $deck->owner->id) && ! $user->isMemberOfGroup ( $deck_id, 'editor' )) {
			//user is not the owner of the deck
			return 1;
		} else {
			//user is the owner of the deck, we should check usage
			if (count ( $deck->getUsageExceptUser ( $current_user_id, 1 ) )) {
				//deck is used somewhere else
				return 1;
			} else {
				//deck is not used somewhere else
				return 0;
			}
		}
	}        
	function getDeck() {
		$id = $_GET ['id'];
		$deck = new Deck ();
		$deck->createFromID ( $id );
		echo json_encode ( $deck );
	}
        function getDeckLite(){
            $id = $_GET ['id'];
            $deck = new Deck ();            
            $deck->createFromIDLite ( $id ); 
            $deck->last_revision_id = $deck->getLastRevisionID();
            echo json_encode ( $deck );
        }
	function usageOveral() {
		$type = $_GET ['type'];
		$nodes = array ();
		$edges = array ();
		if ($type == 'deck') {
			$deck_list = new DeckList ();
			$all_decks = $deck_list->getAllDecks ( 50 );
			foreach ( $all_decks as $deck ) {
				//$nodes[]=$deck->title;
				$nodes [$deck->shortenTitle ( $deck->title )] = array ('border' => rand ( 2, 8 ), 'length' => rand ( 450, 8000 ) );
				$deck_usage = $deck->getUsage ();
				if (count ( $deck_usage )) {
					foreach ( $deck_usage as $indeck ) {
						$edges [$deck->shortenTitle ( $deck->title )] [$indeck->shortenTitle ( $indeck->title )] = array ('border' => rand ( 200, 1000 ) );
					}
				}
			}
			echo json_encode ( array ('nodes' => $nodes, 'edges' => $edges ) );
		} else {
			$res = '';
			echo $res;
		}
	}
	function getEditors() {
		$deck_id = $_GET ['deck_id'];
		$userGroup = new UserGroup ();
		$userGroup->getAll ( $deck_id, 'editor' );
		echo json_encode ( $userGroup );
	}
	function addEditor() {
		$deck_id = $_GET ['deck_id'];
		$userOrMail = $_GET ['userOrMail'];
		$user = new User ();
		$user->createFromUsernameOrEmail ( $userOrMail );
		if ($user->id) {
			$deck = new Deck ();
			$deck->id = $deck_id;
			$deck->addUser ( $user, 'editor' );
		}
		$userGroup = new UserGroup ();
		$userGroup->getAll ( $deck_id, 'editor' );
		echo json_encode ( $userGroup );
	}
	function applyForEditorship() {
		$deck_id = $_GET ['deck_id'];
		$pm = $_GET ['pm'];
		$user_id = $this->getCurrentUserID ();
		if(!$user_id)
			return 0;
		$deck=new Deck();
		$deck->createFromIDLite($deck_id);
		$receiver_id = $deck->owner->id;
		$title = 'Apply for editorship (deck #'.$deck_id.':)';
		$content = 'I would like to apply for editorship of your deck entitled "'.$deck->title.'".<br/>*'.$pm;
		$content = $content.'<br/><a href="http://slidewiki.org/confirmEditorship/user/'.$user_id.'/deck/'.$deck_id.'">Confirm my editorship</a>';
		$m=new Msg();
		$m->sender_id=$user_id;
		$m->receiver_id=$receiver_id;
		$m->title=$title;
		$m->content=$content;
		$m->msg_type="editorship";
		$m->save();
		$m->send();			
	}	
	function addEditorToAllSubdecks() {
		$deck_id = $_GET ['deck_id'];
		$userOrMail = $_GET ['userOrMail'];
		$is_editor = $_GET ['is_editor'];
		$user = new User ();
		$user->createFromUsernameOrEmail ( $userOrMail );
		//assign to current deck
		if ($user->id) {
			$deck = new Deck ();
			$deck->id = $deck_id;
			$deck->addUser ( $user, 'editor' );
		}else{
			echo 0;
			return 0;
		}
		//assign to all subdecks of owner
		$deck_obj = new Deck ();
		$deck_obj->createFromIDLite ( $deck_id );
		if(!$is_editor)
			$deck_obj->assignEditorToSubdecks ( $user,0 );
		else{
			//prevent updating when user is not logged in
			$current_user_id=$this->getCurrentUserID ();
			if(!$current_user_id)
					return 0;			
			$e = new User ();
			$e->createFromID($current_user_id);
			$deck_obj->assignEditorToSubdecks ( $user,$e );
		}	
		
		$userGroup = new UserGroup ();
		$userGroup->getAll ( $deck_id, 'editor' );
		echo json_encode ( $userGroup );
	}
	function removeEditor() {
		$deck_id = $_GET ['deck_id'];
		$user_id = $_GET ['user_id'];
		$user = new User ();
		$user->createFromID ( $user_id );
		$deck = new Deck ();
		$deck->id = $deck_id;
		$deck->removeUser ( $user, 'editor' );
		$userGroup = new UserGroup ();
		$userGroup->getAll ( $deck_id, 'editor' );
		echo json_encode ( $userGroup );
	}
	function removeEditorFromAllSubdecks() {
		$deck_id = $_GET ['deck_id'];
		$user_id = $_GET ['user_id'];
		$user = new User ();
		$user->createFromID ( $user_id );
		$deck = new Deck ();
		$deck->id = $deck_id;
		//remove from the current deck
		$deck->removeUser ( $user, 'editor' );
		//remove from all subdecks of owner
		$deck_obj = new Deck ();
		$deck_obj->createFromID ( $deck_id );
		$deck_obj->removeEditorFromSubdecks ( $user );
		
		$userGroup = new UserGroup ();
		$userGroup->getAll ( $deck_id, 'editor' );
		echo json_encode ( $userGroup );
	}
	function sendMsg() {
		//prevent updating when user is not logged in
		$current_user_id=$this->getCurrentUserID ();
		if(!$current_user_id)
				return 0;		
		$receiver_id = $_GET ['receiver_id'];
		$title = $_GET ['title'];
		$content = $_GET ['content'];
		$m=new Msg();
		$m->sender_id=$current_user_id;
		$m->receiver_id=$receiver_id;
		$m->title=$title;
		$m->content=$content;
		$m->save();
		$m->send();			
	}
	function editDeck() {
		$id = $_GET ['id'];
		$deck = new Deck ();
		$deck->createFromIDLite ( $id );
		$deck->description = $deck->getDescription ();
		$deck->initiator = $deck->getInitiator ();
		$deck->editors=$deck->getEditors ();
		$deck->editor = count ( $deck->editors );
		/*
		 * Decide about making new revision of deck
		 * First check if it is used somewhere else
		 * If it is used in several places by the current user, show her a cascade check box
		 */
		$current_user_id = $this->getCurrentUserID ();
		//prevent updating when user is not logged in
		if(!$current_user_id)
				return 0;		
		//$deck_usage_all = count($deck->getUsage($current_user_id));
		$deck_usage_except_user = count ( $deck->getUsageExceptUser ( $current_user_id ) );
		//$deck_usage_user=$deck_usage_all-$deck_usage_except_user;
		//todo: handle cascading deck update
		$deck->cascade_update = 0;
		if ($deck_usage_except_user) {
			$deck->save_as_new_revision = 1;
		} else {
			$deck->save_as_new_revision = 0;
		}
		$style = new Style ();
		$styles_list = $style->getAll ();
		$deck->styles = $styles_list;
		$deck->tags = join ( ',', $deck->getTags ( $id ) );
		$transition = new Transition ();
		$transitions_list = $transition->getAll ();
		$deck->transitions = $transitions_list;
		
		echo json_encode ( $deck );
	}
	function saveDeck() {
		//prevent updating when user is not logged in
		$current_user_id=$this->getCurrentUserID ();
		if(!$current_user_id)
				return 0;		
		$id = $_GET ['id'];
		$root_deck = $_GET ['root_deck'];
		$node_id = $_GET ['node_id'];
		$data = $_GET ['deck'];
		$deck = new Deck ();
		$deck->createFromID ( $id );
		$user = new User ();
		$user->createFromID ( $current_user_id );
		$deck->user = $user;
		//we only consider change in the title of decks to create new revisions
		$small_change = 1;
		if (trim ( $data ['title'] ) != $deck->title) {
			$small_change = 0;
		}
		$deck->title = urldecode($data ['title']);
                $deck->slug_title = $deck->sluggify($deck->title);
        //var_dump($_GET);
		$deck->default_theme = $data ['theme'];
		$deck->default_transition = $data ['transition'];
		$deck->visibility = $data ['visibility'];
		$deck->save_as_new_revision = $data ['save_as_new_revision'];
		if (! isset ( $data ['cascade_update'] ))
			$deck->cascade_update = 0;
		else
			$deck->cascade_update = 1;
		$deck->abstract = urldecode($data ['abstract']);
		$deck->footer_text = isset ( $data ['footer_text'] ) ? urldecode($data ['footer_text']) : '';
		//if user is the original owner
		
		if ($user->id == $deck->getOriginalOwnerID ()) {
			$deck->setDescription ( @$data ['description'] );
		}
		if ($deck->save_as_new_revision == 0 || $small_change) {
			//just update the deck not making a new revision
			$deck->save ();
			if ($id == $root_deck) {
				$results = array ('root_changed' => $id, 'slug_title' => $deck->slug_title, 'items' => array (array ('rev_id' => $node_id ) ), 'force_refresh' => 1 );
			} else
				$results = array ('root_changed' => 0, 'items' => array (array ('rev_id' => $node_id ) ) );
		} else {
			//$deck_usage = $deck->getUsage ();
			$deck->commit ( $deck->id );
			//if node is the root, no need for hierarchichal revisioning
			if ($id == $root_deck) {
				$results = array ('root_changed' => $deck->id, 'slug_title' => $deck->slug_title, 'items' => array (array ('rev_id' => 'tree-0-deck-' . $deck->id . '-1' ) ) );
			} else {
				$tmp = new Deck ();
				$tmp->createFromIDLite ( $root_deck );
				$tmp->content = $tmp->fetchDeckContentLite ();
				$tree = $tmp->getTree ();
				$rev = new Revision ( $tree );
				$changeset = array ('user_id' => $user->id, 'items' => array (array ('item_id' => $node_id ) ) );
				$results = $rev->handleChange ( $changeset );
				$deckid = $results ['items'] [0] ['target_deck_id'];
				$new_node_id = 'tree-' . $deckid . '-deck-' . $rev->getNodeRevID ( $node_id ) . '-' . $rev->getNodePositionFromID ( $node_id );
				$tmp = new Deck ();
				if ($results ['root_changed']) {
					$tmp->createFromIDLite ( $results ['root_changed'] );
				} else {
					$tmp->createFromIDLite ( $root_deck );
				}
				$tmp->content = $tmp->fetchDeckContentLite ();
				$tree = $tmp->getTree ();
				$rev = new Revision ( $tree );
				//var_dump($rev->parentHash);
				$parent_node = $rev->parentHash [$new_node_id];
				$parent_node_id = $rev->getNodeRevID ( $parent_node );
				$parent_obj = new Deck ();
				$parent_obj->createFromID ( $parent_node_id );
				$parent_obj->replaceContentWith ( $deck->id, $id, 'deck' );
				$results ['items'] [0] ['rev_id'] = 'tree-' . $deckid . '-deck-' . $deck->id . '-' . $rev->getNodePositionFromID ( $node_id );
			}
		
		//todo: handling cascade deck update
		/*
			if ($deck->cascade_update == 1) {
				//update all instances of deck used by the current user
				foreach ( $deck_usage as $element ) {
					$element->replaceContentWith ( $deck->id, $id, 'deck' );
				}
			} else {
				//only update the current instance
				foreach ( $deck_usage as $element ) {
					if ($element->id == $parent_deck_id) {
						$element->replaceContentWith ( $deck->id, $id, 'deck' );
					}
				}
			}
			*/
		}
		$tag = new Tag ();
		$tag->item_type = "deck";
		$tag->item_id = $deck->id;
		$tag->deleteAllItemTags ();
		foreach ( explode ( ',', $data ['tags'] ) as $item ) {
			$tag->tag = $item;
			$tag->save ();
		}
		echo json_encode ( $results );
	}
//        function filterLatex(){
//            $slide_id = $_GET['slide_id'];
//            $slide = new Slide();
//            $slide->createFromID($slide_id);
//            $array = array();
//            $slide->symbolsToLatex();
//            $array = $slide->filterTags();
//            echo $array;
//            $content = $slide->content;
//            $text = $slide->addTagsBack($content,$array);
//            $slide->content = $text;
//            $slide->latexToSymbols();
//            echo $slide->content;
//        }
	function newDeckRevision() {
		//prevent updating when user is not logged in
		$current_user_id=$this->getCurrentUserID ();
		if(!$current_user_id)
				return 0;		
		$id = $_GET ['id'];
		$root_deck = $_GET ['root_deck'];
		$node_id = $_GET ['node_id'];
		$data = $_GET ['deck'];
		$deck = new Deck ();
		$deck->createFromIDLite ( $id );
		$deck->content = $deck->fetchDeckContentLite ();
		$user = new User ();
		$user->createFromID ( $current_user_id );
		$deck->user = $user;
		$deck->title = $data ['title'];
                $deck->slug_title = $deck->sluggify($deck->title);
		$deck->default_theme = $data ['theme'];
		$deck->default_transition = $data ['transition'];
		$deck->abstract = htmlspecialchars($data ['abstract']);
		$deck->footer_text = isset ( $data ['footer_text'] ) ? $data ['footer_text'] : '';
		//if user is the original owner
		if ($user->id == $deck->getOriginalOwnerID ()) {
			$deck->setDescription ( $data ['description'] );
		}
		$deck->commit ( $deck->id );
		//if node is the root, no need for hierarchichal revisioning
		if ($id == $root_deck) {
			$results = array ('root_changed' => $deck->id, 'slug_title' => $deck->slug_title, 'items' => array (array ('rev_id' => 'tree-0-deck-' . $deck->id . '-1' ) ) );
		} else {
			$tmp = new Deck ();
			$tmp->createFromIDLite ( $root_deck );
			$tmp->content = $tmp->fetchDeckContentLite ();
			$tree = $tmp->getTree ();
			$rev = new Revision ( $tree );
			$changeset = array ('user_id' => $user->id, 'items' => array (array ('item_id' => $node_id ) ) );
			$results = $rev->handleChange ( $changeset );
			$deckid = $results ['items'] [0] ['target_deck_id'];
			$new_node_id = 'tree-' . $deckid . '-deck-' . $rev->getNodeRevID ( $node_id ) . '-' . $rev->getNodePositionFromID ( $node_id );
			$tmp = new Deck ();
			if ($results ['root_changed']) {
				$tmp->createFromIDLite ( $results ['root_changed'] );
			} else {
				$tmp->createFromIDLite ( $root_deck );
			}
			$tmp->content = $tmp->fetchDeckContentLite ();
			$tree = $tmp->getTree ();
			$rev = new Revision ( $tree );
			//var_dump($rev->parentHash);
			$parent_node = $rev->parentHash [$new_node_id];
			$parent_node_id = $rev->getNodeRevID ( $parent_node );
			$parent_obj = new Deck ();
			$parent_obj->createFromIDLite ( $parent_node_id );
			$parent_obj->replaceContentWith ( $deck->id, $id, 'deck' );
			$results ['items'] [0] ['rev_id'] = 'tree-' . $deckid . '-deck-' . $deck->id . '-' . $rev->getNodePositionFromID ( $node_id );
		}
		$tag = new Tag ();
		$tag->item_type = "deck";
		$tag->item_id = $deck->id;
		$tag->deleteAllItemTags ();
		foreach ( explode ( ',', $data ['tags'] ) as $item ) {
			$tag->tag = $item;
			$tag->save ();
		}
		echo json_encode ( $results );
	}
	function editSlide() {
		$id = $_GET ['id'];
		$deck_id = $_GET ['deck_id'];
		$pos = $_GET ['pos'];
		$root_deck = $_GET ['root_deck'];
		$slide = new Slide ();
		$slide->createFromID ( $id );
                $deck = new Deck();
                $deck->id = $deck_id;
                $deck->title = $deck->getTitle();
                $deck->slug_title = $deck->sluggify($deck->title);
		$slide->deck = $deck;
		$slide->position = $pos;
		$identicals = $slide->getIdenticals ( $root_deck );
		if (count ( $identicals ))
			$slide->identicals = $identicals;
		else
			$slide->identicals = 0;
		echo json_encode ( $slide );
	}        
	function getSlide() {
		$id = $_GET ['id'];
		
		$slide = new Slide ();
		$slide->createFromID ( $id );
		$slide->contributors = $slide->getContributors ();
		echo json_encode ( $slide );
	}
	function getSlideContent() {
		$id = $_GET ['id'];
		
		$slide = new Slide ();
		$slide->createFromIDLite($id);
		$slide->body = $slide->getBody ();
		$slide->content='';
		echo json_encode ( $slide );
	}	
	//do not create deck revisions, only slide revisions are created
	function saveAsMinorChanges(){
		//prevent updating when user is not logged in
		$current_user_id=$this->getCurrentUserID ();
		if(!$current_user_id)
				return 0;		
		$user = new User ();
		$user->createFromID ( $current_user_id );
		$input = $_POST ['query'];
		$input = preg_replace ( "/[\n\r]/", "", $input );	
		$inputs = json_decode ( $input );	
		foreach ( $inputs->items as $key => $element ) {
			$tmp = explode ( '-', $element->id );
			$old_slide_id = $tmp [3];
			$slide = new Slide ();
			$slide->createFromID ( $old_slide_id );
			$slide->content = '<h2>' . $element->title . '</h2>' . $element->body;
			//$slide->content = '<h2>'.stripslashes($element->title).'</h2>'.stripslashes($element->body);
			$slide->comment = 'minor revision';
			$slide->note = $element->note;
			//$slide->note = stripslashes($element->note);
			$slide->user = $user;
			$new_slide_id = $slide->commit ( $old_slide_id );
		}	
		echo 1;
	}
	//handles saving multiple slide changes
	function saveSlideContentChanges() {
		//prevent updating when user is not logged in
		$current_user_id= $this->getCurrentUserID ();
		if(!$current_user_id)
			return 0;
		$user = new User ();
		$user->createFromID ($current_user_id );
		$root_deck = $_POST ['root_deck'];
		$input = $_POST ['query'];
		$input = preg_replace ( "/[\n\r]/", "", $input );
		//echo $input;
		//var_dump($input);
		//array containing all the change content
		$inputs = json_decode ( $input );
		//prepare change input for handling new deck revisions
		$changeset = array ('user_id' => $user->id );
		//var_dump($inputs);
		foreach ( $inputs->items as $element ) {
			$changeset ['items'] [] = array ('item_id' => $element->id );
		}
		$deck = new Deck ();
		$deck->createFromIDLite ( $root_deck );
		$deck->content = $deck->fetchDeckContentLite ();
		$tree = $deck->getTree ();
		$rev = new Revision ( $tree );
		$results = $rev->handleChange ( $changeset );
		//we have three paralell arrays:inputs, changeset and results
		foreach ( $inputs->items as $key => $element ) {
			$old_slide_id = $rev->getNodeRevID ( $element->id );
			$slide = new Slide ();
			$slide->createFromID ( $old_slide_id );
			$slide->content = '<h2>' . $element->title . '</h2>' . $element->body;
			//$slide->content = '<h2>'.stripslashes($element->title).'</h2>'.stripslashes($element->body);
			$slide->comment = '';
			$slide->note = $element->note;
			//$slide->note = stripslashes($element->note);
                        $deck_for_title = new Deck();
                        $deck_for_title->id = $results ['items'] [$key] ['target_deck_id'];
                        $deck_for_title->title = $deck_for_title->getTitle();
                        $deck_for_title->slug_title = $deck_for_title->sluggify($deck_for_title->title);
			$slide->deck = $deck_for_title;
			$slide->position = $rev->getNodePositionFromID ( $element->id );
			$slide->user = $user;
                        if ($slide->translation_status == 'google'){
                            $slide->translation_status = 'revised';
                        }
                        $translator = $slide->getTranslator();
                        if ($translator){
                            $slide->translator_id = $translator->id;
                        }else{
                            $slide->translator_id = null;
                        }                        
			$new_slide_id = $slide->commit ( $old_slide_id );
			//update results with new slide revision
			$results ['items'] [$key] ['rev_id'] = $new_slide_id;
			$tmp = new Deck ();
			$tmp->id=$results ['items'] [$key] ['target_deck_id'];
			//todo:think about cascading problems
			$tmp->replaceContentByPosition ( $new_slide_id, $old_slide_id, 'slide', $slide->position );
		}
		//$root_changed=$results['root_changed'];
		//echo $root_changed;
		echo json_encode ( $results );
	}
	//handles saving multiple slide changes when replaced content by a term
	function saveSlidesByReplace() {
		//prevent updating when user is not logged in
		$current_user_id= $this->getCurrentUserID ();
		if(!$current_user_id)
			return 0;
		$user = new User ();
		$user->createFromID ($current_user_id );
		$root_deck = $_GET ['root_deck'];
		$term = $_GET ['term'];
		$rep = $_GET ['rep'];
		$slides = $_GET ['slides'];
		$slide_ids=explode(',', $slides);
		
		$changeset = array ('user_id' => $user->id );
		//var_dump($inputs);
		foreach ( $slide_ids as $element ) {
			$changeset ['items'] [] = array ('item_id' => $element );
		}
		$deck = new Deck ();
		$deck->createFromIDLite ( $root_deck );
		$deck->content = $deck->fetchDeckContentLite ();
		$tree = $deck->getTree ();
		$rev = new Revision ( $tree );
		$results = $rev->handleChange ( $changeset );
		//we have three paralell arrays:inputs, changeset and results
		foreach ( $slide_ids as $key=>$element ) {
			$old_slide_id = $rev->getNodeRevID ( $element);
			$slide = new Slide ();
			$slide->createFromID ( $old_slide_id );
			$slide->content= str_ireplace($term, $rep, $slide->content);
			$slide->comment = 'replaced "'.$term.'" by "'.$rep.'"';
                        $deck_for_title = new Deck();
                        $deck_for_title->id = $results ['items'] [$key] ['target_deck_id'];
                        $deck_for_title->title = $deck_for_title->getTitle();
                        $deck_for_title->slug_title = $deck_for_title->sluggify($deck_for_title->title);
			$slide->deck = $deck_for_title;
			$slide->position = $rev->getNodePositionFromID ( $element );
			$slide->user = $user;
                        if ($slide->translation_status == 'google'){
                            $slide->translation_status = 'revised';
                        }
                        $translator = $slide->getTranslator();
                        if ($translator){
                            $slide->translator_id = $translator->id;
                        }else{
                            $slide->translator_id = null;
                        }                        
			$new_slide_id = $slide->commit ( $old_slide_id );
			//update results with new slide revision
			$results ['items'] [$key] ['rev_id'] = $new_slide_id;
			$tmp = new Deck ();
			$tmp->id=$results ['items'] [$key] ['target_deck_id'];
			//todo:think about cascading problems
			$tmp->replaceContentByPosition ( $new_slide_id, $old_slide_id, 'slide', $slide->position );
		}
		//$root_changed=$results['root_changed'];
		//echo $root_changed;
		echo json_encode ( $results );
	}	
        function remove_google(){
            $slide_id = $_GET['slide_id'];
            $user_id = $this->getCurrentUserID();
            //prevent updating when user is not logged in
			if(!$user_id)
				return 0;	    
            $slide = new Slide();
            $slide->id = $slide_id;
            $slide->translator_id = $user_id;
            $slide->removeGoogle();
        }
	function replaceItem() {
		//prevent updating when user is not logged in
		$current_user_id=$this->getCurrentUserID ();
		if(!$current_user_id)
				return 0;
		$user = new User ();
		$user->createFromID ( $current_user_id );
		$root_deck = $_GET ['root_deck'];
		$node_id = $_GET ['node_id'];
		$new_item_id = $_GET ['new_item_id'];
                $deck_for_title = new Deck();
                $deck_for_title->id = $new_item_id;
                $deck_for_title->title = $deck_for_title->getTitle();
                $deck_for_title->slug_title = $deck_for_title->sluggify($deck_for_title->title);
		$type = $_GET ['type'];
		$changeset = array ('user_id' => $user->id, 'items' => array (array ('item_id' => $node_id ) ) );
		$deck = new Deck ();
		$deck->createFromIDLite ( $root_deck );
		$deck->content = $deck->fetchDeckContentLite ();
		$tree = $deck->getTree ();
		$rev = new Revision ( $tree );
		if ($type == 'deck'){ 
			if ($deck->parentsChildren ( $new_item_id )){
				echo '-1'; return;
			}elseif ($type == 'deck' && $rev->getNodeRevID ( $node_id ) == $root_deck){
				$results = array ('root_changed' => $new_item_id, 'slug_title'=>$deck_for_title->slug_title, 'items' => array (array ('rev_id' => 'tree-0-deck-' . $new_item_id . '-1' ) ) );
				echo json_encode ( $results );
				return;
			}
		}
		//handle new deck revision creation
		$results = $rev->handleChange ( $changeset );
		//id of old or newly created deck after revisioning
		$deckid = $results ['items'] [0] ['target_deck_id'];
		$parent = new deck ();
		$parent->createFromIDLite ( $deckid );
		//replace content
		$parent->replaceContentByPosition ( $new_item_id, $rev->getNodeRevID ( $node_id ), $type, $rev->getNodePositionFromID ( $node_id ) );
		$results ['items'] [0] ['rev_id'] = 'tree-' . $deckid . '-' . $type . '-' . $new_item_id . '-' . $rev->getNodePositionFromID ( $node_id );
		echo json_encode ( $results );
	}
	function showSlideUsage() {
		$id = $_GET ['id'];
		$slide = new Slide ();
		$slide->createFromIDLite ( $id );
		$slide->usage = $slide->getUsage ();
		echo json_encode ( $slide );
	}
	function showDeckUsage() {
		$id = $_GET ['id'];
		$deck = new Deck ();
		$deck->createFromIDLite ( $id );
		$deck->usage = $deck->getUsage ();
		echo json_encode ( $deck );
	}
	function createUsagePath(){
		$this->_template->disableHeader();
		$this->_template->disableFooter();
		$this->_noRender = true;
		$deck_id=$_GET['deck'];
		$deck=new Deck();
		$deck->createFromIDLite($deck_id);	
		$this->prepareUsagePath($deck,'');
	}	
	private function prepareUsagePath($deck,$prev){
		$usage=$deck->getUsage();
		//echo count($usage).'<br>';
		if (count($usage)){
			foreach ($usage as $dc){
				$this->prepareUsagePath($dc,'<a href="deck/'.$deck->id.'_'.$deck->slug_title.'">'.$deck->shortenTitle($deck->title).'<sub>'.$deck->getRevisionNumber($deck->id).'</sub></a><b> > </b>'.$prev);
			}				
		}else{
			echo '<a href="deck/'.$deck->id.'_'.$deck->slug_title.'">'.$deck->shortenTitle($deck->title).'<sub>'.$deck->getRevisionNumber($deck->id).'</sub></a> <b> > </b>'.$prev.'<br>';
		}
	}	
	function showSlideRevisions() {
		$id = $_GET ['id'];
//                if ($_GET['deck_id']){
//                    $deck_id = $_GET['deck_id'];
//                }
		$slide = new Slide ();
		$slide->createFromID ( $id );
		$slide->revisions = $slide->getRevisions ();
                $slide->parent_language = $slide->getParentlanguage();
                //$slide->getLinkInDeck($deck_id);
		echo json_encode ( $slide );
	}
	function showDeckRevisions() {
		$id = $_GET ['id'];
		$deck = new Deck ();
		$deck->createFromIDLite ( $id );
		$deck->revisions = $deck->getRevisions ();
                $deck->getTranslatedFromChanged();
                echo json_encode ( $deck );
	}        
	function getSubDeckComments(){
		$id = $_GET ['id'];
		$cache=new CacheAPC();
		$cache->iTtl=1800;
		//if user is not logged in cache tree
		if($cache->bEnabled && $output=$cache->getData('deck_subcomments_'.$id)){
			echo $output;
		}else{
			$deck = new Deck ();
			$deck->createFromIDLite ( $id );
			$deck->comments =$deck->getSubdeckComments();
			if($cache->bEnabled){
				$cache->delData('deck_subcomments_'.$id);
				$cache->setData('deck_subcomments_'.$id, json_encode($deck));
			}			
			echo json_encode ($deck);	
		}		
	}
        function getTranslatedFromLast(){
            $id = $_GET['id'];
            $deck = new Deck();
            $deck->id = $id;
            $deck->deck_id = $deck->getBasicID();
            $translated_from = $deck->getTranslatedFromRevision();
            $result = new DeckList();
            $translated_deck = new Deck();
            $translated_deck->createFromIDLite($translated_from);
            $last_revision = $translated_deck->getLastRevisionID();
            $last_deck = new Deck();
            $last_deck->createFromID($last_revision);
            $result->decks[] = $translated_from;
            $result->decks[] = $last_revision;
            $result->languages = $deck->getLanguage();
            echo json_encode($result);
        }
	function showDiscussion() {
		$item = $_GET ['item'];
		$id = $_GET ['id'];
		if ($item == "deck") {
			$deck = new Deck ();
			$deck->createFromIDLite ( $id );
			$deck->comments = $deck->getAllRevComments ();
			echo json_encode ( $deck );
		} elseif ($item == "slide") {
			$slide = new Slide ();
			$slide->createFromIDLite ( $id );
			$slide->comments = $slide->getAllRevComments ();
			echo json_encode ( $slide );
		}
	}
	function saveComment() {
		$type = $_GET ['type'];
		$id = $_GET ['id'];
		$title = $_GET ['title'];
		$text = $_GET ['text'];
		$comment = new Comment ();
		$comment->title = $title;
		$comment->text = $text;
		$comment->item_id = $id;
		$comment->item_type = $type;
		//prevent updating when user is not logged in
		$current_user_id=$this->getCurrentUserID ();
		if(!$current_user_id)
				return 0;		
		$user = new User ();
		$user->createFromID ( $current_user_id );
		$comment->user = $user;
		$new_comment_id = $comment->create ();
		$comment->createFromID ( $new_comment_id );
		
		$m=new Msg();
		$m->sender_id=$current_user_id;
		$m->msg_type='comment';	
		$m->content=$comment->text ;
		$m->title=$comment->title;			
		if($type=='deck'){
			$tmp=new Deck();
			$tmp->id=$id;
			$m->receiver_id=$tmp->getOwnerID();
			$m->content=$m->content.'<br/>'.'This comment is sent '.$comment->creationTime.' on the following item:<br/>'.'http://slidewiki.org/deck/'.$comment->item_id.'_'.$comment->slug_title;					
		}elseif($type=='slide'){
			$tmp=new Slide();
			$tmp->id=$id;			
			$m->receiver_id=$tmp->getOwnerID();
			$m->content=$m->content.'<br/>'.'This comment is sent '.$comment->creationTime.' on the following item:<br/>'.'http://slidewiki.org/slide/'.$comment->item_id;		
		}elseif($type=='comment'){
			$tmp=new Comment();
			$tmp->createFromID($id);		
			$m->receiver_id=$tmp->user->id;
			$m->content=$m->content.'<br/>'.'This comment is sent '.$comment->creationTime.' on the following item:<br/>'.'http://slidewiki.org/'.$tmp->item_type.'/'.$tmp->item_id;	
		}                
		if($m->receiver_id!=$m->sender_id){
			$m->save();
			$m->send();
		}
		echo json_encode ( $comment );
	}
	function getTestDescription() {
		$type = $_GET ['type'];
		$id = $_GET ['id'];
		if ($type == 'auto') {
			$test = new Test ();
			$test->createFromItem ( $id );
		} else {
			$test = new QList ();
			$test->createFromID ( $id );
		}
		$user_id = $this->getCurrentUserID ();	
		$test->max_for_user = $test->getMaxForUser ( $user_id );
		echo json_encode ( $test );
	}
	/*-------------------------- questions staff ---------------------------*/
	// TODO : fix the function
	/*function needToEdit($old_question, $new_question) {
		if($old_question->difficulty != $new_question->difficulty) return 1;
		if($old_question->question != $new_question->question) return 1;
		$old_answers = array();
		$new_answers = array();
		foreach ($old_question->answers as $answer) {
			$old_answers[]=$answer;
		}
		foreach ($new_question->answers as $answer) {
			$new_answers[]=$answer;	
		}
		sort($old_answers, SORT_STRING);
		sort($new_answers, SORT_STRING);
		if (count($old_distracters)!=count($new_distracters)) {
			return 1;
		} else {
			for ($i=0; $i<(count($old_distracters)); $i++) {
				if($old_distracters[$i] != $new_distracters[$i]) return 1;
			}
		}
		foreach ($old_question->right_answer as $right_answer) {
			$old_right_answers[]=$right_answer;
		}
		foreach ($new_question->right_answer as $right_answer) {
			$new_right_answers[]=$right_answer;
		}
		sort($old_right_answers, SORT_STRING);
		sort($new_right_answers, SORT_STRING);
		if (count($old_right_answers)!=count($new_right_answers)) {
			return 1;
		} else {
			for ($i=0; $i<(count($old_right_answers)); $i++) {
				if($old_right_answers[$i] != $new_right_answers[$i]) return 1;
			}
		}
		return 0;				
	}*/
	function showSlideQuestions() {
		$id = $_GET ['id'];
		$slide = new Slide ();
		$slide->createFromIDLite ( $id );
		$slide->questions = $slide->getQuestions ();
                foreach($slide->questions['accepted'] as $question){
                    $question->question = stripslashes($question->question);
                }
		echo json_encode ( $slide );
	}
	function showDeckQuestions() {
		$id = $_GET ['id'];
		$cache=new CacheAPC();
		$cache->iTtl=600;
		//if user is not logged in cache tree
		if(!$this->getCurrentUserID() && $cache->bEnabled && $output=$cache->getData('deck_question_'.$id)){
			echo $output;
		}else{
			$deck = new Deck ();
			$deck->createFromIDLite ( $id );
			$deck->questions = $deck->getQuestions ();
//                        foreach($deck->questions['accepted'] as $question){
//                                $question->question = stripslashes($question->question);
//                        }
			if($cache->bEnabled){
				$cache->delData('deck_question_'.$id);
				$cache->setData('deck_question_'.$id, json_encode($deck));
			}			
			echo json_encode ($deck);	
		}		
	}
	function getTestsByType() {
		$type = $_GET ['type'];
		switch ($type) {
			case 'user' :
				$user_id = $this->getCurrentUserID ();			
				$user = new User ();
				$user->id = $user_id;
				$result = array ();
				foreach ( $user->getOwnLists () as $list ) {
					$list_obj = new QList ();
					$list_obj->createFromID ( $list ['id'] );
					$result [] = $list_obj;
				}
				echo json_encode ( $result );
				break;
			case 'manual' :
				$list = new QList ();
				$result = array ();
				foreach ( $list->getAll () as $alist ) {
					$list_obj = new QList ();
					$list_obj->createFromID ( $alist ['id'] );
					$result [] = $list_obj;
				}
				echo json_encode ( $result );
				break;
			case 'auto' :
				echo json_encode ( $this->getAutoTests () );
				break;
			default :
				$user_id = $this->getCurrentUserID ();				
				$user = new User ();
				$user->id = $user_id;
				echo json_encode ( $user->getOwnLists () );
				break;
		}
	}
	//TODO refactor the code
	function saveQuestion() {
		if (isset ( $_GET ['id'] ))
			$id = $_GET ['id'];
		$quest_id = $_GET ['quest_id'];
		if (isset ( $_GET ['difficulty'] )) {
			$difficulty = $_GET ['difficulty'];
		} else {
			$difficulty = - 1;
		}
		if (isset ( $_GET ['question'] ))
			$question = $_GET ['question'];
		
		else
			$question = - 1;
		
		$mark = 'accepted';
		$current_userId = $this->getCurrentUserID ();
		//prevent updating when user is not logged in
		if(!$current_userId)
				return 0;		
		$user = new User ();
		$user->createFromID ( $current_userId );
		$new_question = new Question ();
		$new_question->question = $question;
		$new_question->mark = $mark;
		$new_question->difficulty = $difficulty;
		$new_question->diff_count = 0;
		$new_question->user = $user;
		if (isset ( $quest_id ) && $quest_id > 0) {
			$old_question = new Question ();
			$old_question->createFromID ( $quest_id );
			//if ($this->needToEdit($old_question, $new_question)) {  //need to fix the function
			$quest_ownerId = $old_question->user->id;
			$new_question->item_id = $old_question->item_id;
			if ($quest_ownerId == $current_userId) { //simple editing
				$new_question->id = $old_question->id;
				if ($difficulty < 0) {
					$new_question->difficulty = $old_question->difficulty;
				}
				$new_question->diff_count = $old_question->diff_count;
				$new_question->unMarkDoubtful ();
				$new_question->edit ();
				//echo (htmlentities($new_question->question, ENT_QUOTES));
				echo json_encode ( $new_question->id );
			
		//
			} else { //based_on creating						
				$new_question->based_on = $quest_id;
				if ($difficulty < 0) {
					$new_question->difficulty = $old_question->difficulty;
					$new_question->diff_count = $old_question->diff_count;
				} else {
					$new_question->diff_count = $old_question->diff_count;
				}
				if ($question < 0)
					$new_question->question = $old_question->question;
				$new_question_id = $new_question->create ();
				echo json_encode ( $new_question_id );
			}
		
		//}
		} else { //create absolutely new question		
			$slide = new Slide ();
			$slide->id = $id;
			$basic_id = $slide->getBasicId ();
			$new_question->item_id = $basic_id;
			$new_question->based_on = NULL;
			$new_question_id = $new_question->create ();
			echo json_encode ( $new_question_id );
		}
	}
	function getQuestion() {
            $id = $_GET ['id'];
            $quest_model = new Question ();
            $quest_model->createFromID ( $id );
            echo json_encode ( $quest_model );
	}
	function getStats(){
		$cache=new CacheAPC();
		$cache->iTtl=36000;
		$deckList = new DeckList();
		if($cache->bEnabled && $output=$cache->getData('statistics')){
			echo $output;
		}else{
			$stat=new Statistics();
			$stat->calculateAll();
			if($cache->bEnabled){
				$cache->delData('statistics');
				$cache->setData('statistics', json_encode($stat));
			}
			echo json_encode ( $stat );	
		}	
	}
	function getNewDecks(){
		$cache=new CacheAPC();
		$deckList = new DeckList();
		if($cache->bEnabled && $output=$cache->getData('new_decks')){
			echo $output;
		}else{
			$decks = $deckList->getAllDecks(5,true);
			foreach($decks as $deck) {
				$all_slides=$deck->getSlidesLite(1);
				$i = 0;
				
				foreach( $all_slides as $slide ){
					$i++;
					if ($i <= 3){
						$slide->content=$slide->getThumbnailContent();
						$deck->slides[]=$slide;	
						//$deck->tags = $deck->getTags ($deck->id);
					}				
				}	
				$deck->number_of_slides=count($all_slides);
			}
			$output = array ("decks"=>$decks);
			if($cache->bEnabled){
				$cache->delData('new_decks');
				$cache->setData('new_decks', json_encode($output));
			}
			echo json_encode ( $output );
		}		
	}
	function getFeaturedDecks(){
		$cache=new CacheAPC();
		$cache->iTtl=36000;
		$deckList = new DeckList();
		if($cache->bEnabled && $output=$cache->getData('featured_decks')){
			echo $output;
		}else{
			$decks = $deckList->getAllFeatured(5);
			foreach($decks as $deck) {
				$all_slides=$deck->getSlidesLite(1);
				$i = 0;
				
				foreach( $all_slides as $slide ){
					$i++;
					if ($i <= 3){
						$slide->content=$slide->getThumbnailContent();
						$deck->slides[]=$slide;	
						//$deck->tags = $deck->getTags ($deck->id);
					}				
				}	
				$deck->number_of_slides=count($all_slides);
			}
			$output = array ("decks"=>$decks);
			if($cache->bEnabled){
				$cache->delData('featured_decks');
				$cache->setData('featured_decks', json_encode($output));
			}
			echo json_encode ( $output );
		}
	}
	function getDeckList() {
		$type = $_GET ['type'];
		$deckList = new DeckList();
		switch($type){
			case "new":
				$decks = $deckList->getAllDecks(4,true);
			break;
			case "featured":
				$decks = $deckList->getAllFeatured(4);
			break;
			case "popular":
				$decks = $deckList->getAllPopular(4);
			break;						
		}
		foreach($decks as $deck) {
			$all_slides=$deck->getSlidesLite(1);
			$i = 0;
			
			foreach( $all_slides as $slide ){
				$i++;
				if ($i <= 4){
					$slide->content=$slide->getThumbnailContent();
					$deck->slides[]=$slide;	
					//$deck->tags = $deck->getTags ($deck->id);
				}				
			}	
			$deck->number_of_slides=count($all_slides);
		}
		
		$output = array ("decks"=>$decks);
		echo json_encode ( $output );
	}	
	function getQuestionRevisions() {
		$id = $_GET ['id'];
		$question = new Question ();
		$question->createFromID ( $id );
		$result = array ();
		$result = $question->getAllRevisions ();
		$list = new QList ();
		$list->questions = $result;
		echo json_encode ( $list );
	}
	function useQuestRevision() {
		$id = $_GET ['id'];
		$question = new Question ();
		$question->createFromID ( $id );
		$new_id = $question->useQuestRevision ();
		echo json_encode ( $new_id );
	}
	function getQuestionId() {
		$answerId = $_GET ['answerId'];
		$question = new Question ();
		echo $question->getID ( $answerId );
	}
        function saveQuestResults() {
            $quest_id = $_GET['quest_id'];
            $checked = $_GET['checked'];
            $attempt = $_GET['attempt'];
            $quest_eval = new Quest_eval();
            $quest_eval->addNew($attempt,$quest_id,$checked);
            
        }
	function getSlideByBasic() {
		$basic_id = $_GET ['basic_id'];
		$deck_id = $_GET ['deck_id'];
		$deck = new Deck ();
		$deck->createFromIdLite ( $deck_id );
		$deck->slides = $deck->getSlides ();
		$slide_content = "";
		foreach ( $deck->slides as $slide ) {
			if ($slide->slide_id == $basic_id) {
				$slide_content ['content'] = $slide->content;
				$slide_content ['basic_id'] = $basic_id;
				$slide_content ['slide_id'] = $slide->slide_id;
			}
		}
		echo json_encode ( $slide_content );
	
	}
	function getLastSlideByBasic() {
		$basic_id = $_GET ['basic_id'];
		$slide = new Slide ();
		$slide->slide_id = $basic_id;
		$id = $slide->getLastRevisionID ();
		$newSlide = new Slide ();
		$newSlide->createFromID ( $id );
		echo json_encode ( $newSlide->content );
	}
	function getCorrectAnswers() {
		$quest_id = $_GET ['quest_id'];
		$question = new Question ();
		$question->createFromID ( $quest_id );
		echo json_encode ( $question->getRightAnswers () );
	}
	function getDistractors() {
		$quest_id = $_GET ['quest_id'];
		$question = new Question ();
		$question->createFromID ( $quest_id );
		echo json_encode ( $question->getDistractors () );
	}
	function saveAnswer() {
		$questionId = $_GET ['questionId'];
		$answer = $_GET ['answer'];
		$explanation = $_GET ['explanation'];
		$is_right = $_GET ['is_right'];
		$question = new Question ();
		$question->id = $questionId;
		$newAnswerId = $question->addAnswer ( $answer, $explanation, $is_right );
		echo json_encode ( $question->getAnswer ( $newAnswerId ) );
	}
	function editAnswer() {
		$explanation = $_GET ['explanation'];
		$answer = $_GET ['answer'];
		$is_right = $_GET ['is_right'];
		if (isset ( $_GET ['answerId'] ) && $_GET ['answerId'] > 0) {
			$question = new Question ();
			$answerId = $_GET ['answerId'];
			$question->id = $question->getID ( $answerId );
			echo json_encode ( $question->editAnswer ( $answerId, $answer, $explanation, $is_right ) );
		}
	}
	function getAnswer() {
		$answerId = $_GET ['answerId'];
		$question = new Question ();
		$answer = array ();
		$answer = $question->getAnswer ( $answerId );
		echo json_encode ( $answer );
	}
	function removeAnswer() {
		$answerId = $_GET ['answerId'];
		$question = new Question ();
		$question->id = $question->getID ( $answerId );
		$question->removeAnswer ( $answerId );
	}
	function checkCreatingNewQuestionRev() {
		$question_id = $_GET ['question'];
		$question = new Question ();
		$question->createFromID ( $question_id );
		$user_id = $this->getCurrentUserID ();	
		if ($user_id == $question->user->id)
			echo 0;
		else
			echo 1;
	}
	function markDoubtfulQuestion() {
		$id = $_GET ['id'];
		$comment = $_GET ['comment'];
		$user_id = $this->getCurrentUserID ();
		//prevent updating when user is not logged in
		if(!$user_id)
				return 0;		
		$question = new Question ();
		$question->id = $id;
		$question->markDoubtful ( $user_id, $comment );
	}
	function unMarkDoubtful() {
		$id = $_GET ['id'];
		$question = new Question ();
		$question->id = $id;
		$question->unMarkDoubtful ();
	}
	function saveTest() {
            $eval = new Quest_eval();
            $eval->wiki_app = $_GET ['wiki_app'];
            $eval->mtf = $_GET ['mtf'];
            $eval->ripkey = $_GET ['ripkey'];
            $eval->dich = $_GET ['dich'];
            $eval->morgan = $_GET ['morgan'];
            $eval->max_points = $_GET ['max_points'];
	    $attempt = $_GET['attempt'];
            $deck_id = $_GET['deck_id'];
            $eval->finish($attempt, $deck_id);
	}
	function countQuestionCorrect() {
		$points = $_GET ['points'];
		$quest_id = $_GET ['quest_id'];
		$question = new Question ();
		$question->createFromID ( $quest_id );
		$correct = $points / $question->difficulty;
		$question->editCorrect ( $correct );
	}
        function copyQuestion(){
            $id = $_GET['id'];
            $question = new Question();
            $question->createFromID($id);           
            $user_id = $this->getCurrentUserID();
			//prevent updating when user is not logged in
			if(!$user_id)
					return 0;            
            echo json_encode($question->copy($user_id));
        }
        function deleteQuestion(){
            $id = $_GET['id'];
            $question = new Question();
            $question->createFromID($id);           
            $user_id = $this->getCurrentUserID();
			//prevent updating when user is not logged in
			if(!$user_id)
					return 0;
            if ($question->user->id == $user_id){
                $question->delete();
                echo -1;
            } else{
                echo $question->user->id;
            }          
            
        }        
	function reAssign() {
		$quest_id = $_GET ['quest_id'];
		$new_slide_id = $_GET ['new_slide_id'];
		$question = new Question ();
		$question->id = $quest_id;
		$question->reAssign ( $new_slide_id );
	}
	function getSlideLink() {
		$deck_id = $_GET ['deck_id'];
		$quest_id = $_GET ['quest_id'];
		$question = new Question ();
		$question->createFromID ( $quest_id );
		$slide_basic = $question->item_id;
		$deck = new Deck ();
		$deck->id = $deck_id;
		$slide = new Slide ();
		$slide = $deck->findSlideByBasic ( $slide_basic );
		echo json_encode ( $slide );
	}
	function getListQuestions() {
		$id = $_GET ['id'];
		$list = new QList ();
		$list->createFromID ( $id );
		echo json_encode ( $list );
	}
	function deleteFromList() {
		$list_id = $_GET ['list_id'];
		$quest_id = $_GET ['quest_id'];
		$list = new QList ();
		$list->id = $list_id;
		$list->deleteFrom ( $quest_id );
	}
	function getUserLists() {
		//prevent updating when user is not logged in
		$current_user_id=$this->getCurrentUserID ();	
		$user = new User ();
		$user->id = $current_user_id;
		$lists_array = array ();
		foreach ( $user->getOwnLists () as $list_row ) {
			$list = new QList ();
			$list->createFromID ( $list_row ['id'] );
			$lists_array [] = $list;
		}
		echo json_encode ( $lists_array );
	}
	function addList() {
		$title = $_GET ['title'];
		//prevent updating when user is not logged in
		$current_user_id=$this->getCurrentUserID ();
		if(!$current_user_id)
				return 0;			
		$user = new User ();
		$user->id = $current_user_id;
		echo json_encode ( $user->addList ( $title ) );
	}
	function addToList() {
		$quest_id = $_GET ['quest_id'];
		$list_id = $_GET ['list_id'];
		$list = new QList ();
		$list->id = $list_id;
		$list->addQuestion ( $quest_id );
	}
	function listDelete() {
		$id = $_GET ['id'];
		$list = new QList ();
		$list->id = $id;
		$list->delete ();
		echo json_encode ( $list );
	}
	function listRename() {
		$id = $_GET ['id'];
		$title = $_GET ['newTitle'];
		$list = new QList ();
		$list->createFromID ( $id );
		echo json_encode ( $list );
		$list->rename ( $title );
	
	}
	function getAutoTests() {
		if (isset ( $_GET ['keywords'] )) {
			$keywords = $_GET ['keywords'];
		} else {
			$keywords = '';
		}
		$order = 'title';
		$deckList = new DeckList ();
		$tests = array ();
		$tests = $deckList->buildWithQuestions ( $keywords, $order );
		return $tests;
	}
	
	/*---------------------- questions staff end -----------------------------*/
	function isFollowing() {
		$type = $_GET ['item_type'];
		$id = $_GET ['id'];
		$unfollow = $_GET ['unfollow'];
		$user_id = $this->getCurrentUserID ();		
		$user = new User ();
		$user->createFromID ( $user_id );
		$user->follow ( $type, $id, $unfollow );
	}
	function getCSS() {
		$id = $_GET ['id'];
		$styleObj = new Style ();
		echo $styleObj->getCSS ( $id );
	}
	//coverts scss to css
	function compileSCSS() {
		try {
			$sass = new SassParser ( array ('style' => 'nested', 'syntax' => 'scss' ) );
			$css = $sass->toCss ( $_POST ['input'], false );
		} catch ( Exception $e ) {
			$css = '';
		}
		echo $css;
	}
	function getTransitionCSS() {
		$id = $_GET ['id'];
		$transitionObj = new Transition ();
		echo $transitionObj->getCSS ( $id );
	}
	function searchMatchItem() {
		if (isset ( $_GET ['selected_id'] ))
			$selected_id = $_GET ['selected_id'];
		else
			$selected_id = 0;
		$order = $_GET ['order'];
		
		if ($_GET ['tag'])
			$tag = $_GET ['tag'];
		else
			$tag = 'all';
		if (isset ( $_GET ['page'] ))
			$page = $_GET ['page'];
		else
			$page = 1;
		if ($_GET ['typeOfSearch']) {
			$typeOfSearch = $_GET ['typeOfSearch'];
		} else {
			$typeOfSearch = 'both';
		}
		$keywords = $_GET ['keywords'];
                $pattern = '/[^\p{L}0-9- &]/iu';
		$keywords = preg_replace ($pattern,'',$keywords);
                $last_decks = array ();
                $language = false;
                if (isset($_GET['language']) && $_GET['language']!='')
                     $language= $_GET['language'];
		if ($typeOfSearch == 'deck') {
			$deckList = new DeckList ();
                        if (isset($_GET['own']) && $_GET['own'] == 'true'){
                            $deckList->own = true;
                            $deckList->decks = $deckList->searchMatch ( $keywords, $tag, $order, $page, $this->getCurrentUserID(), $language );
                        }else{
                            $deckList->own = false;
                            $deckList->decks = $deckList->searchMatch ( $keywords, $tag, $order, $page, false, $language );
                        }
                        
                        			
			if (count ( $deckList->decks ) > 0) {
				foreach ( $deckList->decks as $deck ) {
					$r = new Deck ();
					$r->id = $deck ['id'];
					$r->deck_id = $deck ['deck_id'];
					$last = new Deck ();
					$last->id = $r->getLastRevisionID ();
                                        $all_slides = $last->getSlidesLite(1);
                                        $i = 0;
                                        foreach( $all_slides as $slide ){
                                                $i++;
                                                if ($i <= 4){
                                                    $slide->content = $slide->getThumbnailContent();
                                                    $last->slides[] = $slide;	
						}				
                                        }	
                                        $last->number_of_slides = count($all_slides);
					$last->title = $last->getTitle ();
                                        $last->slug_title = $last->sluggify($last->title);
					$last_decks [] = $last;
				}
				$deckList->decks = $last_decks;
				if ($selected_id && $selected_id != '0') {
					$deckList->removeLoopingDecks ( $selected_id );
				}
                                echo json_encode ( $deckList );
			} 

			else {
				echo '-1';
			}
		}
		if ($typeOfSearch == 'slide') {
			$slideList = new SlideList ();
                        if (isset($_GET['own']) && $_GET['own'] == 'true'){
                            $slideList->own = true;
                            $slideList->slides = $slideList->searchMatch ( $keywords, $order, $page, $this->getCurrentUserID(),$language );
                        }else{
                            $slideList->own = false;
                            $slideList->slides = $slideList->searchMatch ( $keywords, $order, $page, false, $language );
                        }
			
			$last_slides = array ();
			if (count ( $slideList->slides ) > 0) {
				foreach ( $slideList->slides as $slide ) {
					$r = new Slide ();
					$r->slide_id = $slide ['slide'];
					$last = new Slide ();
					$last->id = $r->getLastRevisionID ();
					$deck = $last->getLastDeck ( $last->id );
					$last->content = $last->getSlideContent ();
					$last->deck = $deck;
					$last->position = $last->getPosition ();
					$last->thumbnailContent = $last->getThumbnailContent ();
					$last_slides [] = $last;
				}
				$slideList->slides = $last_slides;
				
				echo json_encode ( $slideList );
			} else
				echo '-1';
		}
	}
	function setItemTitle() {
		//prevent updating when user is not logged in
		$current_user_id=$this->getCurrentUserID ();
		if(!$current_user_id)
				return 0;		
		$user = new User ();
		$user->createFromID ( $current_user_id);
		$title = $_GET ['title'];
		$root_deck = $_GET ['root_deck'];
		$node_id = $_GET ['node_id'];
		$child_node_id = $_GET ['child_node_id'];
		$type = $_GET ['type'];
		if ($type == 'slide') {
			$child_node_id = $node_id;
		}
		$changeset = array ('user_id' => $user->id, 'items' => array (array ('item_id' => $child_node_id ) ) );
		$deck = new Deck ();		
		$deck->createFromIDLite ( $root_deck );
		$deck->content = $deck->fetchDeckContentLite ();
		$tree = $deck->getTree ();
		$rev = new Revision ( $tree );
		//handle new deck revision creation
		$results = $rev->handleChange ( $changeset );
		//id of old or newly created deck after revisioning
		$deckid = $results ['items'] [0] ['target_deck_id'];
		if ($type == 'deck') {
			$deck = new Deck ();
			$deck->createFromIDLite ( $deckid );
			$deck->user = $user;
			$deck->comment='renamed item.';
			$deck->setTitle ( $title );
			//todo:get the parent node of deck easier and more effectively
			$tmp = new Deck ();
			if ($results ['root_changed']) {
				$tmp->createFromIDLite ( $results ['root_changed'] );
			} else {
				$tmp->createFromIDLite ( $root_deck );
			}

			$tmp->content = $tmp->fetchDeckContentLite ();
			$tree = $tmp->getTree ();
			$rev = new Revision ( $tree );
			//var_dump($rev->parentHash);
			$new_child_id = 'tree-' . $deckid . '-' . $rev->getNodeTypeFromID ( $child_node_id ) . '-' . $rev->getNodeRevID ( $child_node_id ) . '-' . $rev->getNodePositionFromID ( $child_node_id );
			$results ['items'] [0] ['rev_id'] = $rev->parentHash [$new_child_id];
		} else {
			$deck = new Deck ();
			$deck->createFromIDLite ( $deckid );
			$slide = new Slide ();
			$old_slide_id = $rev->getNodeRevID ( $node_id );
			$slide_position = $rev->getNodePositionFromID ( $node_id );
			$slide->createFromID ( $old_slide_id );
			$slide->user = $user;
			$slide->comment='renamed item.';
			//only create new slide revision when slide title has changed
			if (trim ( $title ) != $slide->title) {
				$slide->replaceContentWithNewTitle ( $title );
				$slide->save ();
				$deck->replaceContentByPosition ( $slide->id, $old_slide_id, 'slide', $slide_position );
			}
			$results ['items'] [0] ['rev_id'] = 'tree-' . $deckid . '-slide-' . $slide->id . '-' . $slide_position;
		}
		echo json_encode ( $results );
	}
	function insertNewItemToDeck() {
		$output = array ();
		$content = array ();
		//prevent updating when user is not logged in
		$current_user_id=$this->getCurrentUserID ();
		if(!$current_user_id)
				return 0;		
		$user = new User ();
		$user->createFromID ( $current_user_id );
		$root_deck = $_GET ['root_deck'];
		$node_id = $_GET ['node_id'];
		$type = $_GET ['type'];
		$changeset = array ('user_id' => $user->id, 'items' => array (array ('item_id' => $node_id ) ) );
		$deck = new Deck ();
		$deck->createFromIDLite ( $root_deck );
		$deck->content = $deck->fetchDeckContentLite ();
		$tree = $deck->getTree ();
		$rev = new Revision ( $tree );
		//handle new deck revision creation
		$results = $rev->handleChange ( $changeset );
		//id of old or newly created deck after revisioning
		$deckid = $results ['items'] [0] ['target_deck_id'];
		//prepare to copy the editors from parent deck
		$parent_deck=new Deck();
		$parent_deck->createFromIDLite($deckid);
		$parent_deck->editors=$parent_deck->getEditors();
		$after = $rev->getNodePositionFromID ( $node_id );
		if ($type == "deck") {
			$new_deck = new Deck ();
			$new_deck->user = $user;
			$new_deck->comment = "new deck created!";
			$new_deck->position = $after + 1;
			$new_deck->title = "new deck";
            $new_deck->language = $deck->language;
			$new_deck->create ();
			//copy the editors from parent deck
			$new_deck->addEditors($parent_deck->editors);
			//copy the owner of the parent deck
			$new_deck->addUser($parent_deck->user,'editor');
			$content [] = $new_deck;
			$deck_id = 'tree-' . $deckid . '-deck-' . $new_deck->id . '-' . $new_deck->position;
			$slide_id = 'tree-' . $new_deck->id . '-slide-' . $new_deck->content [0]->id . '-' . $new_deck->content [0]->position;
			$output ['attr'] = array ('id' => $deck_id . '-node' );
			$output ['data'] = array ('title' => 'new deck', 'icon' => $new_deck->getIcon (), 'attr' => array ('class' => 'deck' . $new_deck->id, 'id' => $deck_id, 'href' => '#' . $deck_id . '-view' ), 'children' => array () );
			$output ['children'] = array ("attr" => array ('id' => $slide_id . '-node' ), 'data' => array ('title' => 'new slide', 'icon' => $new_deck->content [0]->getIcon (), 'attr' => array ('class' => 'slide' . $new_deck->content [0]->id, 'id' => $slide_id, 'href' => '#' . $slide_id . '-view' ) ), 'children' => array () );
		} else {
			$new_slide = new Slide ();
			$new_slide->user = $user;
			$new_slide->comment = "new slide created!";
			$new_slide->content = "<h2>new slide</h2>";
                        $deck_for_title = new Deck();
                        $deck_for_title->id = $deckid;
                        $deck_for_title->title = $deck_for_title->getTitle();
                        $deck_for_title->slug_title = $deck_for_title->sluggify($deck_for_title->title);
			$new_slide->deck = $deck_for_title;
			$new_slide->position = $after + 1;
            $new_slide->language = $deck->language;
			$new_slide->create ();
			$content [] = $new_slide;
			$slide_id = 'tree-' . $deckid . '-slide-' . $new_slide->id . '-' . $new_slide->position;
			$output ['attr'] = array ('id' => $slide_id . '-node' );
			$output ['data'] = array ('title' => 'new slide', 'icon' => $new_slide->getIcon (), 'attr' => array ('class' => 'slide' . $new_slide->id, 'id' => $slide_id, 'href' => '#' . $slide_id . '-view' ) );
			$output ['children'] = array ();
		}
		//add node to deck
		$tmp = new Deck ();
		$tmp->createFromIDLite ( $deckid );
		$tmp->content=$tmp->fetchDeckContentLite ();
		$tmp->addContent ( $content );
		//return results
		$results ['items'] [0] ['rev_id'] = $slide_id;
		echo json_encode ( $results );
	}
	//!!! now only works with slides
	function insertDuplicateItemToDeck() {
		$output = array ();
		$content = array ();
		//prevent updating when user is not logged in
		$current_user_id=$this->getCurrentUserID ();
		if(!$current_user_id)
				return 0;		
		$user = new User ();
		$user->createFromID ( $current_user_id );
		$root_deck = $_GET ['root_deck'];
		$node_id = $_GET ['node_id'];
		$type = $_GET ['type'];
		$item_id = $_GET ['item_id'];
		$changeset = array ('user_id' => $user->id, 'items' => array (array ('item_id' => $node_id ) ) );
		$deck = new Deck ();
		$deck->createFromIDLite ( $root_deck );
		$deck->content = $deck->fetchDeckContentLite ();
		$tree = $deck->getTree ();
		$rev = new Revision ( $tree );
		//handle new deck revision creation
		$results = $rev->handleChange ( $changeset );
		//id of old or newly created deck after revisioning
		$deckid = $results ['items'] [0] ['target_deck_id'];
		$after = $rev->getNodePositionFromID ( $node_id );
		if ($type == 'deck') {
			$new_deck = new Deck ();
			$new_deck->createFromIDLite ( $item_id );
			$new_deck->position = $after + 1;
			$content [] = $new_deck;
		} else {
			$current_slide = new Slide ();
			$current_slide->createFromID ( $item_id );
                        $current_slide->language = $current_slide->getLanguage();
			$new_slide = new Slide ();
			$new_slide->user=$user;
			$new_slide->content=$current_slide->content;
			$new_slide->comment="duplicated from slide ".$item_id;	
			$new_slide->position = $after + 1;
                        $new_slide->language = $current_slide->language;
			$new_slide->create ();		
			$item_id=$new_slide->id;
			$content [] = $new_slide;
		}
		//add node to deck
		$tmp = new Deck ();
		$tmp->createFromIDLite ( $deckid );
		$tmp->content=$tmp->fetchDeckContentLite ();
		$tmp->addContent ( $content );
		//return results
		$results ['items'] [0] ['rev_id'] = 'tree-' . $deckid . '-' . $type . '-' . $item_id . '-' . ($after + 1);
		echo json_encode ( $results );
	}	
	function insertItemToDeck() {
		$output = array ();
		$content = array ();
		//prevent updating when user is not logged in
		$current_user_id=$this->getCurrentUserID ();
		if(!$current_user_id)
				return 0;		
		$user = new User ();
		$user->createFromID ( $current_user_id );
		$root_deck = $_GET ['root_deck'];
		$node_id = $_GET ['node_id'];
		$type = $_GET ['type'];
		$item_id = $_GET ['item_id'];
		$changeset = array ('user_id' => $user->id, 'items' => array (array ('item_id' => $node_id ) ) );
		$deck = new Deck ();
		$deck->createFromIDLite ( $root_deck );
		$deck->content = $deck->fetchDeckContentLite ();
		$tree = $deck->getTree ();
		$rev = new Revision ( $tree );
		//handle new deck revision creation
		$results = $rev->handleChange ( $changeset );
		//id of old or newly created deck after revisioning
		$deckid = $results ['items'] [0] ['target_deck_id'];
		$after = $rev->getNodePositionFromID ( $node_id );
		if ($type == 'deck') {
			$new_deck = new Deck ();
			$new_deck->createFromIDLite ( $item_id );
			$new_deck->position = $after + 1;
			//if appending his own deck, inherit editors from parent deck
			if($new_deck->user->id==$user->id){
				$parent_deck=new Deck();
				$parent_deck->createFromIDLite($deckid);
				$parent_deck->editors=$parent_deck->getEditors();
				$new_deck->addEditors($parent_deck->editors);
				$new_deck->addUser($parent_deck->user,'editor');
			}
			$content [] = $new_deck;
		} else {
			$new_slide = new Slide ();
			$new_slide->createFromID ( $item_id );
			$new_slide->position = $after + 1;
			$content [] = $new_slide;
		}
		//add node to deck
		$tmp = new Deck ();
		$tmp->createFromIDLite ( $deckid );
		$tmp->content=$tmp->fetchDeckContentLite ();
		$tmp->addContent ( $content );
		//return results
		$results ['items'] [0] ['rev_id'] = 'tree-' . $deckid . '-' . $type . '-' . $item_id . '-' . ($after + 1);
		echo json_encode ( $results );
	}
	function deleteItemFromPosition() {
		//prevent updating when user is not logged in
		$current_user_id=$this->getCurrentUserID ();
		if(!$current_user_id)
				return 0;		
		$user = new User ();
		$user->id = $current_user_id ;
		$root_deck = $_GET ['root_deck'];
		$node_id = $_GET ['node_id'];
		$parent_deck_node = $_GET ['parent_deck'];
		$changeset = array ('user_id' => $user->id, 'items' => array (array ('item_id' => $node_id ) ) );
		$deck = new Deck ();
		$deck->createFromIDLite ( $root_deck );
		$deck->content = $deck->fetchDeckContentLite ();
		$tree = $deck->getTree ();
		$rev = new Revision ( $tree );
		//handle new deck revision creation
		$results = $rev->handleChange ( $changeset );
		//id of old or newly created deck after revisioning
		$deckid = $results ['items'] [0] ['target_deck_id'];
		$position = $rev->getNodePositionFromID ( $node_id );

		$tree=0; //no need for creating tree again
		$rev = new Revision ( $tree );		
		if($tmp=@$results ['new_deck_revs'][$rev->getNodeParentRevID($parent_deck_node)])
			$new_child_id = 'tree-' . $tmp . '-deck-' . $deckid . '-' . $rev->getNodePositionFromID ( $parent_deck_node );
		else 
			$new_child_id = 'tree-' . $rev->getNodeParentRevID($parent_deck_node) . '-deck-' . $deckid . '-' . $rev->getNodePositionFromID ( $parent_deck_node );
		$results ['items'] [0] ['rev_id'] = $new_child_id;
		$deck = new Deck ();
		$deck->createFromIDLite ( $deckid );
		$deck->deleteItemFromPosition ( $position );
		echo json_encode ( $results );
	}
	function moveItem() {
		//prevent updating when user is not logged in
		$current_user_id=$this->getCurrentUserID ();
		if(!$current_user_id)
				return 0;		
		$user = new User ();
		$user->createFromID ( $current_user_id);
		$root_deck = $_GET ['root_deck'];
		$source_node_id = $_GET ['source_node_id'];
		$target_node_id = $_GET ['target_node_id'];
		$position = $_GET ['position'];
		$changeset = array ('user_id' => $user->id, 'items' => array (array ('item_id' => $source_node_id ), array ('item_id' => $target_node_id ) ) );
		$deck = new Deck ();
		$deck->createFromIDLite ( $root_deck );
		$deck->content = $deck->fetchDeckContentLite ();
		$tree = $deck->getTree ();
		$rev = new Revision ( $tree );
		//handle new deck revision creation
		$results = $rev->handleChange ( $changeset );
		//id of old or newly created deck after revisioning
		$source_deckid = $results ['items'] [0] ['target_deck_id'];
		$target_deckid = $results ['items'] [1] ['target_deck_id'];
		$type = $rev->getNodeTypeFromID ( $source_node_id );
		if ($type == 'deck') {
			$selected_item = new Deck ();
			$selected_item->parent_deck = $source_deckid;
			$selected_item->createFromIDLite ( $rev->getNodeRevID ( $source_node_id ) );
		} else {
			$selected_item = new Slide ();
                        $deck_for_title = new Deck();
                        $deck_for_title->id = $source_deckid;
                        $deck_for_title->title = $deck_for_title->getTitle();
                        $deck_for_title->slug_title = $deck_for_title->sluggify($deck_for_title->title);
			$selected_item->deck = $deck_for_title;
			$selected_item->createFromID ( $rev->getNodeRevID ( $source_node_id ) );
		}
		$selected_item->position = $rev->getNodePositionFromID ( $source_node_id );
		$source_deck = new Deck ();
		$source_deck->createFromIDLite ( $source_deckid );
		$source_deck->user = $user;
		$target_deck = new Deck ();
		$target_deck->createFromIDLite ( $target_deckid );
		//handle new deck revision creation
		$source_deck->moveItem ( $selected_item, $target_deck, $position );
		$results ['items'] [0] ['rev_id'] = 'tree-' . $target_deckid . '-' . $type . '-' . $rev->getNodeRevID ( $source_node_id ) . '-' . $position;
		echo json_encode ( $results );
	}
	
	function getSlideTitle() {
		$id = $_GET ['id'];
		$slide = new Slide ();
		$slide->createFromId ( $id );
		echo $slide->getTitle ();
	}
	function getTagCloudJSON() {
		$tags = new Tag ();
		$tagcloud = $tags->getTagCloudData ();
		
		$json = "({tags:[";
		
		$x = 0;
		//loop through and return results  
		foreach ( $tagcloud as $item ) {
			
			//continue json object  
			$json .= "{tag:'" . html_entity_decode ( $item ["tag"] ) . "',freq:'" . $item ["freq"] . "'}";
			$x ++;
			//add comma if not last row, closing brackets if is  
			if ($x < count ( $tagcloud ))
				$json .= ",";
			else
				$json .= "]})";
		}
		
		//return JSON with GET for JSONP callback  
		$response = $_GET ["callback"] . $json;
		echo $response;
	}
	function loadUserPage() {
		$id = $_GET ['user_id'];
		$page = $_GET ['page'];
		$tab = $_GET ['tab'];
		$feed = array ();
		$user = new User ();
		$user->createFromID ( $id );
		
		$user->setPartOfFeedSize ( $tab );
		switch ($tab) {
			case 'contributed_slide' :
				$feed = $user->getContributedSlides ( $page );
				break;
			case 'contributed_deck' :
				$feed = $user->getContributedDecks ( $page );
				break;
			case 'subscribed_slide' :
				$feed = $user->getSubscribedSlides ( $page );
				break;
			case 'subscribed_deck' :
				$feed = $user->getSubscribedDecks ( $page );
				break;
			case 'subscribed_user' :
				$feed = $user->getSubscribedUsers ( $page );
				break;
			default :
				$feed = $user->getContributedDecks ( $page );
				break;
		}
		echo json_encode ( $feed );
	}
	function updateFeedShowPreferences() {
		$id = $_GET ['user_id'];
		$page = $_GET ['page'];
		$show_contributed_slides = $_GET ['show_contributed_slides'];
		$show_contributed_decks = $_GET ['show_contributed_decks'];
		$show_subscribed_slides = $_GET ['show_subscribed_slides'];
		$show_subscribed_decks = $_GET ['show_subscribed_decks'];
		$show_subscribed_users = $_GET ['show_subscribed_users'];
		
		$user = new User ();
		$user->createFromID ( $id );
		$user->setShowContributedSlides ( $show_contributed_slides );
		$user->setShowContributedDecks ( $show_contributed_decks );
		$user->setShowSubscribedSlides ( $show_subscribed_slides );
		$user->setShowSubscribedDecks ( $show_subscribed_decks );
		$user->setShowSubscribedUsers ( $show_subscribed_users );
		
		$user->setFeedSize ();
		$contributed_decks = $user->getContributedDecks ( $page );
		$contributed_slides = $user->getContributedSlides ( $page );
		$subscribed_decks = $user->getSubscribedDecks ( $page );
		$subscribed_slides = $user->getSubscribedSlides ( $page );
		$subscribed_users = $user->getSubscribedUsers ( $page );
		
		$feed = array ();
		array_push ( $feed, $contributed_decks, $contributed_slides, $subscribed_decks, $subscribed_slides, $subscribed_users );
		
		echo json_encode ( $feed );
	}
        function getUsersSameFB(){
            $fb_id = $_GET['fb_id'];
            $user = new User();
            $result = $user->getUsersSameFB($fb_id);
            echo json_encode($result);
        }
	
	function login() {            
            if (isset($_GET['fb_id'])){
                $user = new User();
                $fb_id = $_GET['fb_id'];
                $user->fb_id = $fb_id;
                if (isset($_GET['id'])){ //there are several users with the same fb_id
                    $auth = new SlideWikiAuth ( $user );
                    $success = $auth->loginByID($_GET['id']);
                    die();
                }
            }else{                
                $login = $_GET ['login'];
                $pass = $_GET ['pass']; 
                $user = new User($login , $pass);
            }
            $auth = new SlideWikiAuth ( $user );
            
            // check if user exists
            $auth_login = $auth->login ();
            if ($auth_login >= 0) {
                    echo ('0');
            } else {
                echo ($auth_login);
            } 
            
	}        
        function checkFBId(){
            $fb_id = $_GET['fb_id'];
            $user = new User();
            echo json_encode($user->checkFBId($fb_id));
        }
	function logout() {
		// initialize a session 
		if (session_id() == "") session_start();
		// check
		unset($_SESSION['uid']);
                unset($_SESSION['access_token']);
                unset($_SESSION['code']);
		session_destroy();
	}	
	function register() {
                $login = $_GET ['login'];
		$username = $_GET ['username'];
		$pass = $_GET ['pass'];
                $result = '';
                $fb_id = '';
                $user = new User($login,$pass,$username);
                
                if (!isset($_GET['fb_id'])){
                    
                    require_once (ROOT . DS . 'application' . DS . 'config' . DS . 'captcha.config.php');
                    require_once (ROOT . DS . 'libraries' . DS . 'backend' . DS . 'captcha' . DS . 'Captcha.php');
                    $captchaKey = $_GET ['captcha'];
                    $captcha = new Captcha ( $captchaOptions );
                    if (! $captcha->isKeyRight ( $captchaKey )) {
			$result.=1;
                    }
                    
                    // validate e-mail (login is a user email)
                    if (! $this->email_valid ( $login )) {
                            $result.=2;
                    }else {
                        if ( ! $user->checkLogin($login)){
                            $result.=5;
                        }
                    }
                    if (! $this->name_valid ( $username )) {
                            $result.=3;
                    }else{
                        if ( ! $user->checkName($username)){
                            $result.=6;
                        }
                    }
                }else{
                    $fb_id = $_GET['fb_id'];
                    $user->fb_id = $fb_id;                    
                }
		
                if ($result == ''){
                    
                    if ($user->register() !== -1){
                        $auth = new SlideWikiAuth ( $user );
                        $auth->login();                        
                        $result = 0;
                    }else{
                        $result.=7;
                    }                    
                }		
                echo json_encode($result);
	}
        function getFB_DATA(){
            $data = array();
            $data['id'] = FB_APP;
            $data['url'] = urlencode(FB_URL);
            echo json_encode($data);
        }
     
        
	function name_valid($temp_name) {
            if ($temp_name =='' || preg_match ( "/[^(\w)|(\x7F-\xFF)|(\s)]/", $temp_name )) {
		return false;
            } else
                return true;
	}
	
	// this function is taken from http://php.net/manual/en/function.preg-match.php
	// All credits to Khalid_Moharram at hotmail dot com
	// email validation, according to the RFC specifications (ONLY FOR ((ENGLISH ASCII)) E-Mails). 
	function email_valid($temp_email) {
            if ($temp_email!='' && preg_match ( "/@/", $temp_email ) == 1) {
                return true;
            } else {
                return false;
            }
	}
	
	function captcha() {
		require_once (ROOT . DS . 'application' . DS . 'config' . DS . 'captcha.config.php');
		require_once (ROOT . DS . 'libraries' . DS . 'backend' . DS . 'captcha' . DS . 'Captcha.php');
		
		$captcha = new Captcha ( $captchaOptions );
		$captcha->getCaptcha ();
		
		echo "done";
	}
	
	/*
	 *  Compare slide to slide, string to slide or string to string
	 */
	
	function compareTwoSlides() {
		require_once (ROOT . DS . 'libraries' . DS . 'backend' . DS . 'SlideCompare' . DS . 'SlideCompare.php');
		
		$slide_rev_id_1 = ( int ) @$_GET ['slide_rev_id_1'];
		$slide_rev_id_2 = ( int ) @$_GET ['slide_rev_id_2'];
		
		// make get slide contents
		$slide1 = new Slide ();
		$slide2 = new Slide ();
		
		@$slide1->createFromID ( $slide_rev_id_1 );
		@$slide2->createFromID ( $slide_rev_id_2 );
		
		if (($slide1->slide_id == NULL) or ($slide2->slide_id == NULL)) {
			die ( "There is no slide in DB with this revision id. \n Please, use the next notation for ajax request:  ?url=ajax/compareTwoSlides&slide_rev_id_1=NUMBER&slide_rev_id_2=NUMBER" );
		} else {
			$compare_result = SlideCompare::compareSlideToSlide ( $slide1->content, $slide2->content );
		}
		
		echo $compare_result;
	}
	
	function compareTwoString() {
		require_once (ROOT . DS . 'libraries' . DS . 'backend' . DS . 'SlideCompare' . DS . 'SlideCompare.php');
		
		$str1 = @$_GET ['str1'];
		$str2 = @$_GET ['str2'];
		$compare_result = NULL;
		if (is_string ( $str1 ) and is_string ( $str2 )) {
			$compare_result = SlideCompare::compareStringToString ( $str1, $str2 );
		} else {
			die ( 'Please, use ?url=ajax/compareTwoString&str1=STRING&str2=STRING notation.' );
		}
		
		echo $compare_result;
	}
	
	function compareSlideToString() {
		require_once (ROOT . DS . 'libraries' . DS . 'backend' . DS . 'SlideCompare' . DS . 'SlideCompare.php');
		
		$slide_rev_id = ( int ) $_GET ['slide_rev_id'];
		$str = $_GET ['str'];
		$compare_result = NULL;
		
		$slide = new Slide ();
		@$slide->createFromID ( $slide_rev_id );
		
		if (($slide->slide_id == NULL) or ! is_string ( $str )) {
			die ( 'Please, use ?url=ajax/compareSlideToString&slide_rev_id=NUMBER&str=STRING notation.' );
		} else {
			$compare_result = SlideCompare::compareSlideToString ( $slide->content, $str );
		}
		
		echo $compare_result;
	}
	
	function setUserPreference() {
		$type = $_GET ['type'];
		$deck_id = $_GET ['deck'];
		$id=$_GET ['id'];
		$user_id=$this->getCurrentUserID ();
		//prevent updating when user is not logged in	
		if (!$user_id) return 0;
		$user = new User ();
		$user->createFromID ( $user_id );
		switch ($type){
			case "style":
				$user->setPreferenceValueNoPass ( 'deck-' . $deck_id . '-theme', $id );
				break;
			case "transition":
				$user->setPreferenceValueNoPass ( 'deck-' . $deck_id . '-transition', $id );
				break;				
		}	
        }
        function getProfile(){
            $id = $_GET['id'];
            $user = new User();
            $user->id = $id;
            $user->getProfile();
            echo (json_encode($user));
        }
        function getFollowedDecks(){
            $id = $_GET['id'];
            $user = new User();
            $user->id = $id;
            $res = array();
            $res = $user->getFollowedDecks();
            echo (json_encode($res));
        }
        function getFollowedSlides(){
            $id = $_GET['id'];
            $user = new User();
            $user->id = $id;
            $res = array();
            $res = $user->getFollowedSlides();
            echo (json_encode($res));
        }
        function getFollowedProfile(){
            $id = $_GET['id'];
            $user = new User();
            $user->id = $id;
            $res = array();
            $res = $user->getFollowedProfiles();
            echo (json_encode($res));
        }
        function getFollowersProfile(){
            $id = $_GET['id'];
            $user = new User();
            $user->id = $id;
            $res = array();
            $res = $user->getFollowersProfiles();
            echo (json_encode($res));
        }
        function saveDescription(){
            $description = $_GET['description'];
            $user_id = $_GET['user_id'];
            $link = $_GET['link'];
            $pattern = "@^(\d+)@i";            
            if ($link && !preg_match($pattern, $link)){
                echo -1; 
            }else{
               $user = new User();
               $user->id = $user_id;
               if ($link)
                    $user->saveInfodeck($link);
               $user->saveDescription($description);
               echo 0;              
            }         
        }
        function getContributedSlides(){
            $id = $_GET['id'];
            $user = new User();
            $user->id = $id;
            $result = array();
            foreach ($user->getContributedSlides() as $slide_id){
                $slide = new Slide();
                $slide->createFromIDLite($slide_id);
                $result[] = $slide;
            }
            echo json_encode($result);
        }
        function getContributedDecks(){
            $id = $_GET['id'];
            $user = new User();
            $user->id = $id;
            $result = array();
            foreach ($user->getContributedDecks() as $deck_id){
                $deck = new Deck();
                $deck->createFromIDLite($deck_id);
                $result[] = $deck;
            }
            echo json_encode($result);
        }
        function MergeUsers(){
            $fb_id = $_GET['fb_id'];
			//prevent updating when user is not logged in
			$current_user_id=$this->getCurrentUserID ();
			if(!$current_user_id)
					return 0;            
            $user = new User();
            $user->id = $current_user_id;
            
            $user->mergeWithFB($fb_id);
            echo json_encode($user);
        }
        function getUserFBId(){
			//prevent updating when user is not logged in
			$current_user_id=$this->getCurrentUserID ();       	
            $user = new User();
            $user->id = $current_user_id;
            $user_fb_id = $user->getFBId();
            echo json_encode($user_fb_id);
        }
        function saveSettings(){
            $user = new User();
            $user->createFromID($_GET['id']);
            $old_password = $_POST['user']['old_password'];            
            $new_password = $_POST['user']['new_password'];
            $confirm_new_password = $_POST['user']['confirm_new_password'];
            $default_language = $_POST['user']['language_id'].'-'.$_POST['user']['language_name'];
            $notification_interval = $_POST['user']['notification_interval'];
            $user->updateNotificationInterval($notification_interval);
            $user->setDefaultLanguage($default_language);
            $pass_error_code = $user->setPassword($old_password, $new_password, $confirm_new_password);
            // error_code = -1 - old_password not correct
            // error_code = -2 - new_password and confirm_new_password mismatch
            //var_dump($pass_error_code);
            			
            if ($pass_error_code == 0) {
                echo '<script>alert("Changes saved successfully!"); top.location.href="./user/'.$user->id.'/edit"</script>';
            }else{
                switch ($pass_error_code) {
                    case -1:
                        echo '<script>alert("Old password is wrong!"); top.location.href="./user/'.$user->id.'/edit"</script>';

                        break;
                    case -2:
                        echo '<script>alert("Confirmation password is wrong!"); top.location.href="./user/'.$user->id.'/edit"</script>';
                        break;
                    default:
                        break;
                }                
            }            
                        
        }
        function saveProfile(){
            $user = new User();
            $user->id = $_GET['id'];            
            $user->first_name = $_POST['user']['first_name'];
            $user->last_name = $_POST['user']['last_name'];
            $user->gender = $_POST['user']['gender'];
            $user->hometown = $_POST['user']['hometown'];
            $user->location = $_POST['user']['location'];
            $user->locale = $_POST['user']['locale']; 
            $user->picture = $_POST['user']['picture'];
            $user->birthday = $_POST['user']['birthday'];            
            $user->infodeck = $_POST['user']['infodeck'];
            $user->saveProfile();
            echo '<script>alert("Changes were saved successfully!"); top.location.href="./user/'.$user->id.'/edit";</script>';
        }
       
        //--------------------------------activity streams-----------------------------------------------
        function getFullDeckStream(){
            $deck_revision_id = $_GET['deck_revision_id'];
            if (isset($_GET['filter'])){
                $filter = $_GET['filter'];
            }else{
                $filter = false;
            }
            if (isset($_GET['month'])){
                $month = $_GET['month'];
            }else{
                $month = false;
            }
            if (isset($_GET['portion'])){
                $portion = $_GET['portion'];
            }else{
                $portion = false;
            }
            $stream = new Stream();            
            $stream->getFullDeckStream($deck_revision_id, $month, $filter, $portion);
            if (count ($stream->activities)){            
                $stream->sort();                
            }
            echo json_encode($stream);
        } 
        function getSlideStream(){
            $slide_revision_id = $_GET['slide_revision_id'];
            if (isset($_GET['filter'])){
                $filter = $_GET['filter'];
            }else{
                $filter = false;
            }
            if (isset($_GET['month'])){
                $month = $_GET['month'];
            }else{
                $month = false;
            } 
            if (isset($_GET['portion'])){
                $portion = $_GET['portion'];
            }else{
                $portion = false;
            }
            $slide = new Slide();
            $slide->id = $slide_revision_id;
            $slide_id = $slide->getBasicID();
            $stream = new Stream();            
            $stream->getSlideStream($slide_id, $month, $filter, $portion);
            echo json_encode($stream);
        }      
        
        
        function getShortStream(){
            $id = $_GET['id'];
            $cache=new CacheAPC();
            $cache->iTtl=36000; //10 hours
            //if user is not logged in cache tree
            if($cache->bEnabled && $output=$cache->getData('deck_short_activities_'.$id)){
                    echo $output;
            }else{
	            $stream = new Stream();
	            $stream->getShortDeckStream($id);
	            if (count ($stream->activities)){            
	                $stream->sort();
                    }
                    $i = 0;
                    $activities = array();
                    while($i < 8 && $i < count($stream->activities)){
                        $activities[] = $stream->activities[$i];
                        $i++;
                    }
                    $stream->activities = $activities;
                    if($cache->bEnabled){
                            $cache->delData('deck_short_activities_'.$id);
                            $cache->setData('deck_short_activities_'.$id, json_encode($stream));
                    }			
                    echo json_encode ($stream);
	            
	    }            
        }
        function getStream(){//index page
			$cache=new CacheAPC();
			$cache->iTtl=3600;
			//if user is not logged in cache tree
			if($cache->bEnabled && $output=$cache->getData('home_activities')){
				echo $output;
			}else{
         	   $stream = new Stream();
           	   $stream->getMainPageStream(10);
				if($cache->bEnabled){
					$cache->delData('home_activities');
					$cache->setData('home_activities', json_encode($stream));
				}			
				echo json_encode ($stream);	
			}             
        }
        function getUserStream(){
            $user_id = $_GET['user_id'];
            if (isset($_GET['month'])){
                $month = $_GET['month'];
            }else{
                $month = false;
            }
            if (isset($_GET['filter'])){
                $filter = $_GET['filter'];
            }else{
                $filter = false;
            }
            if (isset($_GET['portion'])){
                $portion = $_GET['portion'];
            }else{
                $portion = false;
            }
            $stream = new Stream();
            $stream->getUserStream($user_id, $month, $filter, $portion);
            if (count ($stream->activities)){            
                $stream->sort();
            }
            echo json_encode($stream);
        }
        
        function getUserNews(){
            $user_id = $_GET['user_id'];
            if (isset($_GET['month'])){
                $month = $_GET['month'];
            }else{
                $month = false;
            }  
            if (isset($_GET['filter'])){
                $filter = $_GET['filter'];
            }else{
                $filter = false;
            }
            if (isset($_GET['facet'])){
                $facet = $_GET['facet'];
            }else{
                $facet = false;
            }
            if (isset($_GET['portion'])){
                $portion = $_GET['portion'];
            }else{
                $portion = false;
            }
            $type = $_GET['type'];
            $stream = new Stream();
            $stream->getUserNews($user_id, $type, $month, $filter, $facet, $portion);
            if (count ($stream->activities)){            
                $stream->sort();                
            }
            echo json_encode($stream);
        }
        function translate(){      
            //prevent updating when user is not logged in
            $current_user_id = $this->getCurrentUserID();
            if(!$current_user_id)
                return 0;        	    
            $language = $_GET['language'];
            $id = $_GET['id'];            
            $deck = new Deck();
            $deck->createFromID($id); 
            $user = new User();
            $user->createFromID($current_user_id);
            $deck->user = $user;
            $deck->owner = $user;
            $need_to_translate = $deck->addToQueue($language);
            if ($need_to_translate){
                $new_id = $deck->getFutureId($language);
                echo json_encode($new_id);
            }else {
                echo json_encode(0);
            }                      
        }
        function setDeckLanguage(){
            $language = $_GET['language'];
            $deck_id = $_GET['deck_id'];
            $deck = new Deck();
            $deck->createFromID($deck_id);
            $deck->setLanguageFull($language);
        }
        
        function getAllTranslations(){            
            $id = $_GET['id'];
            $slide_rev_id = $_GET['slide_id'];            
            $deck = new Deck();
            $deck->createFromIDLite($id);
            echo json_encode($deck->getAllTranslations($slide_rev_id));
        }
        
        function getSlideTranslationProperties(){
            $slide_id = $_GET['slide_id'];
            $deck_revision_id = $_GET['deck_revision_id'];
            $deck = new Deck();
            $deck->createFromID($deck_revision_id);
            echo $deck->getSlidePropertiesInTheDeck($slide_id);
            //$result = $deck->getSlideProperties($slide_id);
            //echo json_encode($result);
        }
        function searchStream(){
            $mode = $_GET['mode'];
            if (isset($_GET['user_id'])){
               $user_id =  $_GET['user_id'];
            }else{
                $user_id = false;
            } 
            if (isset($_GET['filter'])){
                $filter = $_GET['filter'];
            }else{
                $filter = false; 
            }
            $keywords = $_GET['keywords'];
            $stream = new Stream();
            $stream->searchStream($mode, $filter, $keywords, $user_id);
            if (count($stream->activities)){
               $stream->sort();
               echo json_encode($stream); 
            }else{
                echo 0;
            }
        }
        
        //************** Evaluation **********
        function rank(){
            if (isset($_GET['id'])){
                $deck_id = $_GET['id'];
                $eval = new Quest_eval();
                $eval->deck_id = $deck_id;
                echo json_encode($eval->rank());
            }
        }
		

}
	





